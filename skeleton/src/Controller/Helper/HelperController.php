<?php

namespace App\Controller\Helper;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Helper\Documentation;

final class HelperController extends AbstractController
{
    #[Route('/helper/{documentation}', name: 'app_helper')]
    public function index(Documentation $documentation): Response
    {
        return $this->render('helper/index.html.twig', [
            'documentation' => $documentation,
        ]);
    }
}
