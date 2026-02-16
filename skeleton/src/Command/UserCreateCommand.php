<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Validator\EmailFormat;
use App\Service\PasswordGenerator;
use App\Entity\User\Client;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create new User with specified email and role',
)]
class UserCreateCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PasswordGenerator $passwordGenerator,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User\'s email registerd in the database')
            ->addOption('role', '--r', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Role of the user to create', ['ROLE_USER'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        if ($email) {
            $io->note(sprintf('You passed an argument: %s', $email));
        } else {
            $io->error('Email is required to create a user.');
            return Command::FAILURE;
        }

        $user = new Client();
        $user->setEmail($email);

        $role = $input->getOption('role');

        $password = $this->passwordGenerator->generateStrong();
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        
        $violations = $this->validator->validate($email, [
            new EmailFormat(mode: 'strict'),
        ]);

        if (count($violations) > 0) {
            $io->error('Email invalide : ' . $email);

            foreach ($violations as $violation) {
                $io->text('  - ' . $violation->getMessage());
            }
            return Command::FAILURE;
        }

        if($role) {
            $user->setRoles($role);
        }

        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $io->error('An error occurred while creating the user: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $table = new Table($output);
        $table
            ->setHeaders(['id', 'firstname', 'lastname', 'email', 'roles', 'clear password'])
            ->setRows([
                [$user->getId(), $user->getFirstname(), $user->getLastname(), $user->getEmail(), implode(', ', $user->getRoles()), $password],
            ])
        ;

        $table->render();

        $io->success('User created successfully!');

        return Command::SUCCESS;
    }
}
