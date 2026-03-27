<?php

namespace App\Controller\Product;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\Product\ProductRepository;
use App\Entity\Product;
use App\Entity\User\PostalAdress\Adress;
use App\Entity\User\Client;
use App\Entity\User\Professional;
use App\Entity\Product\Shelf;

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route('/shelf/{shelf}/adress/{adressId}', name: 'app_shelf_product', defaults: ['shelf' => 'All', 'radius' => 20, 'adressId' => null])]
    public function index(?string $shelf, ?int $adressId, ProductRepository $productRepository, Request $request): Response
    {
        $user = $this->getUser();
        $adress = $this->getUser()->getPostalAdress();

        $radius = $request->query->get('radius') ?? 20;
        if($adress) {
            $adress = $user->getPostalAdress();
            
            if($user instanceof Client && $adressId) {
                $foundAdress = $user->getAdressById($adressId);
                if ($foundAdress) {
                    $adress = $foundAdress;
                }
            }

            if($shelf === "All") {
                $products = $productRepository->findWithinRadius($adress, $radius);
            } else {
                $products = $productRepository->findWithinRadius($adress, $radius, $shelf);
            }
        } else {
            if($shelf === "All") {
                $products = $productRepository->findAll();
            } else {
                $products = $productRepository->findByShelf($shelf);
            }
        }

        return $this->render('product/product/index.html.twig', [
            'products' => $products,
            'shelf' => $shelf,
            'adress' => $adress,
            'radius' => (int) $radius
        ]);
    }

    #[Route('/package/{id}', name: 'app_view_product')]
    public function view(Product\Package $package): Response
    {
        return $this->render('product/product/view.html.twig', [
            'package' => $package
        ]);
    }

    #[Route('/show/{id}', name: 'app_show_product')]
    public function productView(Product\Product $product)
    {
        return $this->redirectToRoute('app_view_product', ['id' => $product->getPackages()->first()]);
    }

    private function getAdress(): ?Adress
    {
        $user = $this->getUser();

        if($user) {
            return $user->getPostalAdress();
        }

        return null;
    }
}
