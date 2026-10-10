<?php

declare(strict_types=1);

namespace App\Security\Command;

use App\Security\Entity\User;
use App\Security\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:create-superadmin',
    description: 'Create (or promote) the superadmin account. Prompts for a password unless --password is given.',
)]
final class CreateSuperadminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'E-mail address of the superadmin')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Password (prompted when omitted)')
            ->addOption('locale', null, InputOption::VALUE_REQUIRED, 'UI locale (nl|en)', 'nl');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = (string) $input->getArgument('email');
        $password = $input->getOption('password');

        if (!\is_string($password) || '' === $password) {
            $password = $io->askHidden('Password for '.$email);
            if (!\is_string($password) || \strlen($password) < 12) {
                $io->error('Password must be at least 12 characters.');

                return Command::FAILURE;
            }
        }

        $user = $this->users->findOneByEmail($email);
        $created = false;
        if (null === $user) {
            $user = (new User())->setEmail($email);
            $this->em->persist($user);
            $created = true;
        }

        $user
            ->addRole(User::ROLE_SUPER_ADMIN)
            ->setLocale((string) $input->getOption('locale'))
            ->setIsActive(true)
            ->setPassword($this->hasher->hashPassword($user, $password));

        $this->em->flush();

        $io->success(sprintf('%s superadmin %s', $created ? 'Created' : 'Updated', $user->getEmail()));

        return Command::SUCCESS;
    }
}
