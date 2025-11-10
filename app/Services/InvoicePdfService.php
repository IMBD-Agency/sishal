<?php

namespace App\Services;

use App\Models\Order;
use App\Models\GeneralSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Exception;

class InvoicePdfService
{
    /**
     * Generate PDF invoice for an order.
     *
     * @param Order $order
     * @return \Barryvdh\DomPDF\PDF|null
     */
    public static function generate(Order $order): ?\Barryvdh\DomPDF\PDF
    {
        try {
            Log::info('InvoicePdfService::generate called', [
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);

            // Ensure all necessary relationships are loaded
            $order->loadMissing([
                'items.product',
                'items.variation',
                'invoice.invoiceAddress'
            ]);

            Log::info('Order relationships loaded', [
                'order_id' => $order->id,
                'items_count' => $order->items->count(),
                'has_invoice' => $order->invoice ? 'yes' : 'no'
            ]);

            // Get general settings (cache if needed for performance)
            $generalSettings = GeneralSetting::first();

            Log::info('Loading PDF view', [
                'order_id' => $order->id,
                'view' => 'emails.order-invoice-pdf'
            ]);

            // Generate PDF
            $pdf = Pdf::loadView('emails.order-invoice-pdf', [
                'order' => $order,
                'generalSettings' => $generalSettings,
            ]);

            // Set paper size and orientation
            $pdf->setPaper('A4', 'portrait');

            // Set PDF options for better compatibility
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);

            Log::info('PDF object created successfully', [
                'order_id' => $order->id
            ]);

            return $pdf;
        } catch (Exception $e) {
            Log::error('Failed to generate invoice PDF', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Generate PDF invoice and return as binary data.
     *
     * @param Order $order
     * @return string|null
     */
    public static function generateAsBinary(Order $order): ?string
    {
        Log::info('InvoicePdfService::generateAsBinary called', [
            'order_id' => $order->id,
            'order_number' => $order->order_number
        ]);

        $pdf = self::generate($order);
        
        if (!$pdf) {
            Log::warning('PDF object is null, cannot generate binary', [
                'order_id' => $order->id
            ]);
            return null;
        }

        try {
            Log::info('Calling PDF output() method', [
                'order_id' => $order->id
            ]);

            $binary = $pdf->output();
            
            Log::info('PDF output generated', [
                'order_id' => $order->id,
                'binary_length' => strlen($binary) . ' bytes'
            ]);

            return $binary;
        } catch (Exception $e) {
            Log::error('Failed to output invoice PDF', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Get the filename for the invoice PDF.
     *
     * @param Order $order
     * @return string
     */
    public static function getFilename(Order $order): string
    {
        return 'invoice-' . $order->order_number . '.pdf';
    }
}

