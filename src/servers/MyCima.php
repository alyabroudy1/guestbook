<?php

namespace App\servers;

use App\Entity\Category;
use App\Entity\Dto\ChromeWebContentDTO;
use App\Entity\Dto\HtmlMovieDto;
use App\Entity\Link;
use App\Entity\LinkState;
use App\Entity\Movie;
use App\Entity\MovieType;
use App\Entity\Series;
use App\Entity\Server;

use PHPUnit\Exception;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MyCima extends AbstractServer
{

    private ?int $id = null;
    private static ?MyCima $instance = null;


    private function __construct(private HttpClientInterface $httpClient, private Server $serverConfig, private MovieMatcher $movieMatcher)
    {
        $this->init();
    }

    public static function getInstance(HttpClientInterface $httpClient, Server $serverConfig, MovieMatcher $movieMatcher): static
    {
        if (!self::$instance) {
            $instance = new self($httpClient, $serverConfig, $movieMatcher);
        }
        return $instance;
    }


    public function getConfig(): Server{
        return $this->serverConfig;
    }

    protected function getSearchUrlQuery(): string{
        return '/search/';
    }

//    public function search_test($query): array
//    {
//
//        $movieList = [];
//
//        $mainMovie1 = new Movie();
//        $mainMovie1->setTitle("ratched-");
//        $mainMovie1->setState(Movie::STATE_GROUP_OF_GROUP);
//
//        $source1 = new Source();
//        $source1->setState(Movie::STATE_GROUP_OF_GROUP);
//        $source1->setServer($this->serverConfig);
//        $source1->setVidoUrl("ratcheds1-season");
//        $mainMovie1->addSource($source1);
//
//
////        $mainMovie1 = new Movie();
////        $mainMovie1->setTitle("ratched sub1");
////        $mainMovie1->setState(Movie::STATE_GROUP);
////
////        $source1 = new Source();
////        $source1->setState(Movie::STATE_ITEM);
////        $source1->setServer($this->serverConfig);
////        $source1->setVidoUrl("ratcheds1s1");
////        $mainMovie1->addSource($source1);
//
////        $mainMovie2 = new Movie();
////        $mainMovie2->setTitle("ss ratched s2");
////
////        $source2 = new Source();
////        $source2->setState(Movie::STATE_ITEM);
////        $source2->setServer($this->serverConfig);
////        $source2->setVidoUrl("ratcheds2");
////        $mainMovie2->addSource($source2);
//
//        $movieList[] = $mainMovie1;
//        //     $movieList[] = $mainMovie2;
//
//        return $movieList;
//    }


    public function search(string $query): array
    {
        $movieList = [];
        // Log the query
        // You may need Monolog or Symfony's built-in logger.
        // $this->logger->info("search: " . $query);

        $url = $this->getSearchUrl($query);
        $multiSearch = false;
        if (!str_contains($query, "http")) {
            $multiSearch = true;
            $searchContext = $query;
        }

        $movies = [];
        try {
            $response = $this->getRequest($url);

//            $movieList = $this->getMovieList($query, $searchContext);
            $movieList = $this->generateSearchResult($response);

            if (!$multiSearch){
                return $movieList;
            }
            $url2 = $url . '/list/';
            $response2 = $this->getRequest($url2);
            $movieList2 = $this->generateSearchResult($response2);

        } catch (Exception $e) {
            dd($e->getMessage());
        }


//        $multiSearch = false;
//        if (!str_contains($query, "http")) {
////            if (isset($referer) && !empty($referer)) {
////                $query = rtrim($referer, '/') . "/search/" . $query;
////            } else {
////                $query = $this->websiteUrl . "/search/" . $query;
////            }
//            $query = $this->serverConfig->getAuthority() . '/search/' . $query;
//            $multiSearch = true;
//            $searchContext = $query;
//        }else{
//            $searchContext = str_replace($this->serverConfig->getAuthority(), '', $query);
//        }



        // Once all is done just return the $movieList
        return array_merge($movieList, $movieList2);
    }

    protected function getMovieType(HtmlMovieDto $htmlMovieDto): ?MovieType
    {
        $n = $htmlMovieDto->title;
        if ($this->isSeries($htmlMovieDto->title, $htmlMovieDto->videoUrl)){
            $seasonCond = str_contains($n, 'موسم') || str_contains($n, 'الموسم');
            return $seasonCond ? MovieType::Season : MovieType::Series;
        }
        $episodeCond = str_contains($n, 'حلقة') || str_contains($n, 'الحلقة');
        return $episodeCond ? MovieType::Episode : MovieType::Film;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function generateSearchResult(ResponseInterface $response): array{
        $movieList = [];

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return $movieList;
        }
        $content = $response->getContent();

        // Assuming $content contains your HTML response
        $crawler = new Crawler($content);

        $crawler->filter('.GridItem')->each(function (Crawler $node) use (&$movieList) {
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


//                if (preg_match('~(https?://[^/]+)(/.*)~', $image, $imageMatches)) {
//                    if (count($imageMatches) > 1) {
//                        if ($imageMatches[1] === $this->serverConfig->getAuthority()) {
//                            $image = $imageMatches[2];
//                        }
//                    }
//                }
                $image = $this->generateValidLinkPath($image);
                $videoUrl = $this->generateValidLinkPath($videoUrl);

//                if (preg_match('~(https?://[^/]+)(/.*)~', $videoUrl, $matches)) {
//                    if (count($matches) > 1) {
//                        $videoUrl = $matches[2];
//                    }
//                }

                $htmlMovieDto = new HtmlMovieDto($title, $videoUrl, '', $image, '', null);
                $movie = $this->generateSearchMovie($htmlMovieDto);

//                // Assuming Movie is a class with all the mentioned setters
//                $state = $this->isSeries($title, $videoUrl) ? Movie::STATE_GROUP_OF_GROUP : Movie::STATE_ITEM;
//                // add isSeries(a) function
//
//                $movie = new Movie();  // Instance of your Movie class containing methods like setTitle, setVideoUrl, etc.
//                $movie->setTitle($title);
//                $movie->setCardImage($image);
//                $movie->setBackgroundImage($image);
//                $movie->setState($state);
//                $movie->setSearchContext($searchContext);
////                    $category = new Category();
////                    $category->setName('general');
////                    $movie->addCategory($category);
//
//                $category2 = new Category();
//                $category2->setName('Mycima');
//                $movie->addCategory($category2);
//
//                $source = new Source();
//                $source->setServer($this->serverConfig);
//                $source->setVidoUrl($videoUrl);
//                $source->setState($state);
//                $source->setTitle($title);
//
//                $movie->addSource($source);

                $movieList[] = $movie;
            }
        });

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

    public function fetchSource(Source $source): Movie
    {
        // $movieUrl = $source->getServer()->getWebAddress() . $source->getVidoUrl();
        return match ($source->getState()) {
            Movie::STATE_GROUP_OF_GROUP => $this->fetchGroupOfGroup($source),
            Movie::STATE_ITEM => $this->fetchItem($source)
        };
    }

    /**
     * @param ResponseInterface $response
     * @param Movie $movie
     * @return Link[]
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function generateResolutions(ChromeWebContentDTO $chromeWebContentDTO, Movie $movie): array{
        // Assuming $content contains your HTML response
        $content = $chromeWebContentDTO->content;
        $crawler = new Crawler($content);
//        $realUrl = $response->getInfo('url');
        //todo find a way to get the real url
        $realUrl = $movie->getLink()->getAuthority();


        $descElem = $crawler->filter('.StoryMovieContent')->first();
        $desc = "";
        if ($descElem->count() > 0) {
            $desc = $descElem->text();
        }

        $resolutions = [];

        $videoUrlTested = false;

        //$uls = $crawler->filter('.List--Download--Wecima--Single');
        $uls = $crawler->filterXPath('//ul[contains(@class, "List--Download--Wecima--Single")]');
//        dump('generateResolutions: $uls: ', $uls->count());
        $uls->each(function (Crawler $ul) use (&$resolutions, $realUrl, &$movie, &$videoUrlTested) {
            $lis = $ul->filter('li');

            $lis->each(function (Crawler $li) use (&$resolutions, $realUrl, &$movie, &$videoUrlTested) {
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

                    $finalUrl = $videoUrl . Movie::URL_DELIMITER .'referer='.$realUrl;
                    $link = new Link();
                    $link->setUrl($finalUrl);
                    $link->setTitle($title);
                    $link->setServer($this->getConfig());
                    $link->setSplittable(false);
                    $link->setState(LinkState::Video);
                    $link->setMovie($movie);
                    $resolutions[] = $link;

                }
            });
        });

//        dump('generateResolutions: download '. $uls->count());

        $uls = $crawler->filter('.WatchServersList');
//        dump('generateResolutions: watch '. $uls->count());
//        dd('generateResolutions: contents '. $content);
        $uls->each(function (Crawler $ul) use (&$resolutions, $realUrl, &$movie) {
            $lis = $ul->filter('[data-url]');
            $lis->each(function (Crawler $li) use (&$resolutions, $realUrl, &$movie) {
                $videoUrl = $li->attr('data-url');

                $title = $movie->getTitle();
                $titleElement = $li->filter('strong')->first();
                if ($titleElement !== null) {
                    $title = $titleElement->text();
                }
                $finalUrl = $videoUrl . Movie::URL_DELIMITER .'referer='.$realUrl;
                $link = new Link();
                $link->setUrl($finalUrl);
                $link->setTitle($title);
                $link->setServer($this->getConfig());
                $link->setSplittable(false);
                $link->setState(LinkState::Browse);
                $link->setMovie($movie);
                $resolutions[] = $link;
            });
        });
//        dump('generateResolutions: $resolutions: ', $resolutions);
        return $resolutions;
    }


//    public function fetchItem(Movie $movie): array
//    {
//        $movieUrl = $source->getServer()->getAuthority() . $source->getVidoUrl();
//        $mainMovie = $source->getMovie();
//        $movie = $mainMovie->cloneMovie();
//        $movie->setMainMovie($mainMovie);
//        try {
//            $response = $this->httpClient->request('GET', $movieUrl, [
//            ]);
//            $realUrl = $response->getInfo('url');
//
//            $content = $response->getContent();
//            $crawler = new Crawler($content);
//
//            $descElem = $crawler->filter('.StoryMovieContent')->first();
//            $desc = "";
//            if ($descElem->count() > 0) {
//                $desc = $descElem->text();
//                $movie->setDescription($desc);
//            }
//
//            $videoUrlTested = false;
//
//            //$uls = $crawler->filter('.List--Download--Wecima--Single');
//            $uls = $crawler->filterXPath('//ul[contains(@class, "List--Download--Wecima--Single")]');
//            $uls->each(function (Crawler $ul) use ($realUrl, &$movie, &$videoUrlTested) {
//                $lis = $ul->filter('li');
//
//                $lis->each(function (Crawler $li) use ($realUrl, &$movie, &$videoUrlTested) {
//                    $videoUrlElement = $li->filter('[href]')->first();
//                    if ($videoUrlElement !== null) {
//                        $videoUrl = $videoUrlElement->attr('href');
//                        $title = $movie->getTitle();
//                        $titleElement = $li->filter('resolution')->first();
//                        if ($titleElement !== null) {
//                            $title = $titleElement->text();
//                        }
//
////                        if (!$this->isValidVideoUrl($videoUrl)){
////                                return;
////                        }
//
//                        $state = Movie::STATE_VIDEO;
//
//                        $source = new Source();
//                        $source->setTitle($title);
//                        $source->setServer($this->serverConfig);
//                        $source->setVidoUrl($videoUrl);
//                        $source->setState($state);
//
//                        $movie->addSource($source);
//
//                    }
//                });
//            });
//
//            $uls = $crawler->filter('.WatchServersList');
//            $uls->each(function (Crawler $ul) use ($realUrl, &$movie) {
//                $lis = $ul->filter('[data-url]');
//                $lis->each(function (Crawler $li) use ($realUrl, &$movie) {
//                    $videoUrl = $li->attr('data-url');
//
//                    $title = $movie->getTitle();
//                    $titleElement = $li->filter('strong')->first();
//                    if ($titleElement !== null) {
//                        $title = $titleElement->text();
//                    }
//                    $state = Movie::STATE_BROWSE;
//
//                    $source = new Source();
//                    $source->setServer($this->serverConfig);
//
//                    $referer = $this->extractDomainfromUrl($realUrl) . '/';
//                    $finalUrl = $videoUrl . Movie::URL_DELIMITER .'referer='.$referer;
//
//                    $source->setVidoUrl($finalUrl);
//                    $source->setTitle($title);
//                    $source->setState($state);
//
//                    $movie->addSource($source);
//                });
//            });
//        } catch (\Exception $e) {
//            echo "Our PHP adventure continues, but there might be some bumps in the road!\n";
//        }
//        //todo update server webaddress in db from $realUrl
//        return $movie;
//        return [];
//    }

    function extractDomainfromUrl($videoUrl) {
        if (preg_match('~(https?://[^/]+)(/.*)~', $videoUrl, $matches)) {
            if (count($matches) > 1) {
                $videoUrl = $matches[1];
            }
        }
        return $videoUrl;
    }

//    public function fetchGroupOfGroup(Source $source): Movie
//    {
//        $movieUrl = $source->getServer()->getAuthority() . $source->getVidoUrl();
//        $mainMovie = $source->getMovie();
//
//        try {
//            $response = $this->httpClient->request('GET', $movieUrl, [
//                'headers' => [
//                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
//                    'User-Agent' => 'Mozilla/5.0 (Linux; Android 8.1.0; Android SDK built for x86 Build/OSM1.180201.031; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/69.0.3497.100 Mobile Safari/537.36',
//                ]
//            ]);
//
//            $content = $response->getContent();
//            $crawler = new Crawler($content);
//
//            $descElem = $crawler->filter('.PostItemContent')->first();
//
//            if (count($descElem) > 0) {
//                $desc = $descElem->text();
//                $mainMovie->setDescription($desc);
//            }
//
//            $boxs = $crawler->filter('.List--Seasons--Episodes');
//
////            if (count($boxs) === 0) {
////                $boxs = $crawler->filter('.Episodes--Seasons--Episodes');
////            }
//
//
//            if (count($boxs) === 0) {
//                $source->setState(Movie::STATE_GROUP);
//                return $this->fetchGroup($source);
//                // You can call your fetchGroup function here.
//            } else {
//                $boxs->each(function (Crawler $box) use (&$mainMovie) {
//                    $lis = $box->filter('a');
//                    $lis->each(function (Crawler $li) use (&$mainMovie) {
//                        $title = $li->text();
//                        $videoUrl = $li->attr('href');
//
//                        $state = Movie::STATE_GROUP;
//
//                        if (preg_match('~(https?://[^/]+)(/.*)~', $videoUrl, $matches)) {
//                            if (count($matches) > 1) {
//                                $videoUrl = $matches[2];
//                            }
//                        }
//
//                        $season = $mainMovie->cloneMovie();
//                        $season->setMainMovie($mainMovie);
//                        $season->setTitle($title);
//                        $season->setState($state);
//
//                        $source = new Source();
//                        $source->setServer($this->serverConfig);
//                        $source->setVidoUrl($videoUrl);
//                        $source->setState($state);
//                        $source->setTitle($title);
//
//                        $this->movieMatcher->matchMovie($mainMovie, $season, $source);
//                    });
//                });
//            }
//        } catch (\Exception $e) {
//            dd('error:mycima fetchGroupOfGroup: ' . $e->getMessage());
//        }
//        return $mainMovie;
//    }

//    public function fetchGroup(Source $source)
//    {
//        $movieUrl = $source->getServer()->getAuthority() . $source->getVidoUrl();
//        $mainMovie = $source->getMovie();
//
//        try {
//            $response = $this->httpClient->request('GET', $movieUrl, [
//            ]);
//
//            $content = $response->getContent();
//            $crawler = new Crawler($content);
//
//            $descElem = $crawler->filter('.PostItemContent')->first();
//
//            if ($descElem->count() > 0) {
//                $desc = $descElem->text();
//                $mainMovie->setDescription($desc);
//            }
//
//            $boxs = $crawler->filter('.Episodes--Seasons--Episodes');
//            $boxs->each(function (Crawler $box) use (&$mainMovie, $crawler) {
//                $lis = $box->filter('a');
//
//                $lis->each(function (Crawler $li) use (&$mainMovie) {
//                    $title = $li->text();
//                    $videoUrl = $li->attr('href');
//
//                    $state = Movie::STATE_ITEM;
//
//
//                    if (preg_match('~(https?://[^/]+)(/.*)~', $videoUrl, $matches)) {
//                        if (count($matches) > 1) {
//                            $videoUrl = $matches[2];
//                        }
//                    }
//
//                    $episode = $mainMovie->cloneMovie();
//                    $episode->setMainMovie($mainMovie);
//                    $episode->setTitle($title);
//                    $episode->setState($state);
//
//                    $source = new Source();
//                    $source->setServer($this->serverConfig);
//                    $source->setVidoUrl($videoUrl);
//                    $source->setState($state);
//                    $source->setTitle($title);
//
//                    $this->movieMatcher->matchMovie($mainMovie, $episode, $source);
//                });
////                $this->fetchHiddenEpisodes($crawler, $mainMovie);
//            });
//        } catch (\Exception $e) {
//            echo "Our PHP adventure continues, but there might be some bumps in the road!\n";
//        }
//
//        return $mainMovie;
//    }

    /**
     * @param \Symfony\Contracts\HttpClient\ResponseInterface $response
     * @param array $movieList
     * @param string $query
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
//    public function getMovieList(string $query, string $searchContext): array
//    {
//        $movieList = [];
//        $response = $this->httpClient->request('GET', $query);
//        if ($response->getStatusCode() === Response::HTTP_OK) {
//
//            $html = $response->getContent();
//            $crawler = new Crawler($html);
//            $movieList = [];
//
//            // The beauty of Symfony's DomCrawler component is that it can work as a jQuery-like syntax
//            $crawler->filter('.GridItem')->each(function (Crawler $node) use (&$movieList, $query, $searchContext) {
//                $linkElem = $node->filter('[href]')->first();
//                if ($linkElem->count() > 0) {
//                    $videoUrl = $linkElem->attr('href');
//
//                    $title = $node->filter('[title]')->first()->attr('title');
//                    $imageElem = $node->filter('[style]')->first();
//                    $image = $imageElem->count() > 0 ? $imageElem->attr('style') : "https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png";
//
//                    if (!str_contains($image, "http")) {
//                        $image2Elem = $node->filter('[data-lazy-style]')->first();
//                        $image = $image2Elem->count() > 0 ? $image2Elem->attr('data-lazy-style') : $image;
//                        if ($image === "") {
//                            $image3Elem = $node->filter('.BG--GridItem')->first();
//                            $image = $image3Elem->count() > 0 ? $image3Elem->attr('data-owl-img') : $image;
//                        }
//                    }
//
//                    if (str_contains($image, "(") && str_contains($image, ")")) {
//                        $image = substr($image, strpos($image, '(') + 1, strpos($image, ')') - strpos($image, '(') - 1);
//                    }
//
//
//                    if (preg_match('~(https?://[^/]+)(/.*)~', $image, $imageMatches)) {
//                        if (count($imageMatches) > 1) {
//                            if ($imageMatches[1] === $this->serverConfig->getAuthority()) {
//                                $image = $imageMatches[2];
//                            }
//                        }
//                    }
//
//                    if (preg_match('~(https?://[^/]+)(/.*)~', $videoUrl, $matches)) {
//                        if (count($matches) > 1) {
//                            $videoUrl = $matches[2];
//                        }
//                    }
//
//                    // Assuming Movie is a class with all the mentioned setters
//                    $state = $this->isSeries($title, $videoUrl) ? Movie::STATE_GROUP_OF_GROUP : Movie::STATE_ITEM;
//                    // add isSeries(a) function
//
//                    $movie = new Movie();  // Instance of your Movie class containing methods like setTitle, setVideoUrl, etc.
//                    $movie->setTitle($title);
//                    $movie->setCardImage($image);
//                    $movie->setBackgroundImage($image);
//                    $movie->setState($state);
//                    $movie->setSearchContext($searchContext);
////                    $category = new Category();
////                    $category->setName('general');
////                    $movie->addCategory($category);
//
//                    $category2 = new Category();
//                    $category2->setName('Mycima');
//                    $movie->addCategory($category2);
//
//                    $source = new Source();
//                    $source->setServer($this->serverConfig);
//                    $source->setVidoUrl($videoUrl);
//                    $source->setState($state);
//                    $source->setTitle($title);
//
//                    $movie->addSource($source);
//
//                    $movieList[] = $movie;
//                }
//            });
//        }
//        return $movieList;
//    }

    private function isValidVideoUrl(?string $videoUrl)
    {
        try {
            $response = $this->httpClient->request('GET', $videoUrl, [
            ]);
            $invalidCond = str_contains($response->getContent(), 'File Not Found') || str_contains($response->getContent(), 'File is');
            return !$invalidCond;
        } catch (\Exception $e) {
            dump($e->getMessage());
            return false;
        }
    }

//    private function fetchHiddenEpisodes(Crawler $crawler, Movie $movie)
//    {
//        //MoreEpisodes--Button hoverable activable
//        $moreEp = $crawler->filter('.MoreEpisodes--Button');
//        if (count($moreEp) > 0) {
//            $name = $moreEp->attr('data-term');
//            if ($name === null) {
//                $name = $movie->getTitle(); // Assuming $movie is an object with a getTitle method
//            }
//
//            $domain = extractDomain($movie->getVideoUrl()); // Assuming extractDomain is a function you have defined
//            $moreUrl = $domain . "/AjaxCenter/MoreEpisodes/" . $name . "/" . count($lis) . "/"; // Assuming $lis is an array
//
//            // Use GuzzleHttp\Client to make the HTTP request
//            $client = new \GuzzleHttp\Client();
//            $res = $client->request('GET', $moreUrl, [
//                'headers' => [
//                    'Content-Type' => '*/*',
//                    'Accept' => '*/*'
//                ],
//                'allow_redirects' => true,
//                'http_errors' => false,
//                'timeout' => 9.0
//            ]);
//
//            $doc2 = new Crawler((string) $res->getBody());
//
//            $links = $doc2->filter('[href]');
//
//            foreach ($links as $link) {
//                $titleElm = $crawler->filter('episodetitle')->first();
//                $title = $movie->getTitle();
//                if ($titleElm !== null) {
//                    $title = str_replace(["<\\/episodeTitle><\\/episodeArea><\\/a>", "\\", "\""], "", $titleElm->text());
//
//                    $linkUrl = $link->attr('href');
//
//                    $a = new Movie(); // Assuming Movie is a class you have defined
//                    $a->setStudio(Movie::SERVER_MyCima);
//
//                    $episode = clone $movie;
//                    $episode->setTitle($title);
//                    $episode->setVideoUrl($linkUrl);
//                    $episode->setState(Movie::ITEM_STATE);
//
//                    if ($movie->getSubList() === null) {
//                        $movie->setSubList([]);
//                    }
//
//                    $movie->addSubList($episode); // Assuming addSubList is a method you have defined in the Movie class
//
//                    $episode = $mainMovie->cloneMovie();
//                    $episode->setMainMovie($mainMovie);
//                    $episode->setTitle($title);
//                    $episode->setState($state);
//
//                    $source = new Source();
//                    $source->setServer($this->serverConfig);
//                    $source->setVidoUrl($videoUrl);
//                    $source->setState($state);
//                    $source->setTitle($title);
//
//                    $this->movieMatcher->matchMovie($mainMovie, $episode, $source);
//                }
//            }
//        }
//    }


    protected function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    protected function generateGroupMovies(ChromeWebContentDTO $chromeWebContentDTO, Movie $movie): array
    {
        $movies = [];
        try {
            $content = $chromeWebContentDTO->content;
            $crawler = new Crawler($content);
//        $realUrl = $response->getInfo('url');
            //todo find a way to get the real url
            $realUrl = $movie->getLink()->getAuthority();

            $descElem = $crawler->filter('.PostItemContent')->first();
            $desc = "";
            if ($descElem->count() > 0) {
                $desc = $descElem->text();
            }

//            $boxs = $crawler->filter('.Episodes--Seasons--Episodes');
            $boxs = null;
            if ($movie instanceof Series){
                $boxs = $crawler->filter('.List--Seasons--Episodes');
            }

            $resultMovieType = MovieType::Season;

            if ($boxs?->count() === 0) {
                $boxs = $crawler->filter('[class*="Episodes--Seasons--Episodes"]');
                $resultMovieType = MovieType::Episode;
            }

            $boxs->each(function (Crawler $box) use (&$movies, $crawler, $movie, $desc, $resultMovieType) {
                $lis = $box->filter('a');

                $lis->each(function (Crawler $li) use (&$movies, $movie, $desc, $resultMovieType) {
                    $title = $li->text();
                    $videoUrl = $li->attr('href');

//                    if (preg_match('~(https?://[^/]+)(/.*)~', $videoUrl, $matches)) {
//                        if (count($matches) > 1) {
//                            $videoUrl = $matches[2];
//                        }
//                    }
                    $videoUrl = $this->generateValidLinkPath($videoUrl);

                    $htmlMovieDto = new HtmlMovieDto($title, $videoUrl, $desc, $movie->getCardImage(), '', $movie);
                    $subMovie = null;
                    if ($resultMovieType === MovieType::Episode){
                        $subMovie = $this->generateEpisode($htmlMovieDto);
                    }else{
                        $subMovie = $this->generateSeason($htmlMovieDto);
                    }

                    $movies[] = $subMovie;
//                    $this->movieMatcher->matchMovie($mainMovie, $episode, $source);
                });
//                $this->fetchHiddenEpisodes($crawler, $mainMovie);
            });
        } catch (\Exception $e) {
            echo "Our PHP adventure continues, but there might be some bumps in the road!\n";
        }
        return $movies;
    }
}
