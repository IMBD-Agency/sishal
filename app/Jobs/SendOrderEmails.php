<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Mail\OrderConfirmation;
use App\Mail\OrderNotificationToOwner;
use App\Services\SmtpConfigService;
use Illuminate\Support\Facades\Mail;

class SendOrderEmails implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $orderId,
        public ?string $customerEmail = null
    ) {
        // Use 'database' connection - job will be processed after HTTP response
        // If queue worker is running, emails process in background
        // If not, they'll queue and can be processed later
        $this->onConnection('database');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::with(['items.product', 'items.variation', 'invoice.invoiceAddress'])
            ->find($this->orderId);

        if (!$order) {
            return;
        }

        if (!SmtpConfigService::configureFromSettings()) {
            return;
        }

        $customerEmail = $this->customerEmail ?: $order->email;
        if ($customerEmail) {
            try {
                Mail::mailer('smtp')->to($customerEmail)->send(new OrderConfirmation($order));
            } catch (\Exception $e) {
                \Log::error('Failed to send order confirmation email', [
                    'order_id' => $this->orderId,
                    'email' => $customerEmail,
                    'error' => $e->getMessage()
                ]);
                throw $e; // Retry job
            }
        }

        $ownerEmail = SmtpConfigService::getContactEmail();
        if ($ownerEmail && filter_var($ownerEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::mailer('smtp')->to($ownerEmail)->send(new OrderNotificationToOwner($order));
            } catch (\Exception $e) {
                \Log::error('Failed to send owner notification email', [
                    'order_id' => $this->orderId,
                    'email' => $ownerEmail,
                    'error' => $e->getMessage()
                ]);
                // Don't throw - owner email failure shouldn't retry entire job
            }
        }
    }

}
