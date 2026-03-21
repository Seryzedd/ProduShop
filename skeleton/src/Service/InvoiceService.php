<?php

namespace App\Service;

use App\Entity\User\Order;
use App\Entity\User\Payment\Payment;
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
     * Génère la facture HTML depuis un Order (BDD) enrichi des données Stripe.
     */
    public function generateHtmlFromOrder(Order $order): string
    {
        $data = $this->buildInvoiceDataFromOrder($order);
        return $this->renderTemplate($data);
    }

    /**
     * Génère le PDF depuis un Order.
     */
    public function generatePdfFromOrder(Order $order): string
    {
        return $this->htmlToPdf($this->generateHtmlFromOrder($order));
    }

    /**
     * Génère la facture HTML depuis un Payment (point d'entrée recommandé).
     */
    public function generateHtmlFromPayment(Payment $payment): string
    {
        $data = $this->buildInvoiceDataFromPayment($payment);
        return $this->renderTemplate($data);
    }

    /**
     * Génère le PDF depuis un Payment.
     */
    public function generatePdfFromPayment(Payment $payment): string
    {
        return $this->htmlToPdf($this->generateHtmlFromPayment($payment));
    }

    /**
     * Génère la facture HTML depuis un PaymentIntent Stripe (fallback).
     */
    public function generateHtml(string $paymentIntentId): string
    {
        $data = $this->buildInvoiceData($paymentIntentId);
        return $this->renderTemplate($data);
    }

    /**
     * Génère le PDF depuis un PaymentIntent Stripe (fallback).
     */
    public function generatePdf(string $paymentIntentId): string
    {
        return $this->htmlToPdf($this->generateHtml($paymentIntentId));
    }

    public function pdfHeaders(string $ref): array
    {
        return [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="facture-%s.pdf"', $ref),
        ];
    }

    // =========================================================================
    // Data building — depuis Payment (point d'entrée recommandé)
    // =========================================================================

    private function buildInvoiceDataFromPayment(Payment $payment): array
    {
        $client = null;
        if ($payment->getCustomer()) {
            $c      = $payment->getCustomer();
            $client = [
                'name'  => $c->getFirstname() . ' ' . $c->getLastname(),
                'email' => $c->getEmail(),
                'phone' => $c->getPhone(),
            ];
        }

        $items     = [];
        $merchants = [];
        $totalHT   = 0.0;
        $tvaAmount = 0.0;
        $currency  = 'EUR';
        $paidAt    = null;
        $intentId  = '';

        foreach ($payment->getOrders() as $order) {
            $currency = strtoupper($order->getCurrency());
            $intentId = $order->getIntentId() ?? $intentId;

            if ($order->getPaidAt()) {
                $paidAt = $order->getPaidAt()->format('d/m/Y H:i');
            }

            // Merchant directement sur Order — pas besoin de passer par OrderItem
            $merchant   = $order->getMerchant();
            $merchantId = $merchant?->getId();

            if ($merchant && !isset($merchants[$merchantId])) {
                $merchants[$merchantId] = [
                    'name'    => $merchant->getCompanyName(),
                    'email'   => $merchant->getEmail(),
                    'phone'   => $merchant->getPhone(),
                    'address' => $merchant->getAdress()->getFullAddress(),
                    'siret'   => $merchant->getSiret(),
                ];
            }

            foreach ($order->getOrderItems() as $orderItem) {
                $package    = $orderItem->getPackage();
                $qty        = $orderItem->getQuantity();
                $priceTTC   = $orderItem->getUnitPrice();
                $tvaRate    = ($package->getTaxe() * 100) ?? 0.0;
                $priceHT    = $tvaRate > 0 ? $priceTTC / (1 + $tvaRate / 100) : $priceTTC;
                $subtotalHT = $priceHT * $qty;
                $tvaSub     = ($priceTTC - $priceHT) * $qty;
                $totalHT   += $subtotalHT;
                $tvaAmount += $tvaSub;

                $items[] = [
                    'name'          => $package->getName(),
                    'quantity'      => $qty,
                    'tva_rate'      => $tvaRate,
                    'merchant_name' => $merchant?->getCompanyName() ?? '—',
                    'merchant_id'   => $merchantId,
                    'unit_price'    => number_format($priceHT,    2, ',', ' '),
                    'subtotal'      => number_format($subtotalHT, 2, ',', ' '),
                ];
            }
        }

        $totalTTC = $this->centsToEuros($payment->getAmount());

        return [
            'invoice_number'    => sprintf('INV-%s-%05d', $payment->getCreatedAt()->format('Ymd'), $payment->getId()),
            'invoice_date'      => $payment->getCreatedAt()->format('d/m/Y'),
            'paid_at'           => $paidAt,
            'status'            => $payment->getStatus(),
            'currency'          => $currency,
            'client'            => $client,
            'merchants'         => array_values($merchants),
            'items'             => $items,
            'amounts'           => [
                'total_ht'   => number_format($totalHT,   2, ',', ' '),
                'tva_amount' => number_format($tvaAmount, 2, ',', ' '),
                'total_ttc'  => number_format($totalTTC,  2, ',', ' '),
            ],
            'payment_intent_id' => $intentId,
        ];
    }

    // =========================================================================
    // Data building — depuis Order (BDD)
    // =========================================================================

    private function buildInvoiceDataFromOrder(Order $order): array
    {
        // Client depuis Payment → Customer
        $client = null;
        if ($order->getPayment()?->getCustomer()) {
            $c      = $order->getPayment()->getCustomer();
            $client = [
                'name'  => $c->getFirstname() . ' ' . $c->getLastname(),
                'email' => $c->getEmail(),
                'phone' => $c->getPhone(),
            ];
        }

        // Items groupés par marchand via Order::getItemsByMerchant()
        $groupedByMerchant = $order->getItemsByMerchant();
        $items             = [];
        $merchants         = [];
        $totalHT           = 0.0;
        $tvaAmount         = 0.0;

        foreach ($groupedByMerchant as $merchantId => $group) {
            $merchant = $group['merchant'];

            $merchants[$merchantId] = [
                'name'    => $merchant->getCompanyName(),
                'email'   => $merchant->getEmail(),
                'phone'   => $merchant->getPhone(),
                'address' => $merchant->getAdress()->getFullAddress(),
                'siret'   => $merchant->getSiret(),
            ];

            foreach ($group['items'] as $orderItem) {
                $package  = $orderItem->getPackage();
                $qty      = $orderItem->getQuantity();
                $priceTTC = $orderItem->getUnitPrice();
                $tvaRate  = $package->getTaxe() ?? 0.0;

                $priceHT    = $tvaRate > 0 ? $priceTTC / (1 + $tvaRate / 100) : $priceTTC;
                $subtotalHT = $priceHT * $qty;
                $tvaSub     = ($priceTTC - $priceHT) * $qty;

                $totalHT   += $subtotalHT;
                $tvaAmount += $tvaSub;

                $items[] = [
                    'name'          => $package->getName(),
                    'quantity'      => $qty,
                    'tva_rate'      => $tvaRate,
                    'merchant_name' => $merchant->getCompanyName(),
                    'merchant_id'   => $merchantId,
                    'unit_price'    => number_format($priceHT,    2, ',', ' '),
                    'subtotal'      => number_format($subtotalHT, 2, ',', ' '),
                ];
            }
        }

        $totalTTC = $this->centsToEuros($order->getAmount());

        return [
            'invoice_number'    => $this->buildInvoiceNumberFromOrder($order),
            'invoice_date'      => $order->getCreatedAt()->format('d/m/Y'),
            'paid_at'           => $order->getPaidAt()?->format('d/m/Y H:i'),
            'status'            => $order->getStatus(),
            'currency'          => strtoupper($order->getCurrency()),
            'client'            => $client,
            'merchants'         => array_values($merchants),
            'items'             => $items,
            'amounts'           => [
                'total_ht'   => number_format($totalHT,   2, ',', ' '),
                'tva_amount' => number_format($tvaAmount, 2, ',', ' '),
                'total_ttc'  => number_format($totalTTC,  2, ',', ' '),
            ],
            'payment_intent_id' => $order->getIntentId(),
        ];
    }

    private function buildInvoiceNumberFromOrder(Order $order): string
    {
        return sprintf(
            'INV-%s-%05d',
            $order->getCreatedAt()->format('Ymd'),
            $order->getId()
        );
    }

    // =========================================================================
    // Data building — depuis Stripe (fallback)
    // =========================================================================

    private function buildInvoiceData(string $paymentIntentId): array
    {
        $pi = $this->stripeService->getPaymentIntent($paymentIntentId);

        $customer = is_array($pi['customer']) ? $pi['customer'] : [];
        $client   = [
            'name'  => $customer['name']  ?? 'N/A',
            'email' => $customer['email'] ?? 'N/A',
            'phone' => null,
        ];

        dump($pi);

        $merchants = $this->extractMerchants($pi['metadata'] ?? []);
        $items     = $this->extractItems($pi['metadata'] ?? []);

        $totalCents = (int) ($pi['amount'] ?? 0);
        $totalHT    = array_sum(array_column($items, '_subtotal_ht'));
        $tvaAmount  = array_sum(array_column($items, '_tva_amount'));
        $totalTTC   = $this->centsToEuros($totalCents);

        $items = array_map(static function (array $item): array {
            unset($item['_subtotal_ht'], $item['_tva_amount']);
            return $item;
        }, $items);

        return [
            'invoice_number'    => $this->buildInvoiceNumber($pi),
            'invoice_date'      => date('d/m/Y', $pi['created']),
            'paid_at'           => null,
            'status'            => $pi['status'],
            'currency'          => strtoupper($pi['currency'] ?? 'EUR'),
            'client'            => $client,
            'merchants'         => $merchants,
            'items'             => $items,
            'amounts'           => [
                'total_ht'   => number_format($totalHT,   2, ',', ' '),
                'tva_amount' => number_format($tvaAmount, 2, ',', ' '),
                'total_ttc'  => number_format($totalTTC,  2, ',', ' '),
            ],
            'payment_intent_id' => $paymentIntentId,
        ];
    }

    private function extractItems(array $metadata): array
    {
        $raw     = $metadata['items'] ?? null;
        $decoded = $raw ? json_decode($raw, true) : [];

        if (!is_array($decoded)) return [];

        return array_map(function (array $item): array {
            $qty      = (int)   ($item['qty']   ?? 1);
            $priceTTC = (float) ($item['price'] ?? 0);
            $tvaRate  = (float) ($item['tva']   ?? 0);

            $priceHT    = $tvaRate > 0 ? $priceTTC / (1 + $tvaRate / 100) : $priceTTC;
            $subtotalHT = $priceHT * $qty;
            $tvaAmount  = ($priceTTC - $priceHT) * $qty;

            return [
                'name'          => $item['name'] ?? '—',
                'quantity'      => $qty,
                'tva_rate'      => $tvaRate,
                'merchant_name' => $item['merchant']['name'] ?? '—',
                'merchant'   => ['id' => $item['merchant']['id']   ?? null, ],
                'unit_price'    => number_format($priceHT,    2, ',', ' '),
                'subtotal'      => number_format($subtotalHT, 2, ',', ' '),
                '_subtotal_ht'  => $subtotalHT,
                '_tva_amount'   => $tvaAmount,
            ];
        }, $decoded);
    }

    private function extractMerchants(array $metadata): array
    {
        $raw     = $metadata['items'] ?? null;
        $decoded = $raw ? json_decode($raw, true) : [];

        $merchants = [];
        foreach ($decoded as $item) {
            $id   = $item['merchant']['id']   ?? null;
            $name = $item['merchant']['name'] ?? null;
            if ($id && !isset($merchants[$id])) {
                $merchants[$id] = ['name' => $name ?? '—', 'email' => null, 'phone' => null, 'address' => null, 'siret' => null];
            }
        }

        return array_values($merchants);
    }

    private function buildInvoiceNumber(array $pi): string
    {
        return sprintf('INV-%s-%s', date('Ymd', $pi['created']), strtoupper(substr($pi['id'], -5)));
    }

    // =========================================================================
    // Rendering
    // =========================================================================

    private function renderTemplate(array $data): string
    {
        return $this->twig->render('invoice/template.html.twig', $data);
    }

    // =========================================================================
    // PDF generation via Puppeteer/Chromium
    // =========================================================================

    private function htmlToPdf(string $html): string
    {
        $tmpDir  = sys_get_temp_dir();
        $tmpHtml = $tmpDir . '/invoice_' . uniqid() . '.html';
        $tmpPdf  = $tmpDir . '/invoice_' . uniqid() . '.pdf';

        file_put_contents($tmpHtml, $html);

        $cmd = sprintf(
            'NODE_PATH=/usr/local/lib/node_modules /usr/local/bin/node %s %s %s 2>&1',
            escapeshellarg('/usr/local/bin/html-to-pdf.js'),
            escapeshellarg($tmpHtml),
            escapeshellarg($tmpPdf)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($tmpPdf)) {
            @unlink($tmpHtml);
            throw new \RuntimeException('Puppeteer PDF generation failed: ' . implode("\n", $output));
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