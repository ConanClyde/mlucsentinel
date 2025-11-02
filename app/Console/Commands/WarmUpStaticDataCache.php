<?php

namespace App\Console\Commands;

use App\Services\StaticDataCacheService;
use Illuminate\Console\Command;

class WarmUpStaticDataCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm-static-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up the static data cache for better performance';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Warming up static data cache...');

        try {
            StaticDataCacheService::warmUpCache();

            $this->info('✅ Static data cache warmed up successfully!');
            $this->line('Cached data:');
            $this->line('- Vehicle Types');
            $this->line('- Colleges');
            $this->line('- Violation Types');
            $this->line('- Admin Roles');
            $this->line('- Stakeholder Types');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Failed to warm up cache: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
