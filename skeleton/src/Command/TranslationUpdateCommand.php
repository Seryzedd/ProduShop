<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Extractor\ChainExtractor;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Writer\TranslationWriter;
use Symfony\Component\Translation\Dumper\YamlFileDumper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\Reader\TranslationReaderInterface;
use App\Service\Translation\TranslationFileReader;

#[AsCommand(
    name: 'app:translation:update',
    description: 'Retrieve translations and update translations files',
)]
class TranslationUpdateCommand extends Command
{
    public function __construct(
        #[Autowire('%translator.default_path%')]
        private string $translationPath,
        private readonly KernelInterface $kernel,
        private readonly ExtractorInterface $extractor,
        private readonly TranslationReaderInterface $reader,
        private readonly TranslationFileReader $translationFileReader
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('locale', InputArgument::REQUIRED, 'Language key to update')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $locale = $input->getArgument('locale');
        $projectDir = $this->kernel->getProjectDir();

        /* =============================
        * 1. Extract keys from the app
        * ============================= */

        $extractedCatalogue = new MessageCatalogue($locale);
        $this->extractor->extract($projectDir . '/src', $extractedCatalogue);
        $this->extractor->extract($projectDir . '/templates', $extractedCatalogue);

        /* =============================
        * 2. Extract keys from each registered bundle
        * ============================= */

        foreach ($this->kernel->getBundles() as $bundle) {
            $bundlePath = $bundle->getPath();

            // Extract from PHP and Twig source files within the bundle
            foreach (['/src', '/templates'] as $subDir) {
                $dir = $bundlePath . $subDir;
                if (is_dir($dir)) {
                    $this->extractor->extract($dir, $extractedCatalogue);
                }
            }

            // Merge any existing translations shipped with the bundle
            $bundleTranslationPath = $bundlePath . '/translations';
            if (is_dir($bundleTranslationPath)) {
                $bundleCatalogue = new MessageCatalogue($locale);
                $this->reader->read($bundleTranslationPath, $bundleCatalogue);

                // Fall back to base language if no translations found for the full locale
                if (count($bundleCatalogue->all()) === 0) {
                    $baseLocale = explode('_', $locale)[0];
                    if ($baseLocale !== $locale) {
                        $fallbackCatalogue = new MessageCatalogue($baseLocale);
                        $this->reader->read($bundleTranslationPath, $fallbackCatalogue);

                        foreach ($fallbackCatalogue->getDomains() as $domain) {
                            foreach ($fallbackCatalogue->all($domain) as $id => $message) {
                                $bundleCatalogue->set($id, $message, $domain);
                            }
                        }
                    }
                }

                $extractedCatalogue->addCatalogue($bundleCatalogue);
            }
        }

        /* =============================
        * 3. Extract keys from Symfony components
        * ============================= */

        $componentsBasePath = $projectDir . '/vendor/symfony';
        if (is_dir($componentsBasePath)) {
            foreach (new \DirectoryIterator($componentsBasePath) as $componentDir) {
                if ($componentDir->isDot() || !$componentDir->isDir()) {
                    continue;
                }

                $componentTranslationPath = $componentDir->getPathname() . '/Resources/translations';
                if (is_dir($componentTranslationPath)) {
                    $componentCatalogue = new MessageCatalogue($locale);
                    $this->reader->read($componentTranslationPath, $componentCatalogue);

                    // Fall back to base language if no translations found for the full locale
                    if (count($componentCatalogue->all()) === 0) {
                        $baseLocale = explode('_', $locale)[0];
                        if ($baseLocale !== $locale) {
                            $fallbackCatalogue = new MessageCatalogue($baseLocale);
                            $this->reader->read($componentTranslationPath, $fallbackCatalogue);

                            foreach ($fallbackCatalogue->getDomains() as $domain) {
                                foreach ($fallbackCatalogue->all($domain) as $id => $message) {
                                    $componentCatalogue->set($id, $message, $domain);
                                }
                            }
                        }
                    }

                    $extractedCatalogue->addCatalogue($componentCatalogue);
                }
            }
        }

        /* =============================
        * 4. Load the existing app catalogue
        * ============================= */

        $existingCatalogue = new MessageCatalogue($locale);
        $appCatalogue = new MessageCatalogue($locale);
        $this->reader->read($this->translationPath, $appCatalogue);
        $existingCatalogue->addCatalogue($appCatalogue);
        
        // DEBUG
        dump($existingCatalogue->all());
        /* =============================
        * 5. Diff and merge keys per domain
        * ============================= */

        $domains = array_unique(array_merge(
            $extractedCatalogue->getDomains(),
            $existingCatalogue->getDomains()
        ));

        foreach ($domains as $domain) {
            $output->writeln("\n<comment>Domain: $domain</comment>");

            $newMessages = $extractedCatalogue->all($domain);
            $oldMessages = $existingCatalogue->all($domain);

            // Add new keys — use the key itself as the default value if no translation is available
            foreach ($newMessages as $id => $message) {
                if (!isset($oldMessages[$id])) {
                    $output->writeln("<info>+ $id</info>");
                    $existingCatalogue->set($id, $message ?: $id, $domain);
                }
            }

            // Report unused keys — displayed only, not removed
            foreach ($oldMessages as $id => $message) {
                if (!isset($newMessages[$id])) {
                    $output->writeln("<error>- $id (unused)</error>");
                }
            }

            // Ensure a file is created even if the domain has no keys yet
            if (empty($newMessages) && empty($oldMessages)) {
                $existingCatalogue->set('__placeholder__', '', $domain);
            }
        }

        /* =============================
        * 6. Write one file per domain
        * ============================= */

        // show messages ------------
        foreach ($domains as $domain) {
            $filename = $domain . '.' . $locale . '.yml';
            $messages = $existingCatalogue->all($domain);

            // Supprimer le placeholder technique
            unset($messages['__placeholder__']);

            if (empty($messages)) {
                continue;
            }

            $this->translationFileReader->updateFile($filename, $messages);
        }

        $io->success('Sync completed for locale: ' . $locale);

        return Command::SUCCESS;
    }
}
