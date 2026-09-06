<?php

namespace App\Controller\Admin\Documentation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Documentation\FileEntity;
use App\Repository\Documentation\FileEntityRepository;
use App\Form\Documentation\FileEntityType;
use Symfony\Component\HttpFoundation\Request;
use \DateTime;
use App\Service\Documentation\DocumentationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatableMessage;

#[Route('/admin')]
final class DocumentationController extends AbstractController
{
    public function __construct(private DocumentationManager $fileManager, private EntityManagerInterface $entityManager) {}

    #[Route('/documentation', name: 'app_admin_documentations')]
    public function index(FileEntityRepository $repository, Request $request): Response
    {
        $form = $this->createForm(FileEntityType::class, new FileEntity());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $file = $form->get('file')->getData();
            
            $entity = $this->fileManager->entity($data)->moveFile($file);
            
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $this->addFlash('success', new TranslatableMessage('"%format%" file uploaded.', ['%format%' => $entity->getName()]));
        }

        return $this->render('admin/documentation/index.html.twig', [
            'files' => $repository->findAllByTypes(),
            'form' => $form
        ]);
    }
}
