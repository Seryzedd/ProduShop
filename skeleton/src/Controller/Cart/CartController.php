<?php

namespace App\Controller\Cart;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\Cart\CartItemQuantityType;
use App\Service\Cart\CartService;
use App\DTO\Cart\CartItem;
use App\Entity\Product\Package;

#[Route('/cart')]
final class CartController extends AbstractController
{
    public function __construct(private CartService $cart) {}

    #[Route('/', name: 'app_cart')]
    public function index(Request $request): Response
    {
        $form = $this->createFormBuilder($this->cart)
            ->add('items', CollectionType::class, [
                'entry_type' => CartItemQuantityType::class,
                'label' => false
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $form->getData()->save();

            $this->addFlash('success', 'Cart updated.');
        }

        return $this->render('cart/cart/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/remove/{itemId}', name: 'app_cart_remove')]
    public function removeItem(string $itemId, Request $request): Response
    {
        try {
            $this->cart->remove($itemId);

            $this->addFlash('success', 'Product remove from cart.');
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        $referer = $request->headers->get('referer');

        return $this->redirect($referer ?? $this->generateUrl('app_home'));
    }

    #[Route('/add/{id}', name: 'app_cart_add')]
    public function add(Package $package, Request $request): Response
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
        
        $referer = $request->headers->get('referer');

        return $this->redirect($referer ?? $this->generateUrl('app_home'));
    }

    #[Route('/clear', name: 'app_cart_clear')]
    public function clear(): Response
    {
        $this->cart->clear();

        $this->addFlash('success', 'Products remove from cart.');

        $referer = $request->headers->get('referer');

        return $this->redirect($referer ?? $this->generateUrl('app_home'));
    }
}
