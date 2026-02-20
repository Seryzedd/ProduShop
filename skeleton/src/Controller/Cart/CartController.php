<?php

namespace App\Controller\Cart;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;
use App\Service\Cart\CartService;
use App\Entity\Product\Package;

#[Route('/cart')]
final class CartController extends AbstractController
{
    public function __construct(private CartService $cart) {}

    #[Route('/', name: 'app_cart')]
    public function index(): Response
    {
        return $this->render('cart/cart/index.html.twig', [
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add')]
    public function add(Package $package): Response
    {
        if($package->getStock() > 0) {
            $this->cart->add($package);

            $this->addFlash('success', new TranslatableMessage(
                'Product "%name%" x%quantity% added to cart.', [
                    '%name%' => $package->getProduct()->getName(),
                    '%quantity%' => $package->getQuantity()
                ]
            ));
        } else {
            $this->addFlash('danger', 'Not enough stock for this package.');
        }
        
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove')]
    public function remove(Package $package): Response
    {
        $this->cart->remove($package->getId());
        
        $this->addFlash('success', new TranslatableMessage(
            'Product "%name%" x%quantity% added to cart.', [
                '%name%' => $package->getProduct()->getName(),
                '%quantity%' => $package->getQuantity()
            ]
        ));
        
        return $this->redirectToRoute('app_cart');
    }
}
