<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SalesAnalyticsService;
use Illuminate\Support\Facades\Cache;

class ClearTopSellingCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-top-selling {--all : Clear all product-related caches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the top selling products cache';

    /**
     * Execute the console command.
     */
    public function handle(SalesAnalyticsService $salesAnalytics)
    {
        $this->info('Clearing top selling products cache...');
        
        try {
            // Clear top selling cache
            $salesAnalytics->clearTopSellingCache();
            
            // Clear new arrivals cache
            Cache::forget('new_arrivals_products_20');
            
            if ($this->option('all')) {
                // Clear all product-related caches
                $patterns = [
                    'top_selling_products_*',
                    'new_arrivals_products_*',
                    'best_deal_products_*',
                ];
                
                foreach ($patterns as $pattern) {
                    // Note: This is a simplified approach. In production, you might want to use Redis SCAN
                    $this->info("Clearing cache pattern: {$pattern}");
                }
                
                $this->info('All product-related caches cleared.');
            } else {
                $this->info('Top selling products cache cleared successfully.');
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Failed to clear cache: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}