<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearProductCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all product-related cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing product cache...');
        
        try {
            // Use CacheService which properly handles database cache
            // This prevents clearing ALL cache which causes performance issues
            \App\Services\CacheService::clearProductCaches();
            $this->info('Product cache cleared successfully using CacheService!');
        } catch (\Exception $e) {
            $this->error('Failed to clear product cache: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
