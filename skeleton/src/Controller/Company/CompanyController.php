<?php

namespace App\Controller\Company;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\User\ProfessionalRepository;
use App\Repository\Product\ProductRepository;
use App\Entity\User\Professional;
use App\Entity\User\Client;

#[Route('/company')]
final class CompanyController extends AbstractController
{
    #[Route('/adress/{adressId}', name: 'app_companies', defaults: ['adressId' => null])]
    public function index(ProductRepository $productRepository, Request $request, ?int $adressId): Response
    {
        $radius = $request->query->get('radius') ?? 20;
        $user = $this->getUser();

        $adress = null;
        if($user) {
            $adress = $this->getUser()->getPostalAdress();
            
            if($user instanceof Client && $adressId) {
                $foundAdress = $user->getAdressById($adressId);
                if ($foundAdress) {
                    $adress = $foundAdress;
                }
            }
            
            if($adress) {
                $products = [];
                try {
                    $products = $productRepository->findWithinRadius($adress, $radius);
                } catch (\Throwable $th) {
                    dump($th);
                    $this->addFlash('danger', $th->getMessage());
                }
                
            } else {
                $products = $productRepository->findAll();
            }
        } else {
            $products = $productRepository->findAll();
        }

        return $this->render('company/company/index.html.twig', [
            'products' => $products,
            'radius' => $radius,
            'adress' => $adress
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
