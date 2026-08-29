<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use Twig\Environment;

class GenerateTemplateViewRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private Environment $twig
    ) {}

    public function render(string $view, mixed $data = [])
    {
        return $this->twig->render($view, $data);
    }
}
