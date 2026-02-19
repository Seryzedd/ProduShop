<?php

namespace App\Controller\Product;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product')]
final class ShelfController extends AbstractController
{
    #[Route('/shelf/{name}', name: 'app_product_shelf', defaults: ['name' => 'All'])]
    public function index(): Response
    {
        
        return $this->render('product/shelf/index.html.twig', [
            'controller_name' => 'ShelfController',
        ]);
    }
}
