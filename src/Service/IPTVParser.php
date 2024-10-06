<?php

namespace App\Service;

use App\Entity\IptvChannel;
use App\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class IPTVParser
{

    public function __construct(
        private EntityManagerInterface $entityManager, private LoggerInterface $logger)
    {}

    public function parseAndSave(string $content, callable $progressCallback, callable $outputCallback): void
    {
        $lines = explode("\n", $content);
        foreach ($lines as $index => $line) {
            try {
                $progressCallback($index);
                $processedUrls = [];
                $channel = new IptvChannel();

                if (strpos($line, '#EXTINF:') === 0) {
                    preg_match('/tvg-name="([^"]*)" tvg-logo="([^"]*)" group-title="([^"]*)"/', $line, $matches);
                    $tvgName = $matches[1] ?? '';
                    $tvgLogo = $matches[2] ?? '';
                    $groupTitle = $matches[3] ?? '';
                    $name = trim(substr($line, strrpos($line, ',') + 1));
                    $url = trim($lines[$index + 1] ?? '');

                    if (empty($url)) {
                        // throw new \Exception("URL is missing for channel: $name");
                        $this->logger->error("URL is missing for channel: $name");
                    }

                    if (in_array($url, $processedUrls)) {
                        $this->logger->info("Duplicate URL found and skipped: $url");
                        continue;
                    }

                    $url = $url . '||user-agent=airmaxtv';
                    $processedUrls[] = $url;

                    
                    $channel->setTitle($name);
                    $channel->setUrl($url);
                    $channel->setTvgName($tvgName);
                    $channel->setTvgLogo($tvgLogo);
                    $channel->setGroupTitle($groupTitle);
                    $this->entityManager->persist($channel);
                    // $outputCallback(true, $channel);
                }
            } catch (\Exception $e) {
                $this->logger->error('Error parsing line: ' . $line, ['exception' => $e]);
                $outputCallback(false, $channel);
            }

        }

        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->logger->error('Error saving to database', ['exception' => $e]);
            $outputCallback(false, $channel);
            // throw $e; // Re-throw the exception to be handled by the command
        }
    }


}