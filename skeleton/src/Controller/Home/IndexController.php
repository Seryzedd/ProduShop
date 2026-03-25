<?php

namespace App\Controller\Home;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\Configuration\Homepage\BlockRepository;

final class IndexController extends AbstractController
{
    #[Route('/', name: 'app_home_index')]
    public function index(BlockRepository $blockRepository): Response
    {
        $blocks = $blockRepository->findByOrdered(['active' => true]);

        return $this->render('home/index/index.html.twig', [
            'blocks' => $blocks
        ]);
    }
}
