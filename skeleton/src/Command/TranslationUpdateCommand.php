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
use Symfony\Component\Translation\Writer\TranslationWriterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'app:translation:update',
    description: 'Add a short description for your command',
)]
class TranslationUpdateCommand extends Command
{
    public function __construct(
        #[Autowire('%translator.default_path%')]
        private string $translationPath,
        private readonly KernelInterface $kernel,
        private readonly ExtractorInterface $extractor,
        private readonly TranslationReaderInterface $reader,
        private readonly TranslationWriterInterface $writer,
        private readonly TranslatorInterface $translator
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

        $extractedCatalogue = new MessageCatalogue($locale);

        $this->extractor->extract($projectDir.'/src', $extractedCatalogue);
        $this->extractor->extract($projectDir.'/templates', $extractedCatalogue);

        $bundleCatalogue = $this->translator->getCatalogue($locale);

        /* =============================
         * 2. Lecture catalogue existant
         * ============================= */

        $existingCatalogue = new MessageCatalogue($locale);
        $this->reader->read($this->translationPath, $existingCatalogue);

        $existingCatalogue->addCatalogue($bundleCatalogue);

        /* =============================
         * 3. Diff par domaine
         * ============================= */

        $domains = array_unique(array_merge(
            $extractedCatalogue->getDomains(),
            $existingCatalogue->getDomains()
        ));

        foreach ($domains as $domain) {

            $output->writeln("\n<comment>Domain: $domain</comment>");

            $newMessages = $extractedCatalogue->all($domain);
            $oldMessages = $existingCatalogue->all($domain);

            // Ajouts
            foreach ($newMessages as $id => $message) {
                if (!isset($oldMessages[$id])) {
                    $output->writeln("<info>+ $id</info>");
                    $existingCatalogue->set($id, $message, $domain);
                }
            }

            // Suppressions
            foreach ($oldMessages as $id => $message) {
                if (!isset($newMessages[$id])) {
                    $output->writeln("<error>- $id (unused)</error>");
                }
            }
        }

        /* =============================
         * 4. Écriture
         * ============================= */

        $this->writer->write($existingCatalogue, 'yml', [
            'path' => $this->translationPath,
        ]);

        $output->writeln("\n<info>Sync completed.</info>");

        return Command::SUCCESS;
    }
}
