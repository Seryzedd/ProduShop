<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Repository\User\Payment\PaymentRepository;
use App\Repository\Product\ProductRepository;
use App\Service\Translation\TranslationFileReader;
use App\Service\Sales\SalesStatsService;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_index')]
    public function index(
        UserRepository $usersRespository,
        PaymentRepository $paymentRepository,
        ProductRepository $productRepository,
        TranslationFileReader $translationFileService,
        SalesStatsService $salesStatsService
    ): Response {
        return $this->render('admin/admin/index.html.twig', [
            'usersStats' => $usersRespository->getStats(),
            'paymentStats' => $paymentRepository->getPaymentStats(),
            'productsStats' => $productRepository->getStats(),
            'translations' => $translationFileService->readAllTranslationFiles(),
            'regionClientPercent' => $usersRespository->getClientPercentageByDepartment(),
            'regionProPercent' => $usersRespository->getProPercentageByDepartment(),
            'salesStats' => $salesStatsService->getMonthlySalesStats()
        ]);
    }
}
