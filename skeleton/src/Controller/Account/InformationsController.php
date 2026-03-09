<?php

namespace App\Controller\Account;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Api\StripeService;
use App\Entity\User\Order;
use App\Form\User\AdressType;
use App\Entity\User\PostalAdress\Adress;
use App\Form\User\ProfessionalType;
use App\Form\User\ClientType;
use App\Entity\User\Client;
use App\Entity\User\Professional;

#[Route('/account')]
final class InformationsController extends AbstractController
{
    public function __construct(private StripeService $stripeService) {}
    #[Route('/', name: 'app_account_informations')]
    public function index(): Response
    {
        $user = $this->getUser();
        $paymentMethods = [];
        if ($user instanceOf Client) {
            $stripeCustomerId = $this->getUser()->getStripe()->getCustomerId();

            $paymentMethods = $this->stripeService->getPaymentMethods($stripeCustomerId);
        }

        return $this->render('account/informations/index.html.twig', [
            'paymentMethods' => $paymentMethods
        ]);
    }

    #[Route('/update', name: 'app_account_update')]
    public function UpdateInformations(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        $proClass = Professional::class;
        if($user instanceof $proClass){
            $form = $this->createForm(ProfessionalType::class, $user);
        } else {
            $form = $this->createForm(ClientType::class, $user);
        }

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Your account is updated.');

            return $this->redirectToRoute('app_account_informations');
        }

        return $this->render('account/informations/update.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/adress', name: 'app_account_adress')]
    public function updateAdress(EntityManagerInterface $entityManager, Request $request): Response
    {
        $adress = new Adress();
        if($this->getUser()->getAdress()) {
            $adress = $this->getUser()->getAdress();
        }

        $form = $this->createForm(AdressType::class, $adress);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adress = $form->getData();

            $entityManager->persist($adress);
            $entityManager->flush();

            $this->addFlash('info', 'Postal adress updated.');
        }
        return $this->render('account/informations/adress.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/order/{id}', name: 'app_account_order')]
    public function accountOrder(Order $order): Response
    {
        
        return $this->render('account/informations/order/index.html.twig', [
            'order' => $order
        ]);
    }
}
