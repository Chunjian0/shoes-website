<?php

class DatabaseBackup
{
    private $backupPath;
    private $maxBackups = 7; // Keep the latest 7 Days of backup
    private $mysqldumpPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        if (!file_exists($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }

        // set up mysqldump path
        $this->mysqldumpPath = $this->findMysqldump();
    }

    private function findMysqldump()
    {
        // 1. First try to get from environment variables PATH Find in
        $output = [];
        $returnVar = null;
        exec('where mysqldump 2>&1', $output, $returnVar);
        if ($returnVar === 0 && !empty($output)) {
            $path = trim($output[0]);
            if (file_exists($path)) {
                echo "from PATH Found in mysqldump: " . $path . "\n";
                return $path;
            }
        }

        // 2. Find from the registry MySQL Installation path
        $registryPaths = [
            'HKEY_LOCAL_MACHINE\SOFTWARE\MySQL AB',
            'HKEY_LOCAL_MACHINE\SOFTWARE\WOW6432Node\MySQL AB'
        ];

        foreach ($registryPaths as $regPath) {
            $output = [];
            exec('reg query "' . $regPath . '" /s', $output, $returnVar);
            if ($returnVar === 0) {
                foreach ($output as $line) {
                    if (stripos($line, 'Location') !== false) {
                        $parts = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);
                        if (isset($parts[3])) {
                            $mysqlPath = $parts[3] . '\\bin\\mysqldump.exe';
                            if (file_exists($mysqlPath)) {
                                echo "Find it from the registry mysqldump: " . $mysqlPath . "\n";
                                return $mysqlPath;
                            }
                        }
                    }
                }
            }
        }

        // 3. Check common installation paths
        $drives = ['C:', 'D:', 'E:'];
        $commonPaths = [
            '\\xampp\\mysql\\bin\\mysqldump.exe',
            '\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe',
            '\\wamp\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe',
            '\\laragon\\bin\\mysql\\mysql-8.0.30\\bin\\mysqldump.exe',
            '\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            '\\Program Files (x86)\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
        ];

        // Iterate through all drive letters and path combinations
        foreach ($drives as $drive) {
            foreach ($commonPaths as $path) {
                $fullPath = $drive . $path;
                if (file_exists($fullPath)) {
                    echo "Find from common paths mysqldump: " . $fullPath . "\n";
                    return $fullPath;
                }
            }
        }

        // 4. Recursive search system disk
        $systemDrive = getenv('SystemDrive') ?: 'C:';
        $foundPath = $this->searchMysqldumpInDirectory($systemDrive . '\\');
        if ($foundPath) {
            echo "Find by recursive search mysqldump: " . $foundPath . "\n";
            return $foundPath;
        }

        throw new Exception('Not found mysqldump.exe, please make sure it is installed MySQL.
            You can:
            1. Install MySQL or XAMPP/WAMP/Laragon
            2. Will MySQL bin Add directory to system PATH Environment variables
            3. Manually specify mysqldump.exe Location');
    }

    private function searchMysqldumpInDirectory($directory, $maxDepth = 3, $currentDepth = 0)
    {
        if ($currentDepth >= $maxDepth) {
            return null;
        }

        try {
            // Skip some directories that don't need to be searched
            $skipDirs = ['Windows', 'Program Files (x86)', 'ProgramData', 'Users'];
            if (in_array(basename($directory), $skipDirs)) {
                return null;
            }

            $files = scandir($directory);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $path = $directory . $file;
                
                // Check if it is found mysqldump.exe
                if ($file === 'mysqldump.exe' && is_file($path)) {
                    return $path;
                }

                // Recursive search for subdirectories
                if (is_dir($path)) {
                    $result = $this->searchMysqldumpInDirectory($path . '\\', $maxDepth, $currentDepth + 1);
                    if ($result) {
                        return $result;
                    }
                }
            }
        } catch (Exception $e) {
            // Ignore permission errors, etc.
            return null;
        }

        return null;
    }

    public function backup()
    {
        try {
            // make sure mysqldump Available
            if (!file_exists($this->mysqldumpPath)) {
                throw new Exception('mysqldump Invalid path: ' . $this->mysqldumpPath);
            }

            // Get database configuration
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');

            // Output debug information
            echo "Database configuration information:\n";
            echo "Host: " . $host . "\n";
            echo "Database: " . $database . "\n";
            echo "Username: " . $username . "\n";
            echo "Password length: " . (empty($password) ? "Empty!" : strlen($password)) . "\n";
            echo "Mysqldump path: " . $this->mysqldumpPath . "\n";

            // test mysqldump Version
            $versionCommand = sprintf('"%s" --version', $this->mysqldumpPath);
            echo "test mysqldump Version...\n";
            exec($versionCommand, $versionOutput, $versionReturnVar);
            if ($versionReturnVar === 0) {
                echo "mysqldump Version information: " . implode("\n", $versionOutput) . "\n";
            } else {
                throw new Exception("mysqldump Version test failed, maybe it was a path or permission problem");
            }

            // Test database connection
            try {
                $dsn = "mysql:host=$host;dbname=$database";
                $pdo = new PDO($dsn, $username, $password);
                echo "Database connection test succeeded!\n";
            } catch (PDOException $e) {
                throw new Exception("Database connection test failed: " . $e->getMessage());
            }

            // Generate backup file name
            $filename = sprintf(
                'backup_%s.sql',
                date('Y-m-d_His')
            );
            $filepath = $this->backupPath . DIRECTORY_SEPARATOR . $filename;

            // Make sure the backup directory exists and is writable
            if (!is_dir($this->backupPath)) {
                if (!mkdir($this->backupPath, 0755, true)) {
                    throw new Exception('Unable to create a backup directory: ' . $this->backupPath);
                }
            }
            if (!is_writable($this->backupPath)) {
                throw new Exception('The backup directory is not writable: ' . $this->backupPath);
            }

            // Build mysqldump Order
            $command = sprintf(
                '"%s" --host=%s --user=%s --password=%s --single-transaction --quick --lock-tables=false --set-gtid-purged=OFF --no-tablespaces --databases %s > %s 2>&1',
                str_replace('\\', '\\\\', $this->mysqldumpPath), // Make sure the backslashes in the path are handled correctly
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            // Output the executed command (hidden password)
            echo "Execute the command: " . preg_replace('/--password=([^ ]+)/', '--password=***', $command) . "\n";

            // Perform a backup
            $output = [];
            $returnVar = null;
            exec($command, $output, $returnVar);

            // Output command execution result
            echo "Command execution result code: " . $returnVar . "\n";
            if (!empty($output)) {
                echo "Command output:\n" . implode("\n", $output) . "\n";
            }

            if ($returnVar !== 0) {
                throw new Exception('Database backup failed, return code: ' . $returnVar . "\nCommand output: " . implode("\n", $output));
            }

            // Check backup files
            if (!file_exists($filepath)) {
                throw new Exception('Backup file not created: ' . $filepath);
            }

            $filesize = filesize($filepath);
            echo "Backup file size: " . $filesize . " bytes\n";

            if ($filesize === 0) {
                // Try to execute directly mysqldump Command view error
                $testCommand = sprintf(
                    '"%s" --version',
                    str_replace('\\', '\\\\', $this->mysqldumpPath)
                );
                exec($testCommand, $testOutput, $testReturnVar);
                throw new Exception('The backup file size is0, backup failed.\nMysqldumpTest results:' . implode("\n", $testOutput));
            }

            // Check the file content
            $fileContent = file_get_contents($filepath);
            if (empty($fileContent)) {
                throw new Exception('The backup file content is empty');
            }

            echo "File content preview (forward100character):\n";
            echo substr($fileContent, 0, 100) . "...\n";

            // Compress backup files
            $zipFilename = $filepath . '.zip';
            $zip = new ZipArchive();
            if ($zip->open($zipFilename, ZipArchive::CREATE) === TRUE) {
                if ($zip->addFile($filepath, $filename)) {
                    echo "File has been added to the zipped package\n";
                    $zip->close();
                    unlink($filepath); // Delete the originalSQLdocument
                    echo "Backup file compressed: " . $zipFilename . "\n";
                } else {
                    throw new Exception('Unable to add file to compressed package');
                }
            } else {
                throw new Exception('Unable to create a compressed file');
            }

            // Clean up old backups
            $this->cleanOldBackups();

            echo "Database backup was successful:" . $zipFilename . "\n";
            return true;
        } catch (Exception $e) {
            echo "Backup failed:" . $e->getMessage() . "\n";
            // If there are temporary files, clean them
            if (isset($filepath) && file_exists($filepath)) {
                unlink($filepath);
            }
            if (isset($zipFilename) && file_exists($zipFilename)) {
                unlink($zipFilename);
            }
            return false;
        }
    }

    private function cleanOldBackups()
    {
        $files = glob($this->backupPath . DIRECTORY_SEPARATOR . '*.zip');
        if (count($files) > $this->maxBackups) {
            // Sort by modification time
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            // Delete old files
            $filesToDelete = array_slice($files, $this->maxBackups);
            foreach ($filesToDelete as $file) {
                unlink($file);
                echo "Delete old backups:" . basename($file) . "\n";
            }
        }
    }
}

// If you run this script directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $backup = new DatabaseBackup();
    exit($backup->backup() ? 0 : 1);
} 