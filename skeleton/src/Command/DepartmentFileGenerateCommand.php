<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Service\Department\FileGenerator;
use Symfony\Component\Console\Helper\Table;

#[AsCommand(
    name: 'app:department-file:generate',
    description: 'Generate department',
)]
class DepartmentFileGenerateCommand extends Command
{
    public function __construct(private FileGenerator $fileGenerator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            //->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force file generation, even if file already exist and overwhite if exist')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force') ? true : false;

        $generate = $this->fileGenerator->generate($force);

        if(empty($generate)) {
            $io->success('No file generated.');
        } else {
            $generated = [];
            
            foreach($generate as $language => $file) {
                $generated[] = [$language, $file];
            }

            $table = new Table($output);
            $table
                ->setHeaders(['Language', 'File'])
                ->setRows($generated)
            ;

            $table->render();

            $io->success('Department file generation completed.');
        }

        return Command::SUCCESS;
    }
}
