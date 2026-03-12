<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use App\Service\ImgTransformerService;

class ImageConverterRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private ImgTransformerService $imgTransformer,
        private string $projectDir,
    ) {}

    public function toBase64(string $relativePath): string
    {
        $absolutePath = $this->projectDir . '/public' . $relativePath;

        return $this->imgTransformer->fileToBase64($absolutePath);
    }
}
