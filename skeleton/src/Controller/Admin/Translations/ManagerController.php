<?php

namespace App\Controller\Admin\Translations;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\Translation\TranslationFileReader;
use App\DTO\Translations\TranslationFileDTO;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Translation\PrototypeType;
use App\Service\CommandRunner;
use App\Service\Translation\Languages;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/translations')]
final class ManagerController extends AbstractController
{
    private Languages $languages;

    private CommandRunner $commandRunner;

    private TranslatorInterface $translator;

    public function __construct(Languages $languages, CommandRunner $commandRunner, TranslatorInterface $translator)
    {
        $this->languages = $languages;
        $this->commandRunner = $commandRunner;
        $this->translator = $translator;
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
    public function updateTranslations(string $locale): Response
    {
        $this->extractTranslations($locale);

        $this->addFlash('success', new TranslatableMessage('Translation "%locale%" extracted successfully.', ['%locale%' => $locale]));

        return $this->redirectToRoute('app_admin_translations_manager_edit', ['locale' => $locale]);
    }

    #[Route('/reload/{locale}', name: 'app_admin_translations_manager_reload')]
    public function reloadTranslations(string $locale, TranslationFileReader $translationFileReader)
    {
        $this->extractTranslations($locale);

        return $this->redirectToRoute('app_admin_translations_manager_edit', ['locale' => $locale]);
    }

    #[Route('/edit/file/{filename}/language/{locale}', name: 'app_admin_translations_file_manager_edit')]
    #[Route('/edit/{locale}', name: 'app_admin_translations_manager_edit')]
    public function editLanguage(string $locale, TranslationFileReader $translationFileReader, Request $request, ?string $filename = null)
    {
        
        if ($filename) {
            $languages = $translationFileReader->getFileByFilename($filename);
        } else {
            $languages = $translationFileReader->getFilesByLocale($locale);
        }

        $prototypeForm = $this->createForm(PrototypeType::class);

        $activeFileName = null;
        
        if ($request->getMethod() === "POST") {
            $data = $request->request->all('translations');
            // $data = ['messages.fr.yml' => ['key' => 'value', ...], ...]

            dump($data);
            
            $validated = false;
            foreach ($data as $file => $entries) {
                try {
                    $translations = [];
                    
                    foreach ($entries as $key => $value) {
                        if (!is_array($value)) {
                            // Old flat structure still coming from the form
                            $translations[$key] = $value;
                            continue;
                        }
                        
                        // Structure : ['translationKey' => '...', 'translationValue' => '...']
                        $translationKey   = trim($value['translationKey'] ?? '');
                        $translationValue = $value['translationValue'] ?? '';

                        if ($translationKey !== '') {
                            $translations[$translationKey] = $translationValue;
                        }
                    }

                    $translationFileReader->updateFile($file, $translations);

                    $activeFileName = $file;

                    $validated = true;
                    
                } catch (\Exception $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }

            if($validated) {
                $this->addFlash('success', new TranslatableMessage(
                    'Translations "%locale%" files updated successfully.',
                    ['%locale%' => $locale]
                ));

                if ($filename) {
                    $languages = $translationFileReader->getFileByFilename($filename);
                } else {
                    $languages = $translationFileReader->getFilesByLocale($locale);
                }
            }
        }

        return $this->render('admin/translations/manager/edit.html.twig', [
            'languages' => $languages,
            'locale' => $locale,
            'filename' => $filename,
            'prototypeForm' => $prototypeForm,
            'activeFile' => $activeFileName
        ]);
    }

    private function extractTranslations(string $locale)
    {
        try {
            $this->commandRunner->run('app:translation:update', ['locale' => $locale]);

            $this->addFlash(
                'info',
                new TranslatableMessage(
                    'Translations updating in %locale% done.',
                    ['%locale%' => $locale])
                )
            ;
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }
        
    }

}
