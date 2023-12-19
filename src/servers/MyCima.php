<?php

namespace App\servers;

use App\Entity\Movie;
use App\Entity\MovieSource;
use App\Entity\Server;
use App\Entity\Source;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MyCima implements MovieServerInterface
{

    private ?int $id = null;
    private static ?MyCima $instance = null;

    private function __construct(private HttpClientInterface $httpClient, private Server $serverConfig)
    {
        $this->init();
    }

    public static function getInstance(HttpClientInterface $httpClient, Server $serverConfig): static
    {
        if (!self::$instance) {
            $instance = new self($httpClient, $serverConfig);
        }
        return $instance;
    }


    public function search_test($query): array
    {

        $movieList = [];

        $mainMovie1 = new Movie();
        $mainMovie1->setTitle("ratched-");
        $mainMovie1->setState(Movie::STATE_GROUP_OF_GROUP);

        $source1 = new Source();
        $source1->setState(Movie::STATE_GROUP_OF_GROUP);
        $source1->setServer($this->serverConfig);
        $source1->setVidoUrl("ratcheds1-season");
        $mainMovie1->addSource($source1);


//        $mainMovie1 = new Movie();
//        $mainMovie1->setTitle("ratched sub1");
//        $mainMovie1->setState(Movie::STATE_GROUP);
//
//        $source1 = new Source();
//        $source1->setState(Movie::STATE_ITEM);
//        $source1->setServer($this->serverConfig);
//        $source1->setVidoUrl("ratcheds1s1");
//        $mainMovie1->addSource($source1);

//        $mainMovie2 = new Movie();
//        $mainMovie2->setTitle("ss ratched s2");
//
//        $source2 = new Source();
//        $source2->setState(Movie::STATE_ITEM);
//        $source2->setServer($this->serverConfig);
//        $source2->setVidoUrl("ratcheds2");
//        $mainMovie2->addSource($source2);

        $movieList[] = $mainMovie1;
        //     $movieList[] = $mainMovie2;

        return $movieList;
    }


    public function search(string $query, Request $request): array
    {
        $movieList = [];
        // Log the query
        // You may need Monolog or Symfony's built-in logger.
        // $this->logger->info("search: " . $query);

        $searchContext = $query;
        $multiSearch = false;

        if (!str_contains($query, "http")) {
//            if (isset($referer) && !empty($referer)) {
//                $query = rtrim($referer, '/') . "/search/" . $query;
//            } else {
//                $query = $this->websiteUrl . "/search/" . $query;
//            }
            $query = $this->serverConfig->getWebAddress() . '/search/' . $query;
//            $multiSearch = true;

        }

        $response = $this->httpClient->request('GET', $query);
        if ($response->getStatusCode() === Response::HTTP_OK){

            $html = $response->getContent();
            $crawler = new Crawler($html);
            $movieList = [];

            // The beauty of Symfony's DomCrawler component is that it can work as a jQuery-like syntax
            $crawler->filter('.GridItem')->each(function (Crawler $node) use (&$movieList, $query) {
                $linkElem = $node->filter('[href]')->first();
                if ($linkElem->count() > 0) {
                    $videoUrl = $linkElem->attr('href');

                    $title = $node->filter('[title]')->first()->attr('title');
                    $imageElem = $node->filter('[style]')->first();
                    $image = $imageElem->count() > 0 ? $imageElem->attr('style') : "https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png";

                    if (!str_contains($image, "http")) {
                        $image2Elem = $node->filter('[data-lazy-style]')->first();
                        $image = $image2Elem->count() > 0 ? $image2Elem->attr('data-lazy-style') : $image;
                        if ($image === "") {
                            $image3Elem = $node->filter('.BG--GridItem')->first();
                            $image = $image3Elem->count() > 0 ? $image3Elem->attr('data-owl-img') : $image;
                        }
                    }

                    if (str_contains($image, "(") && str_contains($image, ")")) {
                        $image = substr($image, strpos($image, '(') + 1, strpos($image, ')') - strpos($image, '(') - 1);
                    }


                    if (preg_match('~(https?://[^/]+)(/.*)~', $image, $imageMatches)) {
                        if (count($imageMatches) > 1) {
                            if ($imageMatches[1] === $this->serverConfig->getWebAddress()) {
                                $image = $imageMatches[2];
                            }
                        }
                    }

                    if (preg_match('~(https?://[^/]+)(/.*)~', $videoUrl, $matches)) {
                        if (count($matches) > 1) {
                            $videoUrl = $matches[2];
                        }
                    }

                    // Assuming Movie is a class with all the mentioned setters
                    $state = $this->isSeries($title, $videoUrl) ? Movie::STATE_GROUP_OF_GROUP : Movie::STATE_ITEM;
                    // add isSeries(a) function

                    $movie = new Movie();  // Instance of your Movie class containing methods like setTitle, setVideoUrl, etc.
                    $movie->setTitle($title);
                    $movie->setCardImage($image);
                    $movie->setBackgroundImage($image);
                    $movie->setState($state);

                    $source = new Source();
                    $source->setServer($this->serverConfig);
                    $source->setVidoUrl($videoUrl);
                    $source->setState($state);

                    $movie->addSource($source);

                    $movieList[] = $movie;
                }
            });
        }
        // Once all is done just return the $movieList
        return $movieList;
    }

    public function isSeries($title, $videoUrl): bool
    {
        $u = $videoUrl;
        $n = $title;

        // $this->logger->debug("isSeries: title: $n , url= $u");

        return str_contains($n, "انمي") || str_contains($n, "برنامج") || str_contains($n, "مسلسل") || str_contains($u, "series");
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    private function init()
    {
        //todo: set cookie, headers and other stuff
        //$this->serverConfig->setName('Akwam'); // fetch id from serverConfig
    }

    public function getServerConfig(): Server
    {
        return $this->serverConfig;
    }

    public function setServerConfig(Server $serverConfig): void
    {
        $this->serverConfig = $serverConfig;
    }


    public function fetchMovie(Movie $movie): array
    {
        // TODO: Implement fetchMovie() method.
        dump('mycima fetchMovie: '. $movie->getState());
        switch ($movie->getState()) {
            case Movie::STATE_GROUP_OF_GROUP:
                return $this->fetchGroupOfGroup($movie);
            case Movie::STATE_GROUP:
                return $this->fetchGroup($movie);
            case Movie::STATE_ITEM:
                return $this->fetchItem($movie);
        }
        return [];
    }

    public function fetchSource(Source $source): array
    {
        $movieUrl = $source->getServer()->getWebAddress() . $source->getVidoUrl();
        return [];
        }

        public function fetchItem(Movie $movie): array
    {
        $source = $movie->getSources()->get(0);
        $movieUrl = $source->getServer()->getWebAddress() . $source->getVidoUrl();
        dump('fetchItem ' . $movieUrl);
        try {
            $response = $this->httpClient->request('GET', $movieUrl, [
            ]);

            $content = $response->getContent();
            $crawler = new Crawler($content);

            $descElem = $crawler->filter('.StoryMovieContent')->first();
            $desc = "";
            if ($descElem->count() > 0) {
                $desc = $descElem->text();
                $movie->setDescription($desc);
            }

            $videoUrlTested = false;

            //$uls = $crawler->filter('.List--Download--Wecima--Single');
            $uls = $crawler->filterXPath('//ul[contains(@class, "List--Download--Wecima--Single")]');
            $movieList = [];
            $uls->each(function (Crawler $ul) use (&$movieList, &$movie, &$videoUrlTested) {
                $lis = $ul->filter('li');
                dump('$lis' . $lis->count());
                $lis->each(function (Crawler $li) use (&$movieList, &$movie, &$videoUrlTested) {
                    $videoUrlElement = $li->filter('[href]')->first();
                    if ($videoUrlElement !== null) {
                        $videoUrl = $videoUrlElement->attr('href');
                        $title = $movie->getTitle();
                        $titleElement = $li->filter('resolution')->first();
                        if ($titleElement !== null) {
                            $title = $titleElement->text();
                        }

//                        if (!$this->isValidVideoUrl($videoUrl)){
//                                return;
//                        }

                        $state = Movie::STATE_VIDEO;
                        $video = new Movie();  // Instance of your Movie class containing methods like setTitle, setVideoUrl, etc.
                        $video->setTitle($title);
                        $video->setCardImage($movie->getCardImage());
                        $video->setBackgroundImage($movie->getCardImage());
                        $video->setState($state);
                        $video->setDescription($movie->getDescription());

                        $source = new Source();
                        $source->setServer($this->serverConfig);
                        $source->setVidoUrl($videoUrl);
                        $source->setState($state);

                        $video->addSource($source);

                        $movie->addSubMovie($video);

                        $movieList[] = $video;
                    }
                });
            });


//fetch watch servers
                $uls = $crawler->filter('.WatchServersList');
                $uls->each(function (Crawler $ul) use (&$movieList, &$movie) {
                    $lis = $ul->filter('[data-url]');
                    dump('$lis' . $lis->count());
                    $lis->each(function (Crawler $li) use (&$movieList, &$movie) {
                            $videoUrl = $li->attr('data-url');

                        $title = $movie->getTitle();
                            $titleElement = $li->filter('strong')->first();
                            if ($titleElement !== null) {
                                $title = $titleElement->text();
                            }
                            $state = Movie::STATE_RESOLUTION;
                            $video = new Movie();  // Instance of your Movie class containing methods like setTitle, setVideoUrl, etc.
                            $video->setTitle($title);
                            $video->setCardImage($movie->getCardImage());
                            $video->setBackgroundImage($movie->getCardImage());
                            $video->setState($state);
                            $video->setDescription($movie->getDescription());

                            $source = new Source();
                            $source->setServer($this->serverConfig);
                            $source->setVidoUrl($videoUrl);
                            $source->setState($state);

                            $video->addSource($source);

                            $movie->addSubMovie($video);

                            $movieList[] = $video;
                    });
                });
        } catch (\Exception $e) {
            echo "Our PHP adventure continues, but there might be some bumps in the road!\n";
        }
        return $movieList;
    }

    private function fetchGroupOfGroup(Movie $movie)
    {

        $movieList = [];
        $source = $movie->getSources()->get(0);
        $movieUrl = $source->getServer()->getWebAddress() . $source->getVidoUrl();
        dump('fetchGroupOfGroup', $movieUrl);

        try {
            $response = $this->httpClient->request('GET', $movieUrl, [
                'headers' => [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                    'User-Agent' => 'Mozilla/5.0 (Linux; Android 8.1.0; Android SDK built for x86 Build/OSM1.180201.031; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/69.0.3497.100 Mobile Safari/537.36',
                ]
            ]);

            $content = $response->getContent();
            $crawler = new Crawler($content);

            $descElem = $crawler->filter('.PostItemContent')->first();
            $desc = "";

            if (count($descElem) > 0) {
                $desc = $descElem->text();
                $movie->setDescription($desc);
            }

            $boxs = $crawler->filter('.List--Seasons--Episodes');

//            if (count($boxs) === 0) {
//                $boxs = $crawler->filter('.Episodes--Seasons--Episodes');
//            }

            if (count($boxs) === 0) {
                $movie->setState(Movie::STATE_GROUP);
                dump('boxes are 0 so fetchGroup');
                return $this->fetchGroup($movie);
                // You can call your fetchGroup function here.
            } else {
                $boxs->each(function (Crawler $box) use (&$movieList, $movie) {
                    $lis = $box->filter('a');
                    dump('$lis' . $lis->count());
                    $lis->each(function (Crawler $li) use (&$movieList, $movie) {
                        $title = $li->text();
                        $videoUrl = $li->attr('href');
                        $cardImageUrl = $movie->getCardImage();
                        $backgroundImageUrl = $cardImageUrl;
                        $state = Movie::STATE_GROUP;

                        if (preg_match('~(https?://[^/]+)(/.*)~', $videoUrl, $matches)) {
                            if (count($matches) > 1) {
                                $videoUrl = $matches[2];
                            }
                        }

                        $season = new Movie();  // Instance of your Movie class containing methods like setTitle, setVideoUrl, etc.
                        $season->setTitle($title);
                        $season->setCardImage($cardImageUrl);
                        $season->setBackgroundImage($backgroundImageUrl);
                        $season->setState($state);
                        $season->setDescription($movie->getDescription());

                        $source = new Source();
                        $source->setServer($this->serverConfig);
                        $source->setVidoUrl($videoUrl);
                        $source->setState($state);

                        $season->addSource($source);

                        $movie->addSubMovie($season);

                        $movieList[] = $season;

                    });
                });
            }
        } catch (\Exception $e) {
            dd('error:mycima fetchGroupOfGroup: ' . $e->getMessage());
        }
        return $movieList;
    }

    private function fetchGroup(Movie $movie)
    {

        $source = $movie->getSources()->get(0);
        $movieUrl = $source->getServer()->getWebAddress() . $source->getVidoUrl();
        dump('mycima fetchGroup:' . $movieUrl);
        try {
            $response = $this->httpClient->request('GET', $movieUrl, [
            ]);

            $content = $response->getContent();
            $crawler = new Crawler($content);

            $descElem = $crawler->filter('.PostItemContent')->first();
            $desc = "";

            if ($descElem->count() > 0) {
                $desc = $descElem->text();
                $movie->setDescription($desc);
            }

            $boxs = $crawler->filter('.Episodes--Seasons--Episodes');
            $movieList = [];
            $boxs->each(function (Crawler $box) use (&$movieList, $movie) {
                $lis = $box->filter('a');
                dump('$lis' . $lis->count());
                $lis->each(function (Crawler $li) use (&$movieList, $movie) {
                    $title = $li->text();
                    $videoUrl = $li->attr('href');

                    $state = Movie::STATE_ITEM;


                    if (preg_match('~(https?://[^/]+)(/.*)~', $videoUrl, $matches)) {
                        if (count($matches) > 1) {
                            $videoUrl = $matches[2];
                        }
                    }

                    $episode = new Movie();  // Instance of your Movie class containing methods like setTitle, setVideoUrl, etc.
                    $episode->setTitle($title);
                    $episode->setCardImage($movie->getCardImage());
                    $episode->setBackgroundImage($movie->getCardImage());
                    $episode->setState($state);
                    $episode->setDescription($movie->getDescription());

                    $source = new Source();
                    $source->setServer($this->serverConfig);
                    $source->setVidoUrl($videoUrl);
                    $source->setState($state);

                    $episode->addSource($source);

                    $movie->addSubMovie($episode);

                    $movieList[] = $episode;
                });
            });
        } catch (\Exception $e) {
            echo "Our PHP adventure continues, but there might be some bumps in the road!\n";
        }
        return $movieList;
    }

    private function isValidVideoUrl(?string $videoUrl)
    {
        try {
            $response = $this->httpClient->request('GET', $videoUrl, [
            ]);
            $invalidCond = str_contains($response->getContent(), 'File Not Found') || str_contains($response->getContent(), 'File is');
            return !$invalidCond;
        }catch (\Exception $e) {
            dump($e->getMessage());
            return false;
        }
    }

}