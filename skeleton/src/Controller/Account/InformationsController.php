<?php

namespace App\Controller\Account;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\User\AdressType;
use App\Entity\User\PostalAdress\Adress;
use App\Form\User\ProfessionalType;

#[Route('/account')]
final class InformationsController extends AbstractController
{
    #[Route('/', name: 'app_account_informations')]
    public function index(): Response
    {
        return $this->render('account/informations/index.html.twig', []);
    }

    #[Route('/update', name: 'app_account_update')]
    public function UpdateInformations(Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(ProfessionalType::class, $this->getUser());

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            
            $entityManager->persist($this->getUser());
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
}
