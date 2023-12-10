<?php

namespace App\servers;

use App\Entity\Movie;
use App\Entity\MovieSource;
use App\Entity\Server;
use App\Entity\Source;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AkwamTube implements MovieServerInterface
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


    public function search_test($query): array{

        $movieList = [];

        $mainMovie1 = new Movie();
        $mainMovie1->setTitle("ratched-series 2");
        $mainMovie1->setState(Movie::STATE_GROUP_OF_GROUP);

        $source1 = new Source();
        $source1->setState(Movie::STATE_GROUP_OF_GROUP);
        $source1->setServer($this->serverConfig);
        $source1->setVidoUrl("ratcheds series 2");
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
    public function search($query): array
    {
        $response = $this->httpClient->request('GET', 'https://i.akwam.tube/?s=sonic');
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
            if (preg_match('~https?://([^/]+)(/.*)~', $cardImage, $imageMatches)) {
                if (count($imageMatches) > 1){
                    if ($imageMatches[1] === $this->serverConfig->getWebAddress()){
                        $cardImage = $imageMatches[2];
                    }
                }
            }

            if (preg_match('~https?://([^/]+)(/.*)~', $videoUrl, $matches)) {
                if (count($matches) > 1) {
                    $videoUrl = $matches[2];
                }
            }
            $state = $this->isSeries($title, $videoUrl) ? Movie::STATE_GROUP_OF_GROUP : Movie::STATE_ITEM;

            $movie = new Movie();  // Instance of your Movie class containing methods like setTitle, setVideoUrl, etc.
            $movie->setTitle($title);
            $movie->setCardImage($cardImage);
            $movie->setBackgroundImage($cardImage);
            $movie->setState($state);

            $source = new Source();
            $source->setServer($this->serverConfig);
            $source->setVidoUrl($videoUrl);
            $source->setState($state);

            $movie->addSource($source);

            $movieList[] = $movie;
        });
        return $movieList;
        //return $this->search_test($query);
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
        dump('akwam fetchMovie:', $movie);
        $mainMovie1 = new Movie();
        $mainMovie1->setTitle("ratched sub1");
        $mainMovie1->setState(Movie::STATE_GROUP);

        $source1 = new Source();
        $source1->setState(Movie::STATE_GROUP);
        $source1->setServer($this->serverConfig);
        $source1->setVidoUrl("ratcheds1s1");
        $mainMovie1->addSource($source1);

        $movie->addSubMovie($mainMovie1);

        return [$mainMovie1];
        // TODO: Implement fetchMovie() method.
    }

    public function fetchSource(Source $source): array
    {
        // TODO: Implement fetchSource() method.
    }
}