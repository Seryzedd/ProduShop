<?php

namespace App\Controller\Product;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\Product\ProductRepository;
use App\Entity\Product;

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route('/shelf/{shelf}', name: 'app_shelf_product', defaults: ['shelf' => 'All'])]
    public function index(string $shelf, ProductRepository $productRepository): Response
    {
        if($shelf === "All") {
            $products = $productRepository->findAll();
        } else {
            $products = $productRepository->findByShelf($shelf);
        }
        return $this->render('product/product/index.html.twig', [
            'products' => $products,
            'shelf' => $shelf
        ]);
    }

    #[Route('/package/{id}', name: 'app_view_product')]
    public function view(Product\Package $package): Response
    {
        return $this->render('product/product/view.html.twig', [
            'package' => $package
        ]);
    }
}
