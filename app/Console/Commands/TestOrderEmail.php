<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Mail\OrderConfirmation;
use App\Mail\OrderNotificationToOwner;
use App\Services\SmtpConfigService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class TestOrderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:test-email 
                            {order_id : The ID of the order to test}
                            {--email= : Override recipient email address}
                            {--sync : Send synchronously instead of queuing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test order confirmation and notification emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');
        $overrideEmail = $this->option('email');
        $sync = $this->option('sync');

        // Load order with relationships
        $order = Order::with([
            'items.product',
            'items.variation',
            'invoice.invoiceAddress'
        ])->find($orderId);

        if (!$order) {
            $this->error("Order #{$orderId} not found!");
            return 1;
        }

        $this->info("Testing emails for Order: {$order->order_number}");

        // Configure SMTP
        $smtpConfigured = SmtpConfigService::configureFromSettings();
        
        if (!$smtpConfigured) {
            $this->error("SMTP is not configured! Please configure SMTP settings first.");
            return 1;
        }

        $this->info("✓ SMTP configured successfully");

        // Test customer email
        $customerEmail = $overrideEmail ?: $order->email;
        if ($customerEmail) {
            $this->info("\nSending Order Confirmation to: {$customerEmail}");
            try {
                if ($sync) {
                    Mail::mailer('smtp')->to($customerEmail)->send(new OrderConfirmation($order));
                    $this->info("✓ Order confirmation email sent synchronously");
                } else {
                    Mail::mailer('smtp')->to($customerEmail)->queue(new OrderConfirmation($order));
                    $this->info("✓ Order confirmation email queued");
                    $this->warn("  Note: Email is queued. Run 'php artisan queue:work' to process it.");
                }
            } catch (\Exception $e) {
                $this->error("✗ Failed to send order confirmation email: " . $e->getMessage());
                return 1;
            }
        } else {
            $this->warn("⚠ No customer email found for this order");
        }

        // Test owner notification email
        $ownerEmail = SmtpConfigService::getContactEmail();
        if ($ownerEmail && filter_var($ownerEmail, FILTER_VALIDATE_EMAIL)) {
            $this->info("\nSending Owner Notification to: {$ownerEmail}");
            try {
                if ($sync) {
                    Mail::mailer('smtp')->to($ownerEmail)->send(new OrderNotificationToOwner($order));
                    $this->info("✓ Owner notification email sent synchronously");
                } else {
                    Mail::mailer('smtp')->to($ownerEmail)->queue(new OrderNotificationToOwner($order));
                    $this->info("✓ Owner notification email queued");
                    $this->warn("  Note: Email is queued. Run 'php artisan queue:work' to process it.");
                }
            } catch (\Exception $e) {
                $this->error("✗ Failed to send owner notification email: " . $e->getMessage());
                return 1;
            }
        } else {
            $this->warn("⚠ Owner email not configured or invalid");
        }

        // Check queue status
        if (!$sync) {
            $queuedJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();
            
            $this->info("\n--- Queue Status ---");
            $this->info("Queued jobs: {$queuedJobs}");
            if ($failedJobs > 0) {
                $this->warn("Failed jobs: {$failedJobs}");
                $this->warn("Run 'php artisan queue:failed' to see failed jobs");
            }
        }

        $this->info("\n✓ Email test completed!");
        return 0;
    }
}

