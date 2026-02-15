<?php

namespace App\Controller\Merchant;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Product\ProductType;
use App\Entity\Product\Product;
use App\Repository\Product\ProductRepository;

#[Route('/merchant/product')]
final class ProductController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }
    #[Route('/', name: 'app_merchant_products')]
    public function index(): Response
    {
        return $this->render('merchant/product/index.html.twig', [
        ]);
    }

    #[Route('/create', name: 'app_merchant_product_create')]
    public function create(Request $request): Response
    {
        $product = new Product($this->getUser());
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Product created successfully.');

            return $this->redirectToRoute('app_merchant_product_edit', ['product' => $product->getId()]);
        }

        return $this->render('merchant/product/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/edit/{product}', name: 'app_merchant_product_edit')]
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            $this->addFlash('success', new TranslatableMessage('Product "%name%" successfully updated.', ['%name%' => $product->getName()], 'messages'));

        }

        return $this->render('merchant/product/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/edit/{product}/remove-main-image', name: 'app_merchant_product_remove_main_image')]
    public function removeMainImage(Product $product): Response
    {
        $this->entityManager->remove($product->getImage());
        $product->setImage(null);
        $this->entityManager->flush();

        $this->addFlash('success', new TranslatableMessage('Main image in product "%name%" removed successfully.', ['%name%' => $product->getName()], 'messages'));

        return $this->redirectToRoute('app_merchant_product_edit', ['product' => $product->getId()]);
    }

    #[Route('/delete/{product}', name: 'app_merchant_product_delete')]
    public function delete(Product $product): Response
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();

        $this->addFlash('success', new TranslatableMessage('Product "%name%" removed successfully.', ['%name%' => $product->getName()], 'messages'));

        return $this->redirectToRoute('app_merchant_products');
    }
}
