<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GeneratePdfCertificate extends Command
{
    protected $signature = 'pdf:generate-certificate';
    protected $description = 'Generate for PDF Digitally signed certificate';

    public function handle(): int
    {
        $this->info('Start generating certificates...');

        $certsPath = storage_path('certs');
        if (!file_exists($certsPath)) {
            mkdir($certsPath, 0755, true);
        }

        // Configuration information
        $dn = [
            'countryName' => 'MY',
            'stateOrProvinceName' => 'Penang',
            'localityName' => 'Penang',
            'organizationName' => 'OpticSystem',
            'commonName' => 'localhost',
            'emailAddress' => 'ethankhoo09@gmail.com'
        ];

        // Generate private key configuration
        $config = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'digest_alg' => 'sha256',
        ];

        try {
            // Generate a private key
            $privateKey = openssl_pkey_new($config);
            if ($privateKey === false) {
                throw new \RuntimeException('Failed to generate a private key: ' . openssl_error_string());
            }

            // Export private key
            if (!openssl_pkey_export($privateKey, $privateKeyPEM)) {
                throw new \RuntimeException('Exporting private key failed: ' . openssl_error_string());
            }
            file_put_contents($certsPath . '/private.key', $privateKeyPEM);

            // Generate a certificate
            $x509 = openssl_csr_new($dn, $privateKey, $config);
            if ($x509 === false) {
                throw new \RuntimeException('Failed to generate a certificate request: ' . openssl_error_string());
            }

            // Self-signed certificate
            $cert = openssl_csr_sign($x509, null, $privateKey, 365, $config);
            if ($cert === false) {
                throw new \RuntimeException('Signing certificate failed: ' . openssl_error_string());
            }

            // Export the certificate
            if (!openssl_x509_export($cert, $certificatePEM)) {
                throw new \RuntimeException('Failed to export the certificate: ' . openssl_error_string());
            }
            file_put_contents($certsPath . '/certificate.crt', $certificatePEM);

            $this->info('Certificate generation is complete!');
            $this->info('Save the certificate file in: ' . $certsPath);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate a certificate: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 