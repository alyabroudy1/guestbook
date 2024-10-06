<?php

namespace App\Service;

use App\Entity\IptvChannel;
use App\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IPTVParser
{

    public function __construct(
        private EntityManagerInterface $entityManager,
         private LoggerInterface $logger,
         private HttpClientInterface $httpClient
         )
    {}

    public function fetchContent(string $url, array $headers = []): string
    {
        $response = $this->httpClient->request('GET', $url, [
            'headers' => $headers,
        ]);

        return $response->getContent();
// return '
// #EXTINF:-1 tvg-id="TamazightTV.ma" tvg-logo="https://i.imgur.com/fm6S7we.png" group-title="🇲🇦المغربMAROCCO",Tamazight 
// #EXTVLCOPT:http-referrer=https://snrtlive.ma/
// https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/tamazight_tv8_snrt/hls_snrt/index.m3u8
// #EXTINF:-1 tvg-id="DWDeutsch.de" tvg-logo="https://i.imgur.com/8MRNFb9.png" group-title="🇩🇪 Germany" user-agent="Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML,like Gecko) Mobile/15E148",Deutsche Welle 
// #EXTVLCOPT:http-user-agent=Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML,like Gecko) Mobile/15E148
// http://ott-cdn.ucom.am/s26/index.m3u8
// #EXTINF:-1 tvg-id="ext"tvg-logo="https://bit.ly/3JQfa8u" group-title="🇲🇦المغربMAROCCO",المغربMAROCCO 🇲🇦
// https://bit.ly/3xYLaRh
// #EXTINF:-1 tvg-id="2MInternational.ma" tvg-logo="https://i.imgur.com/MvpntzA.png" group-title="🇲🇦المغربMAROCCO",2M Monde 
// https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/2m_monde/hls_video_ts_tuhawxpiemz257adfc/2m_monde.m3u8
// #EXTINF:-1 tvg-id="AlAoulaEurope.ma" tvg-logo="https://i.imgur.com/df7D3KR.png" group-title="🇲🇦المغربMAROCCO",Al Aoula International 
// #EXTVLCOPT:http-referrer=https://snrtlive.ma/
// https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/al_aoula_inter/hls_snrt/al_aoula_inter.m3u8
// #EXTINF:-1 tvg-id="ext"tvg-logo="https://i.ibb.co/n0Pwp2S/alaoulainter.png" group-title="🇲🇦المغربMAROCCO",AL_Aoula_InterTV HD 🇲🇦
// https://livestream.zazerconer.workers.dev/channel/UC1X2nRRWPptr88W_N9RWS1g.m3u8
// #EXTINF:-1 tvg-id="AlAoulaLaayoune.ma" tvg-logo="https://i.imgur.com/wFgljHj.png" group-title="🇲🇦المغربMAROCCO",Al Aoula Laayoune 
// #EXTVLCOPT:http-referrer=https://snrtlive.ma/
// https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/al_aoula_laayoune/hls_snrt/index.m3u8
// #EXTINF:-1 tvg-id="AlMaghribia.ma" tvg-logo="https://i.imgur.com/7GaahYh.png" group-title="🇲🇦المغربMAROCCO",Al Maghribia 
// #EXTVLCOPT:http-referrer=https://snrtlive.ma/
// https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/al_maghribia_snrt/hls_snrt/index.m3u8
// #EXTINF:-1 tvg-id="Athaqafia.ma" tvg-logo="https://i.imgur.com/mrwFI2L.png" group-title="🇲🇦المغربMAROCCO",Arrabiaa 
// #EXTVLCOPT:http-referrer=https://snrtlive.ma/
// https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/arrabiaa/hls_snrt/index.m3u8
// #EXTINF:-1 tvg-id="Arryadia.ma" tvg-logo="https://i.imgur.com/XjzK3gZ.png" group-title="🇲🇦المغربMAROCCO",Arryadia 
// #EXTVLCOPT:http-referrer=https://snrtlive.ma/
// https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/arriadia/hls_snrt/index.m3u8
// #EXTINF:-1 tvg-id="ext"tvg-logo="https://bit.ly/39TLOm8" group-title="🇲🇦المغربMAROCCO",MR-AL MAGHRIBIA HD🇲🇦
// #EXTVLCOPT:http-referrer=https://snrtlive.ma/
// https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/al_maghribia_snrt/hls_snrt/index.m3u8
// #EXTINF:-1 tvg-id="Assadissa.ma" tvg-logo="https://i.imgur.com/un6qTGO.png" group-title="🇲🇦المغربMAROCCO",Assadissa 
// #EXTVLCOPT:http-referrer=https://snrtlive.ma/
// https://cdnamd-hls-globecast.akamaized.net/live/ramdisk/assadissa/hls_snrt/index.m3u8
// #EXTINF:-1 tvg-id="ext"tvg-logo="http://gratuittv.free.fr/images/aflamtv.png" group-title="🇲🇦المغربMAROCCO",Aflam TV 🇲🇦
// http://gratuittv.free.fr/Files/aflam7/live/playlist.m3u8';


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
        $userAgent = 'airmaxtv';
        $segments = explode('#EXTINF:', $content);
                array_shift($segments); // Remove the first empty element
        // foreach ($lines as $index => $line) {
            foreach ($segments as $index => $segment) {
            $channel = new IptvChannel();
            try {
               
                $progressCallback($index);
                $processedUrls = [];
         
                    // preg_match('/tvg-name="([^"]*)" tvg-logo="([^"]*)" group-title="([^"]*)"/', $line, $matches);
                    // $tvgName = $matches[1] ?? '';
                    // $tvgLogo = $matches[2] ?? '';
                    // $groupTitle = $matches[3] ?? '';
                    // $name = trim(substr($line, strrpos($line, ',') + 1));
                    // $url = trim($lines[$index + 1] ?? '');

                    $lines = array_filter(explode("\n", $segment), 'trim'); // Filter out empty lines
                $infoLine = array_shift($lines);
                $url = trim(array_pop($lines)); // The URL is the last line

                if (empty($url)) {
                    // throw new \Exception("URL is missing for channel: $name");
                    $this->logger->error("URL is missing for channel: $segment");
                    // dd("URL is missing for channel: $segment");
                }

                if (in_array($url, $processedUrls)) {
                    $this->logger->info("Duplicate URL found and skipped: $url");
                    continue;
                }
                    try {
                        $parsedUrl = parse_url($url);
                    if ($parsedUrl && str_contains($parsedUrl['host'], 'airmax')) {
                        $pathParts = explode('/', ltrim($parsedUrl['path'], '/'));
                    $domain = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . (isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '') . '/';
                    $username = $pathParts[0] ?? '';
                    $password = $pathParts[1] ?? '';
                    $filename = end($pathParts);
                    $channel->setFilename($filename);
                    }

                    } catch (\Exception $e) {
                        //throw $th;
                        dump($e->getMessage(), $url);
                    }
                    
    
                    $processedUrls[] = $url;
                    


                    $tvgId = $this->extractAttribute($infoLine, 'tvg-id');
                    $tvgName = $this->extractAttribute($infoLine, 'tvg-name');
                $tvgLogo = $this->extractAttribute($infoLine, 'tvg-logo');
                $groupTitle = $this->extractAttribute($infoLine, 'group-title');
                $name = trim(substr($infoLine, strrpos($infoLine, ',') + 1));

                    // dd($segment, 'id: '.$tvgId, 'gName: '.$tvgName, 'gTitle: '.$groupTitle,  'name: '.$name,'url: '.$url, 'logo: '.$tvgLogo);
                    

                    $referrer = null;
                    foreach ($lines as $line) {
                        if (strpos($line, '#EXTVLCOPT:') === 0) {
                            if (strpos($line, 'http-user-agent=') !== false) {
                                $userAgent = $this->extractVlcOpt($line, 'http-user-agent');
                            } elseif (strpos($line, 'http-referrer=') !== false) {
                                $referrer = $this->extractVlcOpt($line, 'http-referrer');
                            }
                        }
                    }
                    $url = $url .'||user-agent='. $userAgent;
                    if($referrer){
                        $url = $url . '&referrer='.$referrer;
                    }
                    $channel->setTitle($name);
                    $channel->setUrl($url);
                    $channel->setTvgName($tvgName);
                    $channel->setTvgLogo($tvgLogo);

                    if (empty($groupTitle)) {
                        // throw new \Exception("URL is missing for channel: $name");
                        $this->logger->error("GroupTitle is missing for channel: $segment");
                        // dd("URL is missing for channel: $segment");
                        $groupTitle = $tvgId;
                    }

                    $channel->setGroupTitle($groupTitle);
                    try {
                        $this->entityManager->persist($channel);
                    } catch (\Exception $e) {
                        //throw $th;
                        $outputCallback(false, $channel);
                    }
                    // dump($channel->getUrl());
                    // $outputCallback(true, $channel);
            
            } catch (\Exception $e) {
                $this->logger->error('Error parsing line: ' . $segment, ['exception' => $e]);
                // dd($e->getMessage(), $segment);
                $outputCallback(false, $channel);
            }

        }

        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->logger->error('Error saving to database', ['exception' => $e]);
            // $outputCallback(false, $channel);
            // throw $e; // Re-throw the exception to be handled by the command
        }
    }

    private function extractAttribute(string $line, string $attribute): ?string
    {
        preg_match('/' . $attribute . '="([^"]*)"/', $line, $matches);
        return $matches[1] ?? null;
    }

    private function extractVlcOpt(string $line, string $option): ?string
    {
        preg_match('/' . $option . '=(.*)/', $line, $matches);
        return $matches[1] ?? null;
    }

}