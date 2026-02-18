<?php

namespace App\Controller\Admin\Product;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/product/shelf')]
final class ShelfController extends AbstractController
{
    #[Route('/', name: 'app_admin_product_shelf')]
    public function index(): Response
    {
        return $this->render('admin/product/shelf/index.html.twig', [
            
        ]);
    }
}
