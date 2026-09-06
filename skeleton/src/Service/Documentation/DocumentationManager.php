<?php

namespace App\Service\Documentation;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\Entity\Documentation\FileEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use \DateTime;

class DocumentationManager
{
    public function __construct(
        #[Autowire('%app.file_entity_directory%')]
        private string $fileEntityDirectory,
    ) {}

    public function entity(FileEntity $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getDirectory(DateTime $date): string
    {
        $monthDir = $this->fileEntityDirectory . $this->getDateDirectory($date);

        if(!is_dir($monthDir)) {
            mkdir($monthDir, 0777, true);
        }

        return $monthDir;
    }

    private function getDateDirectory(DateTime $date): string
    {
        return '/' . $date->format('Y'). '/' . $date->format('m');
    }

    public function moveFile(UploadedFile $file)
    {
        $date = new DateTime();
        $directory = $this->getDateDirectory($date);

        $completeDirectory = $this->getDirectory($date);

        $newFilename = $this->getFilename($date);

        $this->entity->setName($newFilename);
        $this->entity->setFolder($directory);

        $file->move($completeDirectory, $newFilename);

        return $this->entity;
    }

    private function getFilename(DateTime $date): string
    {
        return $this->entity->getType() . '-Lancana-' . $date->format('d-m-Y-u') . '.' . $this->entity->getFormat();
    }
}