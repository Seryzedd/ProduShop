<?php

namespace App\Controller\Merchant;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product\Slider;
use App\Repository\Product\SliderRepository;
use App\Form\Product\SliderType;

#[Route('/merchant/slider')]
final class SliderController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private SliderRepository $sliderRepository)
    {
    }

    #[Route('/', name: 'app_merchant_slider')]
    public function index(): Response
    {
        $sliders = $this->sliderRepository->findAll();

        return $this->render('merchant/slider/index.html.twig', [
            'sliders' => $sliders,
        ]);
    }

    #[Route('/create', name: 'app_merchant_slider_create')]
    public function create(Request $request): Response
    {
        $form = $this->createForm(SliderType::class, new Slider());

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $slider = $form->getData();

            $entityManager = $this->entityManager;
            $entityManager->persist($slider);
            $entityManager->flush();

            $this->addFlash('success', new TranslatableMessage('Slider "%name%" created successfully.', ['%name%' => $slider->getName()]));

            return $this->redirectToRoute('app_merchant_slider_edit', ['slider' => $slider->getId()]);
        }
        
        return $this->render('merchant/slider/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/edit/{slider}', name: 'app_merchant_slider_edit')]
    public function edit(Slider $slider, Request $request): Response
    {
        $form = $this->createForm(SliderType::class, $slider);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $slider = $form->getData();

            $entityManager = $this->entityManager;
            $entityManager->persist($slider);
            $entityManager->flush();

            $this->addFlash('success', new TranslatableMessage('Slider "%name%" updated successfully.', ['%name%' => $slider->getName()]));

        }

        return $this->render('merchant/slider/create.html.twig', [
            'slider' => $slider,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{slider}', name: 'app_merchant_slider_delete')]
    public function delete(Slider $slider, Request $request): Response
    {
        $entityManager = $this->entityManager;
            $entityManager->remove($slider);
            $entityManager->flush();

            $this->addFlash('success', new TranslatableMessage('Slider "%name%" deleted successfully.', ['%name%' => $slider->getName()]));

        return $this->redirectToRoute('app_merchant_slider');
    }
}
