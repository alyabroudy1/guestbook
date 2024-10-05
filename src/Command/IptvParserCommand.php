<?php

namespace App\Command;

use App\Service\IPTVParser;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:iptv:parse',
    description: 'Fetch and parse IPTV .m3u8 list and save to database',
)]
class IptvParserCommand extends Command
{
    public function __construct(
        private IPTVParser $iptvParser,
        private LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('playlist', InputArgument::REQUIRED, 'playlist url')
            // ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $playlistLink = $input->getArgument('playlist');

        if ($playlistLink) {
            $io->note(sprintf('parsing playlist: %s', $playlistLink));
        }

        try {
            $content = file_get_contents($playlistLink);
            if ($content === false) {
                $io->error('Failed to fetch the IPTV list.');
                return Command::FAILURE;
            }

            $lines = explode("\n", $content);
            $totalLines = count($lines);
            $progressBar = new ProgressBar($output, $totalLines);
            $progressBar->start();

            $this->iptvParser->parseAndSave($content, function ($index) use ($progressBar) {
                $progressBar->setProgress($index);
            });

            $progressBar->finish();
            $io->newLine();
            $io->success('IPTV list has been fetched and saved to the database.');
        } catch (\Exception $e) {
            $this->logger->error('An error occurred during the command execution', ['exception' => $e]);
            $io->error('An error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }


        return Command::SUCCESS;
    }
}
