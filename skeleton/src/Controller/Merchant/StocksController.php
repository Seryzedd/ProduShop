<?php

namespace App\Controller\Merchant;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\Product\ProductsStocksType;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\Product\ProductRepository;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/merchant/stocks')]
final class StocksController extends AbstractController
{
    #[Route('/', name: 'app_merchant_stocks')]
    public function index(ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $products = $productRepository->findByMerchant($this->getUser());

        $form = $this->createForm(CollectionType::class, $products, [
            'entry_type' => ProductsStocksType::class,
            'label' => false
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach($products as $product) {
                $entityManager->persist($product);
            }
            
            $entityManager->flush();

            $this->AddFlash('success', 'Products packages updated.');
        }

        return $this->render('merchant/stocks/index.html.twig', [
            'form' => $form
        ]);
    }
}
