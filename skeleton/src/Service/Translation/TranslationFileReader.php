<?php

namespace App\Service\Translation;

use App\DTO\Translations\TranslationDTO;
use App\DTO\Translations\TranslationsDTO;
use App\DTO\Translations\TranslationFileDTO;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Translation\DataCollectorTranslator;

class TranslationFileReader
{
    public function __construct(
        #[Autowire('%translator.default_path%')]
        private string $translationPath,
        private ?DataCollectorTranslator $dataCollectorTranslator = null
    )
    {
        
    }

    public function readTranslationFile(string $fileName): array
    {
        $completeFileName = $this->translationPath . '/' . $fileName;

        if (!file_exists($completeFileName)) {
            throw new \InvalidArgumentException("File not found: " . $completeFileName);
        }

        $translations = Yaml::parse(file_get_contents($completeFileName));

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

    public static function getLanguagesByFiles(): array
    {
        $dir = '/var/www/html/translations/';
        if(!is_dir($dir)) {
            return null;
        }

        $files = scandir($dir);

        $pattern = '/^.+\.([a-z]{2}(?:_[A-Z]{2})?)\.ya?ml$/';

        $response = [];
        foreach ($files as $file) {

            if (preg_match($pattern, $file, $matches)) {
                $response[$matches[1]] = $matches[1];
            }
        }

        return $response;
    }

    public function updateFile(string $filename, array $content)
    {
        $path = $this->translationPath . '/' . $filename;
        
        if(!file_exists($path)) {
            touch($path);
        }

        $yaml = Yaml::dump(
            $content,
            indent: 4,
            inline: 4,
            flags: Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK | Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE
        );

        $valid = file_put_contents($path, $yaml);

        return $valid;
    }

    public function removeFilesByLocale(string $locale)
    {
        $this->getFilesByLocale($locale);
    }
}