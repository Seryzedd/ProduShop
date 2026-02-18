<?php

namespace App\Controller\Admin\Translations;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\Translation\TranslationFileReader;
use App\Service\Translation\Languages;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\TranslatableMessage;

#[Route('/admin/translations/files')]
final class FilesManagerController extends AbstractController
{
    public function __construct(
        private TranslationFileReader $reader,
        private Languages $languages,
        private Filesystem $fileSystem
        ) {}

    #[Route('/', name: 'app_admin_translations_files')]
    #[Route('/language/{locale}', name: 'app_admin_translations_files_locale')]
    public function index(?string $locale = null): Response
    {
        if ($locale) {
            $files = $this->reader->getFilesByLocale($locale);
        } else {
            $files = $this->reader->readAllTranslationFiles();
        }

        return $this->render('admin/translations/files_manager/index.html.twig', [
            'files' => $files,
            'languages' => $this->languages->getLanguagesKeys(),
            'locale' => $locale
        ]);
    }

    #[Route('/download/{filename}', name: 'app_admin_translations_file_download')]
    public function download(string $filename, TranslationFileReader $translationFileReader)
    {
        return $this->file($translationFileReader->getFullPathFile($filename));
    }

    #[Route('/delete/{filename}', name: 'app_admin_translations_file_delete')]
    public function delete(string $filename, TranslationFileReader $translationFileReader)
    {
        $this->fileSystem->remove($translationFileReader->getFullPathFile($filename));

        $this->addFlash('success', new TranslatableMessage('File "%filename%" deleted from system. Translations in this file are not supported anymore.', ['%filename%' => $filename]));

        return $this->redirectToRoute('app_admin_translations_files');
    }
}
