<?php

namespace App\Controller\Admin\Configuration;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\SortableType;
use App\Form\PositionType;
use App\Repository\Configuration\Homepage\BlockRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Configuration\Homepage\Block;
use App\Form\Configuration\BlockType;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin/configuration/homepage')]
final class HomepageController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route('/', name: 'app_admin_configuration_homepage')]
    public function index(BlockRepository $blockRepository, Request $request): Response
    {
        $blocks = $blockRepository->findByOrdered();
        
        $form = $this->createForm(SortableType::class, ['items' => $blocks]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            foreach($blocks as $block) {
                $this->entityManager->persist($block);
            }

            $this->entityManager->flush();

            $blocks = $blockRepository->findByOrdered();

            $form = $this->createForm(SortableType::class, ['items' => $blocks]);

            $this->addFlash('success', 'Blocks positions updated.');
        }

        return $this->render('admin/configuration/homepage/index.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{block}/update', name: 'app_admin_configuration_homepage_update')]
    public function update(Block $block, Request $request): Response
    {
        return $this->getView($block, $request);
    }

    #[Route('/new', name: 'app_admin_configuration_homepage_new')]
    public function createBlock(Request $request): Response
    {
        $block = new Block();

        return $this->getView($block, $request);
    }

    private function getView(Block $block, Request $request): Response
    {
        $form = $this->createForm(BlockType::class, $block);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            
            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();

            $this->addFlash('success', 'Block saved.');
        }

        return $this->render('admin/configuration/homepage/create.html.twig', [
            'form' => $form
        ]);
    }
}
