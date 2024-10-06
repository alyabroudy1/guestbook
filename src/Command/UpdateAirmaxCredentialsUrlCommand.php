<?php

namespace App\Command;

use App\Entity\AirmaxCredential;
use App\Entity\IptvChannel;
use App\Repository\AirmaxCredentialRepository;
use App\Repository\IptvChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOException;

#[AsCommand(
    name: 'app:update-airmax-credentials-url',
    description: 'Update username and password for airmax channels',
)]
class UpdateAirmaxCredentialsUrlCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private IptvChannelRepository $iptvChannelRepository)
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

        $channels = $this->iptvChannelRepository->findChannelsWithCredentialUrl();

        $newUrl = $newDomain . $newUsername . '/' . $newPassword . '/' ;
        foreach ($channels as $channel) {
            
            $channel->setUrl($newUrl);

            $this->entityManager->persist($channel);
        }
        
        $this->entityManager->flush();

        $io->success($newUrl);
        return Command::SUCCESS;
    }
}
