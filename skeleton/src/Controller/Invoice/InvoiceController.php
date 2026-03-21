<?php

namespace App\Controller\Invoice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\InvoiceService;
use App\Entity\User\Payment\Payment;

#[Route('/invoice')]
final class InvoiceController extends AbstractController
{
    #[Route('/{id}', name: 'app_invoice_show')]
    public function show(string $id): Response
    {
        return $this->render('invoice/show.html.twig', [
            'payment_intent_id' => $id,
        ]);
    }

    // Route qui retourne le HTML brut de la facture
    #[Route('/invoice/{id}/preview', name: 'invoice_preview')]
    public function preview(Payment $payment, InvoiceService $invoiceService): Response
    {
        return new Response(
            $invoiceService->generateHtmlFromPayment($payment),
            200,
            ['Content-Type' => 'text/html']
        );
    }

    // Route PDF — déclenche le téléchargement
    #[Route('/{id}/pdf', name: 'invoice_pdf')]
    public function pdf(Payment $id, InvoiceService $invoiceService): Response
    {
        return new Response(
            $invoiceService->generatePdfFromPayment($id),
            200,
            $invoiceService->pdfHeaders($id->getCreatedAt()->format('d-m-Y'))
        );
    }
}
