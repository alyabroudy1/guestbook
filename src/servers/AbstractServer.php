<?php

namespace App\servers;

use App\Entity\Dto\ChromeWebContentDTO;
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
use App\Service\CookieFinderService;
use PHPUnit\Exception;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
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
    public function search(string $query): array
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
        $season = new Season();
        $series = $htmlMovieDto->mainMovie;
        if ($series instanceof Series){
            $season->setSeries($series);
        }
        return $this->generateMovieFromHtml($season, $htmlMovieDto, LinkState::Fetch, true);
    }

    protected function generateEpisode(HtmlMovieDto $htmlMovieDto): Movie
    {
        $episode = new Episode();
        $season = $htmlMovieDto->mainMovie;
//        if (!$season instanceof Season){
//
//            if ($season !== null){
//                //season can be an instance of series
////                $newSeason = new Season();
////                $newSeason->setLink($season->getLink());
////                $newSeason->setTitle($season->getTitle());
////                $newSeason->setDescription($season->getDescription());
////                $newSeason->setRate($season->getRate());
////                $newSeason->setCardImage($season->getCardImage());
////                $newSeason->setBackgroundImage($season->getBackgroundImage());
////                $newSeason->setCreatedAt($season->getCreatedAt());
////                $newSeason->setUpdatedAt($season->getUpdatedAt());
////                $newSeason->setPlayedTime($season->getPlayedTime());
////                $newSeason->setSearchContext($season->getSearchContext());
////                $newSeason->setTotalTime($season->getTotalTime());
////                $season = $newSeason;
//            }
//        }
        $episode->setSeason($season);
        return $this->generateMovieFromHtml($episode, $htmlMovieDto, LinkState::Fetch, true);
    }


    protected function generateFilm(HtmlMovieDto $htmlMovieDto): ?Movie
    {
        return $this->generateMovieFromHtml(new Film(), $htmlMovieDto, LinkState::Fetch, true);
    }

    /**
     * @param Movie $movie
     * @return Link[]|null
     */
    public function fetchItem(Movie $movie, CookieFinderService $cookieFinderService): ?array
    {
        $url = $movie->getLink()->getUrl();
        if(!str_starts_with($url, 'http')){
            $url =  $this->getConfig()->getAuthority() .$movie->getLink()->getUrl();
        }
        // dd($url, $movie);
        try {
            $response = $this->getRequest($url);
            // dd($response);
        // dd('AbstractServer fetchItem: code: ' . $response->getStatusCode());
            $chromeWebContentDTO = new ChromeWebContentDTO($response->getContent(), $response->getHeaders());
            return $this->generateResolutions($chromeWebContentDTO, $movie);
        } catch (ClientException | TransportExceptionInterface  $e) {
//            dump('AbstractServer fetchItem: error: ' .$e->getMessage());
            $chromeWebContentDTO = $cookieFinderService->findCookies($movie->getLink()->getUrl(), $this->getConfig());
            return $this->generateResolutions($chromeWebContentDTO, $movie);
//            dd('fetchMovie: ', $chromeWebContentDTO);
        }
        return null;
    }

    public function fetchGroup(Movie $movie, CookieFinderService $cookieFinderService): ?array
    {
        $url = $this->getConfig()->getAuthority() . $movie->getLink()->getUrl();
        try {
            $response = $this->getRequest($url);
//            dump('AbstractServer fetchGroup: code: ' . $response->getStatusCode());
            $chromeWebContentDTO = new ChromeWebContentDTO($response->getContent(), $response->getHeaders());
            return $this->generateGroupMovies($chromeWebContentDTO, $movie);
        } catch (ClientException | TransportExceptionInterface  $e) {
//            dump('AbstractServer fetchGroup: error: ' .$e->getMessage());
            $chromeWebContentDTO = $cookieFinderService->findCookies($movie->getLink()->getUrl(), $this->getConfig());
            return $this->generateGroupMovies($chromeWebContentDTO, $movie);
//            dd('fetchMovie: ', $chromeWebContentDTO);
        }
        return null;
    }

    /**
     * @param Movie $movie
     * @return Link[]
     */
    protected abstract function generateResolutions(ChromeWebContentDTO $chromeWebContentDTO, Movie $movie): array;
    protected abstract function generateGroupMovies(ChromeWebContentDTO $chromeWebContentDTO, Movie $movie): array;

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
//        dump('getRequest headers:', $this->getConfig()->getHeaders());
        return $this->getHttpClient()->request('GET', $url, [
            'headers' => $this->getConfig()->getHeaders(),
            'max_redirects' => 10
        ]);
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
        $title = strtolower($htmlMovieDto->title);
        $movie->setTitle($title);
        $movie->setDescription($htmlMovieDto->description);
        $movie->setCardImage($htmlMovieDto->cardImage);
        $movie->setDescription($htmlMovieDto->description);

        $link = new Link();
        $link->setServer($this->getConfig());
        $link->setTitle($title);
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
        $replace = array('series', '-', '_', 'season', 'مسلسل', 'فيلم', 'فلم', 'موسم', 'مشاهدة', 'مترجم', 'انمي', 'أنمي', 'الموسم');
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

    private function generateSeriesFromSeasonDto(HtmlMovieDto $htmlMovieDto)
    {
        $seriesTitle = $this->generateSeriesTitle($htmlMovieDto->title);
        $seriesDTO = new HtmlMovieDto(
            $seriesTitle,
            $htmlMovieDto->videoUrl,
            $htmlMovieDto->description,
            $htmlMovieDto->cardImage,
            $htmlMovieDto->rate
        );
        return $this->generateSeries($seriesDTO);
    }

    private function generateSeasonFromEpisodeDto(HtmlMovieDto $htmlMovieDto)
    {
        $seasonTitle = $this->generateSeriesTitle($htmlMovieDto->title);
        $seasonDTO = new HtmlMovieDto(
            $seasonTitle,
            $htmlMovieDto->videoUrl,
            $htmlMovieDto->description,
            $htmlMovieDto->cardImage,
            $htmlMovieDto->rate
        );
        return $this->generateSeason($seasonDTO);
    }

    private function generateSeriesTitle(string $title)
    {
        $title = $this->generateCleanTitle($title);
        // remove one or two digit in the name like season 01
        $pattern = "/\d{1,2}/";
        return preg_replace($pattern, "", $title);
    }
}
