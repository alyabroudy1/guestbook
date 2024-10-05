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
use App\Repository\IptvChannelRepository;
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

class IptvServer extends AbstractServer
{

    private ?int $id = null;

    private function __construct(private HttpClientInterface $httpClient, private Server $serverConfig, private IptvChannelRepository $repo)
    {
        $this->init();
    }


    public function getConfig(): Server{
        return $this->serverConfig;
    }

    protected function getSearchUrlQuery(): string{
        return '';
    }

    public function search(string $query): array
    {
        return $this->repo->search($query);
    
    }

    protected function getMovieType(HtmlMovieDto $htmlMovieDto): ?MovieType
    {
        return MovieType::Film;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function generateSearchResult(ResponseInterface $response): array{
        return [];
    }

    public function isSeries($title, $videoUrl): bool
    {
       return false;
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
        return [];
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
