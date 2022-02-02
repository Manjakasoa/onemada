<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Utils\Validator;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class AddUserCommand extends Command
{
    protected static $defaultName = 'app:add-user';
    protected static $defaultDescription = 'Creates users and stores them in the database';

    private $validator;
    private $passwordHasher;
    private $entityManager;

    public function __construct(Validator $validator,UserPasswordHasherInterface $passwordHasher,EntityManagerInterface $entityManager) {
        parent::__construct();
        $this->validator = $validator;
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The username of the user.')
            ->addArgument('password', InputArgument::REQUIRED, 'Argument description')
            ->addArgument('firstname', InputArgument::REQUIRED, 'Argument description')
            ->addArgument('lastname', InputArgument::REQUIRED, 'Argument description')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null !== $input->getArgument('email')) {
            return;
        }
        $io = new SymfonyStyle($input, $output);
        $io->title('Add User Command Interactive Wizard');
        $io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:add-user email@example.com password firstname lastname',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
        ]);

        
        $email = $input->getArgument('email');
        if (null !== $email) {
            $io->text(' > <info>Email</info>: '.$email);
        } else {
            $email = $io->ask('Email', null, [$this->validator, 'validateEmail']);
            $input->setArgument('email', $email);
        }
        $password = $input->getArgument('password');
        if (null !== $password) {
            $io->text(' > <info>Password</info>: '.$password);
        } else {
            $password = $io->askHidden('Password', null, [$this->validator, 'validatePassword']);
            $input->setArgument('password', $password);
        }
        $firstname = $input->getArgument('firstname');
        if (null !== $firstname) {
            $io->text(' > <info>Firstname</info>: '.$firstname);
        } else {
            $firstname = $io->ask('Firstname', null, [$this->validator, 'validateUsername']);
            $input->setArgument('firstname', $firstname);
        }
        $lastname = $input->getArgument('lastname');
        if (null !== $lastname) {
            $io->text(' > <info>Lastname</info>: '.$lastname);
        } else {
            $lastname = $io->ask('Lastname', null, [$this->validator, 'validateUsername']);
            $input->setArgument('lastname', $lastname);
        }
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $plainPasword = $input->getArgument('password');
        $firstname = $input->getArgument('firstname');
        $lastname = $input->getArgument('lastname');
        $fullName = $firstname.' '.$lastname;
        $user = new User();
        $password = $this->passwordHasher->hashPassword($user,$plainPasword);
        $user->setEmail($email)
             ->setPassword($password)
             ->setFirstname($firstname)
             ->setLastname($lastname)
             ->setDateAdd(new \DateTime('now'))
             ->setRoles(['ROLE_ADMIN']);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $io->success(sprintf('%s was successfully created: %s (%s)', 'Administrator user', $fullName, $email));

        return Command::SUCCESS;
    }
}
