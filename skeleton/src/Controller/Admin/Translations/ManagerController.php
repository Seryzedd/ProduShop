<?php

namespace App\Controller\Admin\Translations;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\Translation\TranslationFileReader;
use App\DTO\Translations\TranslationFileDTO;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Translation\AllTranslationsType;
use App\Service\CommandRunner;
use App\Service\Translation\Languages;

#[Route('/admin/translations')]
final class ManagerController extends AbstractController
{
    public function __construct(Languages $languages)
    {
        $this->languages = $languages;
    }
    
    #[Route('/', name: 'app_admin_translations_manager')]
    public function index(TranslationFileReader $translationFileReader): Response
    {
        return $this->render('admin/translations/manager/index.html.twig', [
            'translationFiles' => $translationFileReader->readAllTranslationFiles(),
            'languages' => $this->languages->getLanguagesKeys()
        ]);
    }

    #[Route('/extract/{locale}', name: 'app_admin_translations_manager_extract')]
    public function updateTranslations(CommandRunner $commandRunner, string $locale): Response
    {
        $commandRunner->run('translation:extract', ['--force' => true, 'locale' => $locale, '--format' => 'yml']);

        $this->addFlash('success', new TranslatableMessage('Translation "%locale%" extracted successfully.', ['%locale%' => $locale]));

        return $this->redirectToRoute('app_admin_translations_manager');
    }

    #[Route('/edit/{locale}', name: 'app_admin_translations_manager_edit')]
    public function editLanguage(string $locale, TranslationFileReader $translationFileReader, Request $request)
    {
        $languages = $translationFileReader->getFilesByLocale($locale);
        
        $form = $this->createForm(AllTranslationsType::class, $languages);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            
        }

        return $this->render('admin/translations/manager/edit.html.twig', [
            'languages' => $languages,
            'locale' => $locale,
            'form' => $form
        ]);
    }
}
