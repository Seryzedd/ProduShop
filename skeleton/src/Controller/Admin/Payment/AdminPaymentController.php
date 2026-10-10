<?php

namespace App\Controller\Admin\Payment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\User\Payment\PaymentRepository;
use App\Entity\User\Payment\Payment;
use \DateTime;

#[Route('/admin/user/payment')]
final class AdminPaymentController extends AbstractController
{
    #[Route('/', name: 'app_admin_payments')]
    public function index(PaymentRepository $paymentRepository): Response
    {
        $payments = $paymentRepository->findByOrdered();

        $date = new DateTime();

        $lastYearFees = $paymentRepository->getPlatformFeesTotalBetween($date, $date->modify('+12 months'));

        return $this->render('admin/payment/admin_payment/index.html.twig', [
            'payments' => $payments,
            'lastYearFees' => $lastYearFees
        ]);
    }

    #[Route('/{id}', name: 'app_admin_payment')]
    public function paymentDetails(Payment $payment): Response
    {

        return $this->render('admin/payment/admin_payment/view.html.twig', [
            'payment' => $payment
        ]);
    }
}
