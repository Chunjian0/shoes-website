<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$settings = \Illuminate\Support\Facades\DB::table('settings')->where('group', 'homepage')->get();

echo "Homepage Settings:\n";
foreach ($settings as $setting) {
    echo "{$setting->key}: " . ($setting->value === null ? "NULL" : $setting->value) . "\n";
} 