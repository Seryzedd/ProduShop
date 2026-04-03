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
use App\Entity\User\Payment\Payment;
use App\Entity\User\Professional;
use App\Repository\User\OrderRepository;
use App\Entity\Translations\ProfessionalTranslation;
use Symfony\Component\Translation\TranslatableMessage;

#[Route('/account')]
final class InformationsController extends AbstractController
{
    public function __construct(private StripeService $stripeService, private EntityManagerInterface $entityManager) {}
    
    #[Route('/', name: 'app_account_informations')]
    public function index(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $paymentMethods = [];
        if ($user instanceOf Client && $this->getUser()->getStripe()) {
            $stripeCustomerId = $this->getUser()->getStripe()->getCustomerId();

            $paymentMethods = $this->stripeService->getPaymentMethods($stripeCustomerId);
        }

        return $this->render('account/informations/index.html.twig', [
            'paymentMethods' => $paymentMethods
        ]);
    }

    #[Route('/update', name: 'app_account_update')]
    public function UpdateInformations(Request $request)
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
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Your account is updated.');

            return $this->redirectToRoute('app_account_informations');
        }

        return $this->render('account/informations/update.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/professional/translation/add/{langage}', name: 'app_account_translation_add')]
    public function addProfessionalTranslation(string $langage): Response
    {
        $user = $this->getUser();

        if($user instanceof Professional) {
            $translation = new ProfessionalTranslation();
            $translation->setLocale($langage);

            $user->addTranslation($translation);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                new TranslatableMessage(
                    'Translation %language% added in your account.',
                        ['%language%' => $langage]
                    )
                )
            ;
        } else {
            $this->addFlash(
                'danger',
                'Invalid account type.'
            );
        }
        

        return $this->redirectToRoute('app_account_update');
    }

    #[Route('/adress/update/{adress}', name: 'app_account_adress')]
    public function updateAdress(EntityManagerInterface $entityManager, Request $request, Adress $adress): Response
    {
        return $this->adressForm($adress, $request);
    }

    #[Route('/adress/new', name: 'app_account_new_adress')]
    public function addAdress(Request $request): Response
    {
        $adress = new Adress();

        $adress->setUser($this->getUser());

        return $this->adressForm($adress, $request);
    }

    #[Route('/adress/delete/{adress}', name: 'app_account_remove_adress')]
    public function removeAdress(Adress $adress): Response
    {
        $this->entityManager->remove($adress);
        $this->entityManager->flush();

        $this->addFlash('success', 'Postal adress deleted from account');
        
        return $this->redirectToRoute('app_account_informations');
    }

    private function adressForm(Adress $adress, Request $request): Response
    {
        $form = $this->createForm(AdressType::class, $adress);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adress = $form->getData();

            $this->entityManager->persist($adress);
            $this->entityManager->flush();

            $this->addFlash('info', 'Postal adress updated.');
        }
        return $this->render('account/informations/adress.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/payment/{payment}', name: 'app_account_payment')]
    public function accountPayment(Payment $payment): Response
    {
        return $this->render('account/informations/Payment/index.html.twig', [
            'payment' => $payment
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
