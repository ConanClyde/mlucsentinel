<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SetupReverb extends Command
{
    protected $signature = 'reverb:setup';

    protected $description = 'Setup Reverb broadcasting configuration';

    public function handle()
    {
        $this->info('Setting up Reverb broadcasting...');

        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            $this->error('.env file not found!');

            return 1;
        }

        $envContent = file_get_contents($envPath);

        // Check if Reverb is already configured
        if (strpos($envContent, 'REVERB_APP_KEY') !== false) {
            $this->warn('Reverb configuration already exists in .env file.');
            if (! $this->confirm('Do you want to regenerate the keys?')) {
                return 0;
            }
        }

        // Generate random keys
        $appKey = Str::random(32);
        $appSecret = Str::random(32);

        $reverbConfig = "\n# Reverb Broadcasting Configuration\n";
        $reverbConfig .= "BROADCAST_CONNECTION=reverb\n";
        $reverbConfig .= "REVERB_APP_ID=1\n";
        $reverbConfig .= "REVERB_APP_KEY={$appKey}\n";
        $reverbConfig .= "REVERB_APP_SECRET={$appSecret}\n";
        $reverbConfig .= "REVERB_HOST=localhost\n";
        $reverbConfig .= "REVERB_PORT=8080\n";
        $reverbConfig .= "REVERB_SCHEME=http\n\n";
        $reverbConfig .= "VITE_REVERB_APP_KEY=\"\${REVERB_APP_KEY}\"\n";
        $reverbConfig .= "VITE_REVERB_HOST=\"\${REVERB_HOST}\"\n";
        $reverbConfig .= "VITE_REVERB_PORT=\"\${REVERB_PORT}\"\n";
        $reverbConfig .= "VITE_REVERB_SCHEME=\"\${REVERB_SCHEME}\"\n";

        // Update BROADCAST_CONNECTION if it exists
        if (preg_match('/^BROADCAST_CONNECTION=.*/m', $envContent)) {
            $envContent = preg_replace('/^BROADCAST_CONNECTION=.*/m', 'BROADCAST_CONNECTION=reverb', $envContent);
        }

        // Remove existing Reverb config if present
        $envContent = preg_replace('/# Reverb Broadcasting Configuration.*?(?=\n[A-Z_]+=|\n$)/s', '', $envContent);
        $envContent = preg_replace('/^REVERB_.*\n/m', '', $envContent);
        $envContent = preg_replace('/^VITE_REVERB_.*\n/m', '', $envContent);

        // Append new config
        $envContent = rtrim($envContent)."\n".$reverbConfig;

        file_put_contents($envPath, $envContent);

        $this->info('✅ Reverb configuration added to .env file');
        $this->newLine();
        $this->info('Generated credentials:');
        $this->line("  REVERB_APP_KEY: {$appKey}");
        $this->line("  REVERB_APP_SECRET: {$appSecret}");
        $this->newLine();
        $this->warn('Next steps:');
        $this->line('  1. Run: npm run dev');
        $this->line('  2. Run: php artisan reverb:start');
        $this->line('  3. Visit: /users/administrators');

        return 0;
    }
}
