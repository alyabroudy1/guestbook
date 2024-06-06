<?php

namespace App\servers;

use App\Entity\Dto\HtmlMovieDto;
use App\Entity\Episode;
use App\Entity\Film;
use App\Entity\Link;
use App\Entity\LinkState;
use App\Entity\Movie;
use App\Entity\MovieType;
use App\Entity\Season;
use App\Entity\Series;
use App\Entity\Server;
use PHPUnit\Exception;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractServer
{

    /**
     * @param $query
     * @return Movie[]
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function search($query): array
    {
        $url = $this->getSearchUrl($query);
        $movies = [];
        try {
            $response = $this->getRequest($url);
            return $this->generateSearchResult($response);
        } catch (Exception $e) {
            dd($e->getMessage());
        }
        return $movies;
    }
    public abstract function getConfig(): Server;

    protected function generateSearchMovie(HtmlMovieDto $htmlMovieDto): ?Movie
    {
        $movieType = $this->getMovieType($htmlMovieDto);
        return match ($movieType) {
            MovieType::Series => $this->generateSeries($htmlMovieDto),
            MovieType::Season => $this->generateSeason($htmlMovieDto),
            MovieType::Episode => $this->generateEpisode($htmlMovieDto),
            MovieType::Film => $this->generateFilm($htmlMovieDto),
            default => null,
        };
    }

    protected function generateSeries(HtmlMovieDto $htmlMovieDto): ?Movie
    {
        return $this->generateMovieFromHtml(new Series(), $htmlMovieDto, LinkState::Fetch, true);
    }

    protected function generateSeason(HtmlMovieDto $htmlMovieDto): ?Movie
    {
        return $this->generateMovieFromHtml(new Season(), $htmlMovieDto, LinkState::Fetch, true);

    }

    protected function generateEpisode(HtmlMovieDto $htmlMovieDto): Movie
    {
        return $this->generateMovieFromHtml(new Film(), $htmlMovieDto, LinkState::Fetch, true);
    }


    protected function generateFilm(HtmlMovieDto $htmlMovieDto): ?Movie
    {
        return $this->generateMovieFromHtml(new Film(), $htmlMovieDto, LinkState::Fetch, true);
    }

    /**
     * @param Movie $movie
     * @return Link[]|null
     * @throws TransportExceptionInterface
     */
    public function fetchItem(Movie $movie): ?array
    {
        $url = $this->getConfig()->getAuthority() . $movie->getLink()->getUrl();
        try {
            $response = $this->getRequest($url);
            return $this->generateResolutions($response, $movie);
        } catch (Exception $e) {
            dd($e->getMessage());
        }
        return null;
    }

    /**
     * @param ResponseInterface $response
     * @param Movie $movie
     * @return Link[]
     */
    protected abstract function generateResolutions(ResponseInterface $response, Movie $movie): array;

    protected function getSearchUrl($query): string
    {
        if (str_starts_with($query, 'http')){
            return $query;
        }
        return $this->getConfig()->getAuthority() . $this->getSearchUrlQuery() . $query;
    }

    protected abstract function getSearchUrlQuery(): string;

    /**
     * @throws TransportExceptionInterface
     */
    protected function getRequest(string $url): ResponseInterface
    {
        return $this->getHttpClient()->request('GET', $url);
    }

    protected abstract function getHttpClient(): HttpClientInterface;

    /**
     * @param ResponseInterface $response
     * @return Movie[]
     */
    protected abstract function generateSearchResult(ResponseInterface $response): array;

    protected abstract function getMovieType(HtmlMovieDto $htmlMovieDto): ?MovieType;
    public abstract function isSeries($title, $videoUrl): bool;

    public function generateValidLinkPath(?string $url): string
    {
        $urlAuthority = $this->extractUrlAuthority($url);
        if ($urlAuthority && $urlAuthority['authority'] === $this->getConfig()->getAuthority()){
            return $urlAuthority['path'];
        }
        return $url;
    }

    public function extractUrlAuthority(?string $url): ?array
    {
        if (!$url){
            return null;
        }
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['host']) && $parsedUrl['scheme'] && isset($parsedUrl['path'])) {
            $authority = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
            $path = $parsedUrl['path'];
            return ['authority' => $authority, 'path' => $path];
        }
        return null;
    }

    protected function generateMovieFromHtml(Movie $movie, HtmlMovieDto $htmlMovieDto, LinkState $linkState, bool $splittableLink): Movie
    {
        $movie->setTitle($htmlMovieDto->title);
        $movie->setDescription($htmlMovieDto->description);
        $movie->setCardImage($htmlMovieDto->cardImage);
        $movie->setDescription($htmlMovieDto->description);

        $link = new Link();
        $link->setServer($this->getConfig());
        $link->setTitle($htmlMovieDto->title);
        $link->setState($linkState);
        $link->setUrl($htmlMovieDto->videoUrl);
        $link->setSplittable($splittableLink);
        $link->setMovie($movie);
        $link->setAuthority($this->getConfig()->getAuthority());

        $movie->setLink($link);
        return $movie;
    }

    protected function generateCleanTitle(?string $title)
    {
        // Array of words to be replaced
        $replace = array('series', '-', '_', 'season', 'مسلسل', 'فيلم', 'فلم', 'موسم', 'مشاهدة', 'مترجم', 'انمي', 'أنمي');
        $title = str_ireplace($replace, '', $title);

        // Replace 4 digit numbers
        //$title = preg_replace('/\b\d{4}\b/', '', $title);

        // Extra spaces should be removed from the title
        $title = trim($title);
        $title = strtolower($title);

        // Multiple spaces between words should be replaced with only one space
        $title = preg_replace('!\s+!', ' ', $title);

        return trim($title);
    }
}