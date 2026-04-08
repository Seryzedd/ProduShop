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
use App\Entity\Configuration\AbstractText;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\Translations\TextTranslationType;

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

    #[Route('/{text}/translate', name: 'app_admin_configuration_homepage_translate')]
    public function translateText(AbstractText $text, Request $request): Response
    {
        
        $form = $this->createForm(CollectionType::class, $text->getTranslations(), [
            'entry_type' => TextTranslationType::class,
            'label' => 'Translations',
            'allow_add' => true,
            'allow_delete' => true
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            
            foreach($form->getData() as $element) {
                
                $element->setTranslatable($text);

                $this->entityManager->persist($element);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Test Translations updated.');
        }

        return $this->render('admin/configuration/homepage/translate.html.twig', [
            'form' => $form,
            'textElement' => $text
        ]);
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
