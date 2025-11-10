<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\GeneralSetting;
use App\Services\InvoicePdfService;

class OrderNotificationToOwner extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $generalSettings;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        // Load necessary relationships for the email template and PDF
        $order->loadMissing([
            'items.product',
            'items.variation',
            'invoice.invoiceAddress'
        ]);
        
        $this->order = $order;
        $this->generalSettings = GeneralSetting::first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Order Received - #' . $this->order->order_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-notification-to-owner',
            with: [
                'order' => $this->order,
                'generalSettings' => $this->generalSettings,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        \Log::info('=== ATTACHMENTS METHOD CALLED ===', [
            'order_id' => $this->order->id ?? 'N/A',
            'order_number' => $this->order->order_number ?? 'N/A'
        ]);

        try {
            // Generate PDF directly
            $pdfBinary = InvoicePdfService::generateAsBinary($this->order);
            
            if (!$pdfBinary || empty($pdfBinary)) {
                \Log::warning('Failed to generate PDF binary for owner email attachment', [
                    'order_id' => $this->order->id,
                    'order_number' => $this->order->order_number,
                    'pdf_binary_length' => $pdfBinary ? strlen($pdfBinary) : 0
                ]);
                
                return [];
            }

            $filename = InvoicePdfService::getFilename($this->order);
            $pdfSize = strlen($pdfBinary);

            \Log::info('PDF generated successfully, creating attachment', [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'pdf_size' => $pdfSize . ' bytes',
                'filename' => $filename
            ]);

            // Use a closure that generates PDF on demand (better for queued emails)
            $orderId = $this->order->id;
            
            $attachment = Attachment::fromData(
                function () use ($orderId) {
                    \Log::info('PDF attachment closure executing', ['order_id' => $orderId]);
                    
                    $order = \App\Models\Order::with([
                        'items.product',
                        'items.variation',
                        'invoice.invoiceAddress'
                    ])->find($orderId);

                    if (!$order) {
                        \Log::error('Order not found in attachment closure', ['order_id' => $orderId]);
                        return '';
                    }

                    $pdfBinary = InvoicePdfService::generateAsBinary($order);
                    
                    if (!$pdfBinary) {
                        \Log::warning('PDF generation failed in closure', ['order_id' => $orderId]);
                        return '';
                    }

                    \Log::info('PDF generated in closure', [
                        'order_id' => $orderId,
                        'size' => strlen($pdfBinary) . ' bytes'
                    ]);

                    return $pdfBinary;
                },
                $filename
            )->withMime('application/pdf');

            \Log::info('Attachment created successfully', [
                'order_id' => $this->order->id,
                'filename' => $filename
            ]);

            return [$attachment];
        } catch (\Exception $e) {
            \Log::error('Exception while generating PDF attachment for owner email', [
                'order_id' => $this->order->id ?? 'N/A',
                'order_number' => $this->order->order_number ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [];
        }
    }
}

