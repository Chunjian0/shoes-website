<?php

class EnvironmentChecker
{
    private $requirements = [
        'php' => '8.2.0',
        'extensions' => [
            'pdo_mysql',
            'openssl',
            'mbstring',
            'tokenizer',
            'xml',
            'ctype',
            'json',
            'fileinfo',
            'gd'
        ]
    ];

    public function check()
    {
        $results = [];
        
        // examinePHPVersion
        $results['php_version'] = [
            'required' => $this->requirements['php'],
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, $this->requirements['php'], '>=')
        ];

        // examinePHPExtended
        foreach ($this->requirements['extensions'] as $extension) {
            $results['extensions'][$extension] = [
                'status' => extension_loaded($extension),
                'current' => extension_loaded($extension) ? 'Installed' : 'Not installed'
            ];
        }

        // examineComposer
        $composerVersion = shell_exec('composer --version');
        $results['composer'] = [
            'status' => !empty($composerVersion),
            'current' => $composerVersion ?? 'Not installed'
        ];

        // examineNode.js
        $nodeVersion = shell_exec('node --version');
        $results['nodejs'] = [
            'status' => !empty($nodeVersion),
            'current' => $nodeVersion ?? 'Not installed'
        ];

        // examinenpm
        $npmVersion = shell_exec('npm --version');
        $results['npm'] = [
            'status' => !empty($npmVersion),
            'current' => $npmVersion ?? 'Not installed'
        ];

        // Check storage directory permissions
        $results['storage_permissions'] = [
            'status' => $this->checkStoragePermissions(),
            'current' => $this->checkStoragePermissions() ? 'Writable' : 'Not written'
        ];

        // examine.envdocument
        $results['env_file'] = [
            'status' => file_exists('.env'),
            'current' => file_exists('.env') ? 'exist' : 'Does not exist'
        ];

        return $results;
    }

    private function checkStoragePermissions()
    {
        $paths = [
            'storage/app',
            'storage/framework',
            'storage/logs',
            'bootstrap/cache'
        ];

        foreach ($paths as $path) {
            if (!is_writable($path)) {
                return false;
            }
        }

        return true;
    }

    public function displayResults($results)
    {
        echo "Environmental inspection results:\n\n";
        
        echo "PHPVersion:\n";
        echo "Required version:{$results['php_version']['required']}\n";
        echo "Current version:{$results['php_version']['current']}\n";
        echo "state:" . ($results['php_version']['status'] ? 'pass' : 'Not passed') . "\n\n";

        echo "PHPExtensions:\n";
        foreach ($results['extensions'] as $ext => $info) {
            echo "$ext: {$info['current']}\n";
        }
        echo "\n";

        echo "Composer: {$results['composer']['current']}\n";
        echo "Node.js: {$results['nodejs']['current']}\n";
        echo "NPM: {$results['npm']['current']}\n";
        echo "Storage directory permissions: {$results['storage_permissions']['current']}\n";
        echo "Environment configuration file: {$results['env_file']['current']}\n";
    }

    public function allPassed($results)
    {
        if (!$results['php_version']['status']) return false;
        
        foreach ($results['extensions'] as $ext) {
            if (!$ext['status']) return false;
        }
        
        if (!$results['composer']['status']) return false;
        if (!$results['nodejs']['status']) return false;
        if (!$results['npm']['status']) return false;
        if (!$results['storage_permissions']['status']) return false;
        if (!$results['env_file']['status']) return false;
        
        return true;
    }
}

// If you run this script directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $checker = new EnvironmentChecker();
    $results = $checker->check();
    $checker->displayResults($results);
    exit($checker->allPassed($results) ? 0 : 1);
} 