<?php

namespace App\Controller\Company;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\User\ProfessionalRepository;
use App\Repository\Product\ProductRepository;
use App\Entity\User\Professional;

#[Route('/company')]
final class CompanyController extends AbstractController
{
    #[Route('/', name: 'app_companies')]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        $radius = $request->query->get('radius') ?? 20;
        if($this->getUser()) {
            $adress = $this->getUser()->getPostalAdress();
            if($adress) {
                $products = $productRepository->findWithinRadius($adress, $radius);
            } else {
                $products = $productRepository->findAll();
            }
        } else {
            $products = $productRepository->findAll();
        }

        return $this->render('company/company/index.html.twig', [
            'products' => $products,
            'radius' => $radius
        ]);
    }

    #[Route('/{id}', name: 'app_company')]
    public function view(Professional $professional, Request $request): Response
    {
        
        return $this->render('company/company/view.html.twig', [
            'products' => $professional->getProducts(),
            'professional' => $professional,
            'radius' => $request->query->get('radius') ?? 20
        ]);
    }
}
