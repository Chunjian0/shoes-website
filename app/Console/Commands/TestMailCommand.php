<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test';
    protected $description = '';

    public function handle(): void
    {
        try {
            $this->info('...');
            
            Mail::raw('.', function ($message) {
                $message->to('ethankhoo09@gmail.com')
                    ->subject(' - ' . now()->format('Y-m-d H:i:s'));
            });

            $this->info('');
            Log::info('');
        } catch (\Exception $e) {
            $this->error('' . $e->getMessage());
            Log::error('', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 