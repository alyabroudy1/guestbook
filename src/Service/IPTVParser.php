<?php

namespace App\Service;

use App\Entity\AirmaxCredential;
use App\Entity\Dto\IptvSegmentDTO;
use App\Entity\IptvChannel;
use App\Entity\Link;
use App\Repository\AirmaxCredentialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IPTVParser
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface        $logger,
        private HttpClientInterface    $httpClient,
        private AirmaxCredentialRepository $credentialRepo
    )
    {
    }

    public function fetchContent(string $url, array $headers = []): string
    {
        $response = $this->httpClient->request('GET', $url, [
            'headers' => $headers,
        ]);
//        return '
//
//#EXTINF:-1 tvg-id="ZarokTV.tr" tvg-logo="https://i.imgur.com/o0eevnb.png" group-title="🇹🇷 Turkey",Zarok TV
//https://zindikurmanci.zaroktv.com.tr/hls/stream.m3u8
//
//
//
//
//#EXTM3U
//#EXTINF:-1 tvg-name="Das Erste Ⓖ" tvg-logo="https://upload.wikimedia.org/wikipedia/commons/thumb/c/ca/Das_Erste_2014.svg/640px-Das_Erste_2014.svg.png" tvg-id="DasErste.de" group-title="🇩🇪 Germany",Das Erste Ⓖ
//https://mcdn.daserste.de/daserste/de/master.m3u8
//#EXTINF:1 tvg-id="RTLZweiDeutschland.de" tvg-language="German" tvg-logo="https://i.imgur.com/0dDMVLa.png" group-title="🇩🇪 Germany",RTL Zwei
//https://s6.hopslan.com/rtl2x1/tracks-v1a1/mono.m3u8
//#EXTINF:1 tvg-id="RTLZweiDeutschland.de" tvg-language="German" tvg-logo="https://i.imgur.com/0dDMVLa.png" group-title="🇩🇪 Germany",RTL Zwei+
//http://178.219.128.68:64888/RTL2';

        return $response->getContent();
        return '
 #EXTINF:-1 tvg-id="TamazightTV.ma" tvg-logo="https://i.imgur.com/fm6S7we.png" group-title="🇲🇦المغربMAROCCO",Tamazight
 #EXTVLCOPT:http-referrer=https://snrtlive.ma/
 https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/tamazight_tv8_snrt/hls_snrt/index.m3u8
 #EXTINF:-1 tvg-id="DWDeutsch.de" tvg-logo="https://i.imgur.com/8MRNFb9.png" group-title="🇩🇪 Germany" user-agent="Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML,like Gecko) Mobile/15E148",Deutsche Welle
 #EXTVLCOPT:http-user-agent=Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML,like Gecko) Mobile/15E148
 http://ott-cdn.ucom.am/s26/index.m3u8
 #EXTINF:-1 tvg-id="ext"tvg-logo="https://bit.ly/3JQfa8u" group-title="🇲🇦المغربMAROCCO",المغربMAROCCO 🇲🇦
 https://bit.ly/3xYLaRh
 #EXTINF:-1 tvg-id="2MInternational.ma" tvg-logo="https://i.imgur.com/MvpntzA.png" group-title="🇲🇦المغربMAROCCO",2M Monde
 https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/2m_monde/hls_video_ts_tuhawxpiemz257adfc/2m_monde.m3u8
 #EXTINF:-1 tvg-id="AlAoulaEurope.ma" tvg-logo="https://i.imgur.com/df7D3KR.png" group-title="🇲🇦المغربMAROCCO",Al Aoula International
 #EXTVLCOPT:http-referrer=https://snrtlive.ma/
 https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/al_aoula_inter/hls_snrt/al_aoula_inter.m3u8
 #EXTINF:-1 tvg-id="ext"tvg-logo="https://i.ibb.co/n0Pwp2S/alaoulainter.png" group-title="🇲🇦المغربMAROCCO",AL_Aoula_InterTV HD 🇲🇦
 https://livestream.zazerconer.workers.dev/channel/UC1X2nRRWPptr88W_N9RWS1g.m3u8
 #EXTINF:-1 tvg-id="AlAoulaLaayoune.ma" tvg-logo="https://i.imgur.com/wFgljHj.png" group-title="🇲🇦المغربMAROCCO",Al Aoula Laayoune
 #EXTVLCOPT:http-referrer=https://snrtlive.ma/
 https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/al_aoula_laayoune/hls_snrt/index.m3u8
 #EXTINF:-1 tvg-id="AlMaghribia.ma" tvg-logo="https://i.imgur.com/7GaahYh.png" group-title="🇲🇦المغربMAROCCO",Al Maghribia
 #EXTVLCOPT:http-referrer=https://snrtlive.ma/
 https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/al_maghribia_snrt/hls_snrt/index.m3u8
 #EXTINF:-1 tvg-id="Athaqafia.ma" tvg-logo="https://i.imgur.com/mrwFI2L.png" group-title="🇲🇦المغربMAROCCO",Arrabiaa
 #EXTVLCOPT:http-referrer=https://snrtlive.ma/
 https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/arrabiaa/hls_snrt/index.m3u8
 #EXTINF:-1 tvg-id="Arryadia.ma" tvg-logo="https://i.imgur.com/XjzK3gZ.png" group-title="🇲🇦المغربMAROCCO",Arryadia
 #EXTVLCOPT:http-referrer=https://snrtlive.ma/
 https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/arriadia/hls_snrt/index.m3u8
 #EXTINF:-1 tvg-id="ext"tvg-logo="https://bit.ly/39TLOm8" group-title="🇲🇦المغربMAROCCO",MR-AL MAGHRIBIA HD🇲🇦
 #EXTVLCOPT:http-referrer=https://snrtlive.ma/
 https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/al_maghribia_snrt/hls_snrt/index.m3u8
 #EXTINF:-1 tvg-id="Assadissa.ma" tvg-logo="https://i.imgur.com/un6qTGO.png" group-title="🇲🇦المغربMAROCCO",Assadissa
 #EXTVLCOPT:http-referrer=https://snrtlive.ma/
 https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/assadissa/hls_snrt/index.m3u8
 #EXTINF:-1 tvg-id="ext"tvg-logo="http://gratuittv.free.fr/images/aflamtv.png" group-title="🇲🇦المغربMAROCCO",Aflam TV 🇲🇦
 http://gratuittv.free.fr/Files/aflam7/live/playlist.m3u8';


//         return '#EXTINF:-1 tvg-id="movie" tvg-logo="https://bit.ly/3uTlfI6" group-title="movie ماما عنايه",الحلقةS01E15
// https://fvideo6b.ddns.me/vod5nn/mama-3naya-15.mp4/chunk.m3u8?/ss/
// #EXTINF:-1 tvg-id="movie" tvg-logo="https://bit.ly/3uTlfI6" group-title="movie ماما عنايه",الحلقةS01E16
// https://fvideo6b.ddns.me/vod5nn/mama-3naya-16.mp4/chunk.m3u8?/ss/
// #EXTINF:-1 tvg-id="movie" tvg-logo="https://bit.ly/3uTlfI6" group-title="movie ماما عنايه",الحلقةS01E17
// https://fvideo6b.ddns.me/vod5nn/mama-3naya-17.mp4/chunk.m3u8?/ss/
// #EXTINF:-1 tvg-id="movie" tvg-logo="https://bit.ly/3uTlfI6" group-title="movie ماما عنايه",الحلقةS01E18
// https://fvideo6b.ddns.me/vod5nn/mama-3naya-18.mp4/chunk.m3u8?/ss/
// #EXTINF:-1 tvg-id="movie" tvg-logo="https://bit.ly/3uTlfI6" group-title="movie ماما عنايه",الحلقةS01E19
// https://fvideo6b.ddns.me/vod5nn/mama-3naya-19.mp4/chunk.m3u8?/ss/
// #EXTINF:-1 tvg-id="movie" tvg-logo="https://bit.ly/3uTlfI6" group-title="movie ماما عنايه",الحلقةS01E20
// https://fvideo6.ddns.me/vod5nn/mama-3naya-21.mp4/chunk.m3u8?/ss/
// #EXTINF:-1 tvg-id="movie" tvg-logo="https://bit.ly/3uTlfI6" group-title="movie ماما عنايه",الحلقةS01E21
// https://fvideo6b.ddns.me/vod5nn/mama-3naya-21.mp4/chunk.m3u8?/ss/
// #EXTINF:-1 tvg-id="movie" tvg-logo="https://bit.ly/3uTlfI6" group-title="movie ماما عنايه",الحلقةS01E22
// https://fvideo6b.ddns.me/vod5nn/mama-3naya-22.mp4/chunk.m3u8?/ss/
// #EXTINF:-1 tvg-id="movie" tvg-logo="https://bit.ly/3uTlfI6" group-title="movie ماما عنايه",الحلقةS01E23
// https://fvideo6b.ddns.me/vod5nn/mama-3naya-23.mp4/chunk.m3u8?/ss/
// #EXTINF:-1 tvg-id="movie" tvg-logo="https://bit.ly/3uTlfI6" group-title="movie ماما عنايه",الحلقةS01E24
// https://fvideo6b.ddns.me/vod5nn/mama-3naya-24.mp4/chunk.m3u8?/ss/
// #EXTINF:-1 tvg-id="movie" tvg-logo="https://bit.ly/3uTlfI6" group-title="movie ماما عنايه",الحلقةS01E25
// https://fvideo6.ddns.me/vod5nn/mama-3naya-25.mp4/chunk.m3u8?/ss/';
    }

    public function parseAndSave(string $content, callable $progressCallback, callable $outputCallback): void
    {
        // $lines = explode("\n", $content);


        $segments = explode('#EXTINF:', $content);
        array_shift($segments); // Remove the first empty element

        $segmentsSample = $segments[array_key_first($segments)];

        $this->extractAndSaveCredentials($segmentsSample);
        // foreach ($lines as $index => $line) {
        foreach ($segments as $index => $segment) {
            $progressCallback($index);
            $segmentDTO = $this->generateSegmentDTO($segment);
            if ($segmentDTO === null) {
                $this->logger->error("error parsing segment: $segment");
                continue;
            }
            if ($segmentDTO->tvgLogo == 'https://bit.ly/3JQfa8u') {
                continue;
            }

            $channel = new IptvChannel();
            $channel->setTitle($segmentDTO->name);
            $channel->setUrl($segmentDTO->url);
            $channel->setTvgName($segmentDTO->tvgName);
            $channel->setTvgLogo($segmentDTO->tvgLogo);
            $channel->setFileName($segmentDTO->fileName);
            $channel->setCredentialUrl($segmentDTO->credentialUrl);
            $groupTitle = $segmentDTO->groupTitle;
            if (empty($groupTitle)) {
                // throw new \Exception("URL is missing for channel: $name");
                $this->logger->error("GroupTitle is missing for channel: $segment");
//                dd($segment, $cleanedLines, $url, $groupTitle);
                // dd("URL is missing for channel: $segment");
                $groupTitle = $segmentDTO->id;
            }

            $channel->setGroupTitle($groupTitle);
            try {
                if (strlen($channel->getUrl()) > 1500) {
                    dd($channel, strlen($channel->getUrl()));
                }
                $this->entityManager->persist($channel);
            } catch (\Exception $e) {
                //throw $th;
                $outputCallback(false, $channel);
            }

        }

        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {

            dump($e->getMessage());
            $this->logger->error('Error saving to database', ['exception' => $e]);
        }
//        dd('done 1');
//
//
//        $segments = explode('#EXTINF:', $content);
//        array_shift($segments); // Remove the first empty element
//        // foreach ($lines as $index => $line) {
//        foreach ($segments as $index => $segment) {
//            $channel = new IptvChannel();
//            try {
//
//                $progressCallback($index);
//                $processedUrls = [];
//
//                // preg_match('/tvg-name="([^"]*)" tvg-logo="([^"]*)" group-title="([^"]*)"/', $line, $matches);
//                // $tvgName = $matches[1] ?? '';
//                // $tvgLogo = $matches[2] ?? '';
//                // $groupTitle = $matches[3] ?? '';
//                // $name = trim(substr($line, strrpos($line, ',') + 1));
//                // $url = trim($lines[$index + 1] ?? '');
//
////                $lines = array_filter(explode("\n", $segment), 'trim'); // Filter out empty lines
//                $lines = explode("\n", $segment); // Filter out empty lines
////                $infoLine = array_shift($lines);
////                dump($lines);
//
//                // Step 2: Trim each line and remove any empty ones.
//                $cleanedLines = array_filter(array_map('trim', $lines), function ($line) {
////                    dump('filter: ' .$line);
//                    return !(empty($line) || str_starts_with('#EXT', $line));
//                });
//
//                if (count($cleanedLines) < 2) {
//                    $this->logger->error("segment contains only one line for channel: $segment");
////                    dd($cleanedLines, $segment);
//                    continue;
//                }
//
//                $url = trim(array_pop($cleanedLines)); // The URL is the last line
//
//                if (empty($url) || !str_starts_with($url, 'http')) {
////                    dd($cleanedLines, $url);
//                    if (str_starts_with($url, '#')) {
//                        $url = str_replace('#', '', $url);
//                        if (!str_starts_with($url, 'http')) {
//                            $this->logger->error("URL doesnt start with http for channel: $segment");
////                            dd($segment, $cleanedLines, $url);
//                            dump($cleanedLines, $url);
//                            continue;
//                        }
//                    } else {
//                        while (count($cleanedLines) > 1) {
//                            $url = trim(array_pop($cleanedLines)); // The URL is the last line
//                            $url = str_replace('#', '', $url);
//                            if (str_starts_with($url, 'http')) {
//                                // throw new \Exception("URL is missing for channel: $name");
//                                break;
////                            dd($segment, $cleanedLines, $url);
//                                // dd("URL is missing for channel: $segment");
//                                continue;
//                            }
//                        }
//                        if (!str_starts_with($url, 'http')) {
//                            $this->logger->error("URl is missing for segment: $segment");
//                            continue;
//                        }
//                    }
//                }
//
//                if (in_array($url, $processedUrls)) {
//                    $this->logger->info("Duplicate URL found and skipped: $url");
//                    continue;
//                }
//
//
//
//                $processedUrls[] = $url;
//                $infoLine = $cleanedLines[0];
//
//                $tvgId = $this->extractAttribute($infoLine, 'tvg-id');
//                $tvgName = $this->extractAttribute($infoLine, 'tvg-name');
//                $tvgLogo = $this->extractAttribute($infoLine, 'tvg-logo');
//                $groupTitle = $this->extractAttribute($infoLine, 'group-title');
//                $name = trim(substr($infoLine, strrpos($infoLine, ',') + 1));
////                dd('163: ' , $tvgId, $tvgName, $tvgLogo, $groupTitle, $name, $url, $segment);
//
//                // dd($segment, 'id: '.$tvgId, 'gName: '.$tvgName, 'gTitle: '.$groupTitle,  'name: '.$name,'url: '.$url, 'logo: '.$tvgLogo);
//
//
//                $referrer = null;
//                foreach ($cleanedLines as $line) {
//                    if (strpos($line, '#EXTVLCOPT:') === 0) {
//                        if (strpos($line, 'http-user-agent=') !== false) {
//                            $userAgent = $this->extractVlcOpt($line, 'http-user-agent');
//                        } elseif (strpos($line, 'http-referrer=') !== false) {
//                            $referrer = $this->extractVlcOpt($line, 'http-referrer');
//                        }
//                    }
//                }
//                $url = $url . '||user-agent=' . $userAgent;
//                if ($referrer) {
//                    $url = $url . '&referrer=' . $referrer;
//                }
//                $channel->setTitle($name);
//                $channel->setUrl($url);
//                $channel->setTvgName($tvgName);
//                $channel->setTvgLogo($tvgLogo);
//
//                if (empty($groupTitle)) {
//                    // throw new \Exception("URL is missing for channel: $name");
//                    $this->logger->error("GroupTitle is missing for channel: $segment");
//                    dd($segment, $cleanedLines, $url, $groupTitle);
//                    // dd("URL is missing for channel: $segment");
//                    $groupTitle = $tvgId;
//                }
//
//                $channel->setGroupTitle($groupTitle);
//                try {
//                    $this->entityManager->persist($channel);
//                } catch (\Exception $e) {
//                    //throw $th;
//                    $outputCallback(false, $channel);
//                }
//                // dump($channel->getUrl());
//                // $outputCallback(true, $channel);
//
//            } catch (\Exception $e) {
//                dd($e->getMessage());
//                $this->logger->error('Error parsing line: ' . $segment, ['exception' => $e]);
//                // dd($e->getMessage(), $segment);
//                $outputCallback(false, $channel);
//            }
//
//        }
//
//        try {
//            $this->entityManager->flush();
//        } catch (\Exception $e) {
//            $this->logger->error('Error saving to database', ['exception' => $e]);
//            // $outputCallback(false, $channel);
//            // throw $e; // Re-throw the exception to be handled by the command
//        }
    }

    private function extractAttribute(string $line, string $attribute): ?string
    {
//        preg_match('/' . $attribute . '="([^"]*)"/', $line, $matches);
        // Modify the regular expression to stop at " or ,
        preg_match('/' . $attribute . '="([^",]*)/', $line, $matches);
        return $matches[1] ?? null;
    }

    private function extractVlcOpt(string $line, string $option): ?string
    {
        preg_match('/' . $option . '=(.*)/', $line, $matches);
        return $matches[1] ?? null;
    }

    private function generateSegmentDTO(string $segment)
    {

        try {

//            $progressCallback($index);
            $processedUrls = [];

            // preg_match('/tvg-name="([^"]*)" tvg-logo="([^"]*)" group-title="([^"]*)"/', $line, $matches);
            // $tvgName = $matches[1] ?? '';
            // $tvgLogo = $matches[2] ?? '';
            // $groupTitle = $matches[3] ?? '';
            // $name = trim(substr($line, strrpos($line, ',') + 1));
            // $url = trim($lines[$index + 1] ?? '');

//                $lines = array_filter(explode("\n", $segment), 'trim'); // Filter out empty lines
            $lines = explode("\n", $segment); // Filter out empty lines
//                $infoLine = array_shift($lines);
//                dump($lines);
            // Step 2: Trim each line and remove any empty ones.
            $cleanedLines = array_filter(array_map('trim', $lines), function ($line) {
//                    dump('filter: ' .$line);
//                return !(empty($line) || str_starts_with('#EXT', $line));
                return !empty($line);
            });
            // pop the first element of the lines
            $infoLine = array_shift($cleanedLines);

            $url = null;
            if (count($cleanedLines) > 0) {
                //todo extract extra infos
                $url = $this->extractUrl($cleanedLines); // The URL is the last line

//                dump($extraLines);
//                $this->logger->error("segment contains only one line for channel: $segment");
////                    dd($cleanedLines, $segment);
//                return null;
            }
            if ($url === null) {
//                dump($segment);
                return null;
            }
            $fileName = null;
            $credentialUrl = null;

            try {
                $parsedUrl = parse_url($url);
                if (!$parsedUrl || !array_key_exists('host', $parsedUrl)) {
                    $this->logger->error("host is missing for url: $url");
                    return null;
                }
                if ($parsedUrl && str_contains($parsedUrl['host'], 'airmax')) {
                    $pathParts = explode('/', ltrim($parsedUrl['path'], '/'));
                    $domain = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . (isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '') . '/';
                    $username = $pathParts[0] ?? '';
                    $password = $pathParts[1] ?? '';
                    $fileName = end($pathParts);

                    // Extract the part after the last occurrence of '|'
                    if (strpos($fileName, '|') !== false) {
                        $fileNameParts = explode('|', $fileName);
                        $fileName = $fileNameParts[0];  // Get the part after the last '|'
                    }
                    $credentialUrl = $domain . $username . '/' . $password . '/';
                    // dd($channel->getCredentialUrl());
                }

            } catch (\Exception $e) {
                //throw $th;
                dump($e->getMessage(), $url);
            }

//            dd($infoLine, $cleanedLines, $url);

            $tvgId = $this->extractAttribute($infoLine, 'tvg-id');
            $tvgName = $this->extractAttribute($infoLine, 'tvg-name');
            $tvgLogo = $this->extractAttribute($infoLine, 'tvg-logo');
            $groupTitle = $this->extractAttribute($infoLine, 'group-title');
            $name = trim(substr($infoLine, strrpos($infoLine, ',') + 1));


            $segmentDTO = new IptvSegmentDTO();
            $segmentDTO->id = $tvgId;
            $segmentDTO->tvgName = $tvgName;
            $segmentDTO->tvgLogo = $tvgLogo;
            $segmentDTO->groupTitle = $groupTitle;
            $segmentDTO->name = $name;
//            if ($fileName) {
//                $segmentDTO->url = 'http://194.164.53.40/movie/fetch/' . $fileName;
//            } else {
//                $segmentDTO->url = $url;
//            }
            $segmentDTO->url = $url;
            $segmentDTO->fileName = $fileName;
            $segmentDTO->credentialUrl = $credentialUrl;
            return $segmentDTO;


            //////////////////////
//            if (in_array($url, $processedUrls)) {
//                $this->logger->info("Duplicate URL found and skipped: $url");
//                return null;
//            }
//            try {
//                $parsedUrl = parse_url($url);
//                if (!array_key_exists('host', $parsedUrl)) {
//                    $this->logger->error("host is missing for url: $url");
//                    dd($segment, $cleanedLines, $url);
//                    return null;
//                }
//                if ($parsedUrl && str_contains($parsedUrl['host'], 'airmax')) {
//                    $pathParts = explode('/', ltrim($parsedUrl['path'], '/'));
//                    $domain = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . (isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '') . '/';
//                    $username = $pathParts[0] ?? '';
//                    $password = $pathParts[1] ?? '';
//                    $filename = end($pathParts);
//                    $channel->setFilename($filename);
//                    $channel->setCredentialUrl($domain . $username . '/' . $password . '/');
//                    // dd($channel->getCredentialUrl());
//                }
//
//            } catch (\Exception $e) {
//                //throw $th;
//                dump($e->getMessage(), $url);
//            }
//
//
//            $processedUrls[] = $url;

//                dd('163: ' , $tvgId, $tvgName, $tvgLogo, $groupTitle, $name, $url, $segment);

            // dd($segment, 'id: '.$tvgId, 'gName: '.$tvgName, 'gTitle: '.$groupTitle,  'name: '.$name,'url: '.$url, 'logo: '.$tvgLogo);
//
//
//            $referrer = null;
//            foreach ($cleanedLines as $line) {
//                if (strpos($line, '#EXTVLCOPT:') === 0) {
//                    if (strpos($line, 'http-user-agent=') !== false) {
//                        $userAgent = $this->extractVlcOpt($line, 'http-user-agent');
//                    } elseif (strpos($line, 'http-referrer=') !== false) {
//                        $referrer = $this->extractVlcOpt($line, 'http-referrer');
//                    }
//                }
//            }
//            $url = $url . '||user-agent=' . $userAgent;
//            if ($referrer) {
//                $url = $url . '&referrer=' . $referrer;
//            }
//            $channel->setTitle($name);
//            $channel->setUrl($url);
//            $channel->setTvgName($tvgName);
//            $channel->setTvgLogo($tvgLogo);
//
//            if (empty($groupTitle)) {
//                // throw new \Exception("URL is missing for channel: $name");
//                $this->logger->error("GroupTitle is missing for channel: $segment");
//                dd($segment, $cleanedLines, $url, $groupTitle);
//                // dd("URL is missing for channel: $segment");
//                $groupTitle = $tvgId;
//            }
//
//            $channel->setGroupTitle($groupTitle);
//            try {
//                $this->entityManager->persist($channel);
//            } catch (\Exception $e) {
//                //throw $th;
//                $outputCallback(false, $channel);
//            }
            // dump($channel->getUrl());
            // $outputCallback(true, $channel);

        } catch (\Exception $e) {
//            dd($e->getMessage());
            $this->logger->error('Error parsing line: ' . $segment, ['exception' => $e]);
            // dd($e->getMessage(), $segment);
//            $outputCallback(false, $channel);
        }
    }

    private function extractUrl(array $lines)
    {
//        $url = trim($url); // The URL is the last line
        $referrer = null;
        $userAgent = 'airmaxtv';
//        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36';
        $url = null;

        foreach ($lines as $line) {
            if (str_starts_with($line, 'http')) {
                $url = $line;
                continue;
            }
            if (strpos($line, '#EXTVLCOPT:') === 0) {
                if (strpos($line, 'http-user-agent=') !== false) {
                    $userAgent = $this->extractVlcOpt($line, 'http-user-agent');
                } elseif (strpos($line, 'http-referrer=') !== false) {
                    $referrer = $this->extractVlcOpt($line, 'http-referrer');
                }
            }
        }
        if ($url === null) {
            $url = array_pop($lines);
            if (!str_starts_with($url, '#http')) {
//                dd($lines);
                return null;
            }
            $url = str_replace('#http', 'http', $url);
        }
        $url = $url . '|user-agent=' . $userAgent;
        if ($referrer) {
            $url = $url . '&referrer=' . $referrer;
        }

//        if (empty($url) || !str_starts_with($url, 'http')) {
//            if (str_starts_with($url, '#')) {
//                $url = str_replace('#', '', $url);
//                if (!str_starts_with($url, 'http')){
////                    $this->logger->error("URL doesnt start with http for channel: $segment");
////                            dd($segment, $cleanedLines, $url);
//                    dd("URL doesnt start with http for channel: $url");
//                    return $url;
//                }
//            }else{
//                while (count($cleanedLines) > 1){
//                    $url = trim(array_pop($cleanedLines)); // The URL is the last line
//                    $url = str_replace('#', '', $url);
//                    if (str_starts_with($url, 'http')) {
//                        // throw new \Exception("URL is missing for channel: $name");
//                        break;
////                            dd($segment, $cleanedLines, $url);
//                        // dd("URL is missing for channel: $segment");
//                        continue;
//                    }
//                }
//                if (!str_starts_with($url, 'http')) {
//                    dd("URL missing for channel: $url");
//                    return $url;
//                }
//            }
//        }
        return $url;
    }

    private function extractAndSaveCredentials(string $segmentsSample)
    {
        if (!$segmentsSample || !str_contains($segmentsSample, 'airmax')) {
            $this->logger->error("error parsing credentials: $segmentsSample");
            return;
        }
        $segment = $this->generateSegmentDTO($segmentsSample);
        if ($segment->credentialUrl != null) {
            $this->credentialRepo->updateCredentials($segment->credentialUrl);

        }
    }

    public function parseInfoLine(string $infoLine)
    {
        if (!str_starts_with($infoLine, '#EXTINF')) {
            return null;
        }

        $tvgLogo = $this->extractAttribute($infoLine, 'tvg-logo');

        if ($tvgLogo == 'https://bit.ly/3JQfa8u') {
            return null;
        }
        $groupTitle = $this->extractAttribute($infoLine, 'group-title');

        if (str_contains(trim($groupTitle), 'sport')) {
            return null;
        }

        $tvgId = $this->extractAttribute($infoLine, 'tvg-id');
        $tvgName = $this->extractAttribute($infoLine, 'tvg-name');


        $name = trim(substr($infoLine, strrpos($infoLine, ',') + 1));


        $iptvChannel = new IptvChannel();
//        $iptvChannel->setUrl($tvgId);
        $iptvChannel->setTvgName($tvgName ?? $tvgId);
        $iptvChannel->setTvgLogo($tvgLogo);
        $iptvChannel->setGroupTitle($groupTitle);
        $iptvChannel->setTitle($name !== '' ? $name : $tvgName);
//            if ($fileName) {
//                $segmentDTO->url = 'http://194.164.53.40/movie/fetch/' . $fileName;
//            } else {
//                $segmentDTO->url = $url;
//            }
//        $segmentDTO->url = $url;
//        $segmentDTO->fileName = $fileName;
//        $segmentDTO->credentialUrl = $credentialUrl;
        return $iptvChannel;
    }

    public function parseCurrentIptvSegmentExtraInfo(string $line, IptvChannel $currentSegment)
    {
        if (str_starts_with($line, 'http')) {
            $currentSegment->setUrl($line);
        }

        // todo extract :
//        if (strpos($line, '#EXTVLCOPT:') === 0) {
//            if (strpos($line, 'http-user-agent=') !== false) {
//                $userAgent = $this->extractVlcOpt($line, 'http-user-agent');
//            } elseif (strpos($line, 'http-referrer=') !== false) {
//                $referrer = $this->extractVlcOpt($line, 'http-referrer');
//            }
//        }

        return $currentSegment;
    }

}
