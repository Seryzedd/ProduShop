<?php

namespace App\Controller\Account;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/account')]
final class InformationsController extends AbstractController
{
    #[Route('/', name: 'app_account_informations')]
    public function index(): Response
    {
        return $this->render('account/informations/index.html.twig', []);
    }
}
