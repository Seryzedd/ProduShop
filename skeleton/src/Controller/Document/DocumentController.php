<?php

namespace App\Controller\Document;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Documentation\FileEntity;
use App\Repository\Documentation\FileEntityRepository;

#[Route('/document')]
final class DocumentController extends AbstractController
{
    #[Route('/{entity}', name: 'app_document')]
    public function index(FileEntity $entity): Response
    {
        return $this->render('document/index.html.twig', [
            'document' => $entity
        ]);
    }
}
