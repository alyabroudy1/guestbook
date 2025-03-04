<?php

namespace App\Command;

use App\Entity\IptvChannel;
use App\Repository\IptvChannelRepository;
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
    name: 'app:parse-iptv',
    description: 'Fetch and parse IPTV .m3u8 list and save to database',
)]
class IptvParserCommand extends Command
{
    private const BATCH_SIZE = 50; // Smaller batch size for low memory
    private const PLAYLIST_LINKS = [
        [
            'url' => 'https://raw.githubusercontent.com/airtech35/airtech35/refs/heads/airtech35-patch-1/arach',
            'headers' => ['User-Agent: Mozilla/5.0', 'Accept: */*'],
            'provider' => 'airmaxtv',
            'description' => 'Channels'
        ],
        [
            'url' => 'https://raw.githubusercontent.com/airtech35/airtech35/refs/heads/airtech35-patch-1/m200ovie',
            'headers' => ['User-Agent: Mozilla/5.0', 'Accept: */*'],
            'provider' => 'airmaxtv',
            'description' => 'Movies'
        ],
        [
            'url' => 'https://raw.githubusercontent.com/airtech35/airtech35/refs/heads/airtech35-patch-1/sss2025',
            'headers' => ['User-Agent: Mozilla/5.0', 'Accept: */*'],
            'provider' => 'airmaxtv',
            'description' => 'Series 2025'
        ],
        [
            'url' => 'https://raw.githubusercontent.com/airtech35/airtech35/airtech35-patch-1/airtech2024pluss2',
            'headers' => ['User-Agent: Mozilla/5.0', 'Accept: */*'],
            'provider' => 'airmaxtv',
            'description' => 'Series 2024'
        ],
        [
            'url' => 'https://raw.githubusercontent.com/airtech35/airtech35/refs/heads/airtech35-patch-1/Serier2023',
            'headers' => ['User-Agent: Mozilla/5.0', 'Accept: */*'],
            'provider' => 'airmaxtv',
            'description' => 'Series 2023'
        ],
    ];
    public function __construct(
        private IPTVParser $iptvParser,
        private IptvChannelRepository $iptvRepo,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Parse predefined IPTV playlists and save channels to database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->iptvRepo->truncateAll();
            $io->info('All old data removed');

            foreach (self::PLAYLIST_LINKS as $playlist) {
                if (!$this->processPlaylist($playlist, $io)) {
                    $io->warning(sprintf('Skipped playlist: %s', $playlist['url']));
                    continue;
                }
                // Force garbage collection between playlists
                gc_collect_cycles();
            }
            $io->success('All playlists processing completed');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->handleException($e, $io);
            return Command::FAILURE;
        }finally {
            // Ensure memory is cleared even on failure
            $this->entityManager->clear();
            gc_collect_cycles();
        }
    }

    private function processPlaylist(array $playlist, SymfonyStyle $io): bool
    {
        $io->section(sprintf('Processing playlist: %s', $playlist['url']));

        // Clear old data for specific provider
//        if (str_contains($playlist['provider'], 'airmaxtv')) {
//            $this->clearOldPaidList($io);
//        }

        // Stream content instead of loading all at once
        $stream = $this->openStream($playlist['url'], $this->parseHeaders($playlist['headers']), $io);
        if ($stream === false) {
            return false;
        }

        return $this->processStream($stream, $io, $playlist['url']);
    }
    private function parseHeaders(array $headers): array
    {
        return array_reduce($headers, function (array $carry, string $header): array {
            [$key, $value] = explode(':', $header, 2);
            $carry[trim($key)] = trim($value);
            return $carry;
        }, []);
    }

    private function clearOldPaidList(SymfonyStyle $io): void
    {
        $this->iptvRepo->removeOldPaidList();
        $io->info('Old paid list removed');
    }
    private function openStream(string $url, array $headers, SymfonyStyle $io)
    {
        $context = stream_context_create([
            'http' => [
                'header' => array_map(fn($k, $v) => "$k: $v", array_keys($headers), $headers),
                'timeout' => 30.0
            ]
        ]);

        $stream = @fopen($url, 'r', false, $context);
        if ($stream === false) {
            $io->error('Failed to open stream for: ' . $url);
            return false;
        }
        return $stream;
    }

    private function processStream($stream, SymfonyStyle $io, string $url): bool
    {
        $progressBar = new ProgressBar($io);
        $progressBar->start();
        $processed = 0;
        $batch = [];

        /** @var IptvChannel|null $currentSegment */
        $currentSegment = null;
        while (!feof($stream)) {
            $line = fgets($stream, 2048); // Limit buffer size
            if ($line === false || trim($line) === '') {
                continue;
            }
            $line = trim($line);

            // Process complete channel entries (assuming M3U format with #EXTINF)
//            if (str_contains($line, '#EXTINF') || str_contains($line, 'http')) {
            try {
                if (str_starts_with($line, '#EXTINF')) {
                    if ($currentSegment !== null && $currentSegment->getUrl() !== null) {
                        $batch[] = $currentSegment;
                        $processed++;
                        $progressBar->setProgress($processed);

                        if (count($batch) >= self::BATCH_SIZE) {
                            $this->iptvRepo->saveBatch($batch, true);
                            $batch = [];
                            gc_collect_cycles(); // Free memory
                        }
//                        dump(
//                            'title: ' . $currentSegment->getTitle(),
//                            'tvgName: ' . $currentSegment->getTvgName(),
//                            'gTitle: ' . $currentSegment->getGroupTitle(),
//                            'url: ' . $currentSegment->getUrl(),
//                        );
                    }
                    $currentSegment = $this->iptvParser->parseInfoLine($line);

                }
                // continue if no data filled in EXTINF
                if ($currentSegment !== null) {
                    $currentSegment = $this->iptvParser->parseCurrentIptvSegmentExtraInfo($line, $currentSegment);
                }
            }catch (\Exception $e) {
                $this->logFailedChannel($currentSegment, $io);
                $this->handleException($e, $io);
//                dd($e->getMessage(), $line);
            }

        }

        // Save the last segment if it exists
        if ($currentSegment !== null && $currentSegment->getUrl() !== null) {
            $batch[] = $currentSegment;
            $processed++;
        }

        // Save any remaining batch
        if (!empty($batch)) {
            $this->iptvRepo->saveBatch($batch, true);
        }

        fclose($stream);
        $progressBar->finish();
        $io->newLine();
        $io->success(sprintf('Playlist. Channels: %d', $progressBar->getProgress()));

        return true;
    }

    private function logFailedChannel(IptvChannel $channel, SymfonyStyle $io): void
    {
        $message = sprintf(
            "Failed - Name: %s, Group: %s, URL: %s",
            $channel->getTvgName() ?: 'N/A',
            $channel->getGroupTitle() ?: 'N/A',
            $channel->getUrl() ?: 'N/A'
        );
        $io->warning($message);
    }

    private function handleException(\Exception $e, SymfonyStyle $io): void
    {
        $this->logger->error('IPTV parsing failed', [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $io->error('Error occurred: ' . $e->getMessage());
    }


}
