<?php

namespace App\Controller\Admin\Product;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Product\ShelfRepository;
use App\Entity\Product\Shelf;
use App\Form\Product\ShelfType;

#[Route('/admin/product/shelf')]
final class ShelfController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route('/', name: 'app_admin_product_shelf')]
    public function index(ShelfRepository $repository, Request $request): Response
    {
        $form = $this->getForm(new Shelf(), $request);

        return $this->render('admin/product/shelf/index.html.twig', [
            'shelves' => $repository->findAll(),
            'form' => $form
        ]);
    }

    #[Route('/edit/{shelf}', name: 'app_admin_product_shelf_edit')]
    public function editShelf(Shelf $shelf, Request $request): Response
    {
        $form = $this->getForm($shelf, $request);

        return $this->render('admin/product/shelf/edit.html.twig', [
            'shelf' => $shelf,
            'form' => $form
        ]);
    }

    private function getForm(Shelf $shelf, Request $request)
    {
        $form = $this->createForm(ShelfType::class, $shelf);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $shelf = $form->getData();

            $this->entityManager->persist($shelf);
            $this->entityManager->flush();

            $this->addFlash('success', new TranslatableMessage('Shelf "%name%" saved.', ['%name%' => $shelf->getName()]));
        }

        return $form;
    }
}
