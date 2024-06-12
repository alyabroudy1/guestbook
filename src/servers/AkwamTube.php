<?php

namespace App\servers;

use App\Entity\Dto\ChromeWebContentDTO;
use App\Entity\Dto\HtmlMovieDto;
use App\Entity\Link;
use App\Entity\LinkState;
use App\Entity\Movie;
use App\Entity\MovieType;
use App\Entity\Server;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AkwamTube extends AbstractServer
{

    private ?int $id = null;
    private static ?AkwamTube $instance = null;
    private function __construct(private HttpClientInterface $httpClient, private Server $serverConfig)
    {
        $this->init();
    }

    public static function getInstance(HttpClientInterface $httpClient, Server $serverConfig): static
    {
        if (!self::$instance){
            $instance = new self($httpClient, $serverConfig);
        }
       return $instance;
    }
    public function getConfig(): Server{
        return $this->serverConfig;
    }

//    public function search_test($query): array{
//
//        $movieList = [];
//
//        $mainMovie1 = new Movie();
//        $mainMovie1->setTitle("ratched-series 2");
//        $mainMovie1->setState(Movie::STATE_GROUP_OF_GROUP);
//
//        $source1 = new Source();
//        $source1->setState(Movie::STATE_GROUP_OF_GROUP);
//        $source1->setServer($this->serverConfig);
//        $source1->setVidoUrl("ratcheds series 2");
//        $mainMovie1->addSource($source1);
//
//
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
//   //     $movieList[] = $mainMovie2;
//
//        return $movieList;
//    }

    protected function getSearchUrlQuery(): string{
        return '/?s=';
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function generateSearchResult(ResponseInterface $response): array{
        $content = $response->getContent();

        // Assuming $content contains your HTML response
        $crawler = new Crawler($content);

// Find all the <li> elements with the class "video-grid"
        $videoGridElements = $crawler->filter('li.video-grid');

// Initialize an array to store the extracted data
        $movieList = [];

// Loop through each <li> element
        $videoGridElements->each(function (Crawler $videoGrid) use (&$movieList) {
            // Extract data from each <li> element
            $videoElement= $videoGrid->filter('div.thumb a');
            $videoUrl = $videoElement->attr('href');
            $cardImageElement = $videoGrid->filter('div.thumb a img');
            $cardImage = $cardImageElement->attr('data-src');
            $title = $cardImageElement->attr('alt');

            $cardImage = $this->generateValidLinkPath($cardImage);
            $videoUrl = $this->generateValidLinkPath($videoUrl);

            $htmlMovieDto = new HtmlMovieDto($title, $videoUrl, '', $cardImage, '', null);
            $movie = $this->generateSearchMovie($htmlMovieDto);

            $movieList[] = $movie;
        });
        return $movieList;
    }

    protected function getMovieType(HtmlMovieDto $htmlMovieDto): ?MovieType
    {
        if ($this->isSeries($htmlMovieDto->title, $htmlMovieDto->videoUrl)){
            return MovieType::Series;
        }
        return MovieType::Film;
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

    private function init()
    {
        //todo: set cookie, headers and other stuff
        //$this->serverConfig->setName('Akwam'); // fetch id from serverConfig
    }

//    public function fetchMovie(Movie $movie): Movie
//    {
//        dump('akwam fetchMovie:', $movie);
//                return match ($movie->getState()){
//            Movie::STATE_ITEM => $this->fetchItem($movie)
//        };
////        $mainMovie1 = new Movie();
////        $mainMovie1->setTitle("ratched sub1");
////        $mainMovie1->setState(Movie::STATE_GROUP);
////
////        $source1 = new Source();
////        $source1->setState(Movie::STATE_GROUP);
////        $source1->setServer($this->serverConfig);
////        $source1->setVidoUrl("ratcheds1s1");
////        $mainMovie1->addSource($source1);
////
////        $movie->addSubMovie($mainMovie1);
////
////        return [$mainMovie1];
//        // TODO: Implement fetchMovie() method.
//    }

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

        $videoGridElements = $crawler->filter('#play-video');
        $resolutions = [];

        if ($videoGridElements->count() > 0) {
            $referer = $this->getConfig()->getAuthority() . '/';
            $href = $videoGridElements->attr('href');
            if ($href !== null){
                $href = str_replace('rand&', '', $href);
                if (str_starts_with($href, '/')){
                    $href = 'https:' . $href;
                }
                $response2 = $this->httpClient->request('GET', $href, [
                    'headers' => [
                        'referer' => $this->getConfig()->getAuthority() . '/'
                    ]
                ]);
                $content2 = $response2->getContent();
                $crawler2 = new Crawler($content2);
                $elements = $crawler2->filter('.embeding ul li a')->each(function (Crawler $node) {
                    return [
                        'url' => $node->attr('data-src'),
                        'title' => $node->text()
                    ];
                });

                foreach ($elements as $linkArray){
//                    $referer = $this->extractDomainfromUrl($realUrl) . '/';

                    $finalUrl = $linkArray['url'] . Movie::URL_DELIMITER .'referer='.$referer;
                    $link = new Link();
                    $link->setUrl($finalUrl);
                    $link->setTitle($linkArray['title']);
                    $link->setServer($this->getConfig());
                    $link->setSplittable(false);
                    $link->setState(LinkState::Browse);
                    $link->setMovie($movie);
                    $resolutions[] = $link;
                }

            }
        }

        return $resolutions;
    }


    protected function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    protected function generateGroupMovies(ChromeWebContentDTO $chromeWebContentDTO, Movie $movie): array
    {
        return [];
    }
}