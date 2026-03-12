<?php

namespace App\Service;

use App\Service\Api\StripeService;
use Twig\Environment;

class InvoiceService
{
    public function __construct(
        private StripeService $stripeService,
        private Environment   $twig,
    ) {}

    // =========================================================================
    // Public API
    // =========================================================================

    /**
     * Returns the rendered HTML invoice for a given PaymentIntent.
     */
    public function generateHtml(string $paymentIntentId): string
    {
        $data = $this->buildInvoiceData($paymentIntentId);

        return $this->renderTemplate($data);
    }

    /**
     * Returns the raw PDF binary for a given PaymentIntent.
     * Uses wkhtmltopdf under the hood — must be installed on the server.
     */
    public function generatePdf(string $paymentIntentId): string
    {
        $html = $this->generateHtml($paymentIntentId);

        return $this->htmlToPdf($html);
    }

    /**
     * Streams the PDF as a download response.
     * Use in a Symfony controller:
     *
     *   return new Response(
     *       $invoiceService->generatePdf($id),
     *       200,
     *       $invoiceService->pdfHeaders($id)
     *   );
     */
    public function pdfHeaders(string $paymentIntentId): array
    {
        return [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="facture-%s.pdf"', $paymentIntentId),
        ];
    }

    // =========================================================================
    // Data building
    // =========================================================================

    private function buildInvoiceData(string $paymentIntentId): array
    {
        $pi = $this->stripeService->getPaymentIntent($paymentIntentId);

        
        // --- Client ---
        $customer = is_array($pi['customer']) ? $pi['customer'] : [];
        $client = [
            'name'  => $customer['name']  ?? 'N/A',
            'email' => $customer['email'] ?? 'N/A',
        ];

        // --- Merchant ---
        $transferData   = $pi['transfer_data'] ?? [];
        $destination    = is_array($transferData['destination'] ?? null) ? $transferData['destination'] : [];
        $merchant = [
            'name'  => $destination['business_profile']['name'] ?? ($destination['email'] ?? 'N/A'),
            'email' => $destination['email'] ?? 'N/A',
            'id'    => $destination['id']    ?? ($pi['metadata']['merchant_account'] ?? 'N/A'),
        ];

        // --- Items from metadata ---
        $items = $this->extractItems($pi['metadata'] ?? []);

        // --- Amounts ---
        $totalCents    = (int) ($pi['amount'] ?? 0);
        $feeCents      = (int) ($pi['application_fee_amount'] ?? 0);
        $merchantCents = $totalCents - $feeCents;

        // TVA calculée à partir des items (taux par ligne)
        $totalHT   = array_sum(array_column($items, '_subtotal_ht'));
        $tvaAmount = array_sum(array_column($items, '_tva_amount'));
        $totalTTC  = $this->centsToEuros($totalCents);

        // Nettoyer les clés internes avant d'envoyer au template
        $items = array_map(static function (array $item): array {
            unset($item['_subtotal_ht'], $item['_tva_amount']);
            return $item;
        }, $items);

        return [
            'invoice_number' => $this->buildInvoiceNumber($pi),
            'invoice_date'   => date('d/m/Y', $pi['created']),
            'status'         => $pi['status'],
            'currency'       => strtoupper($pi['currency'] ?? 'EUR'),
            'client'         => $client,
            'merchant'       => $merchant,
            'items'          => $items,
            'amounts'        => [
                'total_ht'     => number_format($totalHT,                             2, ',', ' '),
                'tva_amount'   => number_format($tvaAmount,                           2, ',', ' '),
                'total_ttc'    => number_format($totalTTC,                            2, ',', ' '),
                'fee'          => number_format($this->centsToEuros($feeCents),       2, ',', ' '),
                'merchant_net' => number_format($this->centsToEuros($merchantCents),  2, ',', ' '),
            ],
            'payment_intent_id' => $paymentIntentId,
        ];
    }

    /**
     * Parses items from PaymentIntent metadata.
     * Expected format: "Poulet x1 @ 9.00 € tva:20.00"
     */
    private function extractItems(array $metadata): array
    {
        $raw     = $metadata['items'] ?? null;
        $decoded = $raw ? json_decode($raw, true) : [];
 
        if (!is_array($decoded)) {
            return [];
        }
 
        return array_map(function (array $item): array {
            $qty      = (int)   ($item['qty']   ?? 1);
            $priceTTC = (float) ($item['price'] ?? 0);
            $tvaRate  = (float) ($item['tva']   ?? 0);
 
            $priceHT    = $tvaRate > 0 ? $priceTTC / (1 + $tvaRate / 100) : $priceTTC;
            $subtotalHT = $priceHT * $qty;
            $tvaAmount  = ($priceTTC - $priceHT) * $qty;
 
            return [
                'name'         => $item['name'] ?? '—',
                'quantity'     => $qty,
                'tva_rate'     => $tvaRate,
                'unit_price'   => number_format($priceHT,    2, ',', ' '),
                'subtotal'     => number_format($subtotalHT, 2, ',', ' '),
                '_subtotal_ht' => $subtotalHT,
                '_tva_amount'  => $tvaAmount,
            ];
        }, $decoded);
    }

    private function buildInvoiceNumber(array $pi): string
    {
        // Format: INV-YYYYMMDD-XXXXX (last 5 chars of PI id)
        $date   = date('Ymd', $pi['created']);
        $suffix = strtoupper(substr($pi['id'], -5));

        return sprintf('INV-%s-%s', $date, $suffix);
    }

    // =========================================================================
    // Rendering
    // =========================================================================

    private function renderTemplate(array $data): string
    {
        return $this->twig->render('invoice/template.html.twig', $data);
    }

    // =========================================================================
    // PDF generation via wkhtmltopdf
    // =========================================================================

    private function htmlToPdf(string $html): string
    {
        
        $tmpHtml = tempnam(sys_get_temp_dir(), 'invoice_') . '.html';
        $tmpPdf  = tempnam(sys_get_temp_dir(), 'invoice_') . '.pdf';
 
        file_put_contents($tmpHtml, $html);
 
        $script = '/usr/local/bin/html-to-pdf.js';
 
        $cmd = sprintf(
            'NODE_PATH=/usr/local/lib/node_modules_custom/node_modules node %s %s %s 2>&1',
            escapeshellarg($script),
            escapeshellarg($tmpHtml),
            escapeshellarg($tmpPdf)
        );
 
        exec($cmd, $output, $returnCode);
 
        if ($returnCode !== 0 || !file_exists($tmpPdf)) {
            @unlink($tmpHtml);
            throw new \RuntimeException(
                'Puppeteer PDF generation failed. Check that Node, Puppeteer and Chromium are installed.'
            );
        }
 
        $pdf = file_get_contents($tmpPdf);
 
        @unlink($tmpHtml);
        @unlink($tmpPdf);
 
        return $pdf;
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function centsToEuros(int $cents): float
    {
        return $cents / 100;
    }
}
