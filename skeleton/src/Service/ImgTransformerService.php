<?php

namespace App\Service;

class ImgTransformerService
{
    public function fileToBase64(string $imgPath): string
    {
        // Read image path, convert to base64 encoding
        $imageData = base64_encode(file_get_contents($imgPath));

        // Format the image SRC: data:{mime};base64,{data};
        $src = 'data: '.mime_content_type($imgPath).';base64,'.$imageData;

        return $src;
    }
}