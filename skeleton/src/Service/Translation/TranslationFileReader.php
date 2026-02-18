<?php

namespace App\Service\Translation;

use App\DTO\Translations\TranslationDTO;
use App\DTO\Translations\TranslationsDTO;
use App\DTO\Translations\TranslationFileDTO;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class TranslationFileReader
{
    public function __construct(
        #[Autowire('%translator.default_path%')]
        private string $translationPath)
    {
        
    }

    public function readTranslationFile(string $fileName): array
    {
        $completeFileName = $this->translationPath . '/' . $fileName;

        if (!file_exists($completeFileName)) {
            throw new \InvalidArgumentException("File not found: " . $completeFileName);
        }

        $translations = Yaml::parse(file_get_contents($completeFileName), true);

        return $translations;
    }

    public function getFilesByLocale(string $locale): TranslationsDTO
    {
        $translationFiles = glob($this->translationPath . '/*.' . $locale . '.yml');
        $filesByLocale = [];

        foreach ($translationFiles as $file) {
            $filesByLocale[] = $this->readTranslationFileAsDTO(basename($file));
        }

        $dto = new TranslationsDTO($filesByLocale);

        return $dto;
    }

    public function getFileByFilename(string $filename): TranslationsDTO
    {
        $translationFiles = glob($this->translationPath . '/' . $filename);
        $filesByLocale = [];

        foreach ($translationFiles as $file) {
            $filesByLocale[] = $this->readTranslationFileAsDTO(basename($file));
        }

        $dto = new TranslationsDTO($filesByLocale);

        return $dto;
    }

    public function getFullPathFile(string $filename): string
    {
        return $this->translationPath . '/' . $filename;
    }

    public function readTranslationFileAsDTO(string $fileName): TranslationFileDTO
    {
        $translationFileDTO = new TranslationFileDTO($fileName);
        $translations = $this->readTranslationFile($fileName);

        foreach ($translations as $key => $value) {
            $translationDTO = new TranslationDTO($key, $value);
            $translationFileDTO->addTranslation($translationDTO);
        }

        return $translationFileDTO;
    }

    public function readAllTranslationFiles(): array
    {
        $translationFiles = glob($this->translationPath . '/*.yml');
        $allTranslations = [];

        foreach ($translationFiles as $key => $value) {
            $translationFileDTO = $this->readTranslationFileAsDTO(basename($value));
            $allTranslations[$translationFileDTO->getLocale()][] = $translationFileDTO;
        }

        return $allTranslations;
    }

    public function updateFile(string $filename, array $content)
    {
        $path = $this->translationPath . '/' . $filename;
        if(!file_exists($path)) {
            throw new \Exception("Error file does not exist.");
            
        }

        $yaml = Yaml::dump($content);

        $valid = file_put_contents($path, $yaml);

        return $valid;
    }

    public function removeFilesByLocale(string $locale)
    {
        $this->getFilesByLocale($locale);
    }
}