<?php

namespace App\Command;

use App\Entity\AirmaxCredential;
use App\Repository\AirmaxCredentialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-airmax-credentials',
    description: 'Update username and password for airmax channels',
)]
class UpdateAirmaxCredentialsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AirmaxCredentialRepository $airmaxCredentialsRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
        ->addArgument('username', InputArgument::REQUIRED, 'New username')
        ->addArgument('password', InputArgument::REQUIRED, 'New password')
        ->addArgument('domain', InputArgument::OPTIONAL, 'New domain')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $newUsername = $input->getArgument('username');
        $newPassword = $input->getArgument('password');
        $newDomain = $input->getArgument('domain');

        $credentials = $this->airmaxCredentialsRepository->findOneBy(['username' => $newUsername]);

        if (!$credentials) {
            $credentials = new AirmaxCredential();
        }

        $credentials->setUsername($newUsername);
        $credentials->setPassword($newPassword);
        $credentials->setDomain($newDomain);

        $this->entityManager->persist($credentials);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
