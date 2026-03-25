<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use Twig\Environment;
use App\Entity\Configuration\Homepage\Block;

class BlockRendererRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private Environment $twig
    ) {}

    public function renderBlock(Block $block)
    {
        return $this->twig->render('components/block_view.html.twig', [
            'block' => $block,
        ]);
    }
}
