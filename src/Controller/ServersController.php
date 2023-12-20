<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Server;
use App\Entity\Source;
use App\servers\AkwamTube;
use App\servers\MovieServerInterface;
use App\servers\MyCima;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use http\Header\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ServersController extends AbstractController
{
    private $servers;

    public function __construct(private HttpClientInterface $httpClient, private EntityManagerInterface $entityManager)
    {
        $this->initializeServers();
    }

    public function search($query): array
    {
        //get search result from servers
        //todo: try to get the result from database first and if theres no result then fetch it from the net
        //todo: find a way to update database movies something like fetched the last added movies once a day
       //todo:optimize search
       // $movieList = $this->getMovieListFromDB($query);
        $movieList = [];
        if (empty($movieList)) {
            //search all server and add result to db
            $this->searchAllServers($query);
            //fetch result again from db
            $movieList = $this->getMovieListFromDB($query);
        }

        return $movieList;
    }

//    public function fetchMovie(Movie $movie): array
//    {
////        $jsonData = match ($movie->getState()){
////            Movie::STATE_ITEM => $this->serversController->fetchMovie($movie)
////        };
////
//
//        $movieList = $this->entityManager->getRepository(Movie::class)->findSubMovies($movie);
//        if (empty($movieList) && $movie->getSources()->count() > 0) {
//            $source = $movie->getSources()->get(0);
//            /** @var MovieServerInterface $server */
//            $server = $this->servers[$source->getServer()->getName()];
//            //fetch result again from db
//            $movieList = $server->fetchMovie($movie);
//            dd('fetchMovie',$movieList);
//            //save to data base movies with only groupOfGroup, Group, Item
//            if ($movie->getState() < Movie::STATE_RESOLUTION){
//                $this->matchMovieList($movieList, $server);
//            }
//        }
//        return $movieList;
//    }

    public function fetchSource(Source $source): Movie
    {
        /** @var MovieServerInterface $server */
        $server = $this->servers[$source->getServer()->getName()];

        if ($source->getState() === Movie::STATE_ITEM ){
            return $server->fetchItem($source);
        }

        $movie = $source->getMovie();
        //todo check if works for android like that
        if ($movie->getSubMovies()->count() > 0){
            return $movie;
        }
        /** @var Movie $result */
        $result = match ($source->getState()) {
            Movie::STATE_GROUP_OF_GROUP => $server->fetchGroupOfGroup($source),
            Movie::STATE_GROUP => $server->fetchGroup($source),
        };

        $this->matchMovieList($result->getSubMovies()->toArray(), $server);

        return $result;
    }

    private function initializeServers()
    {
        //akwamTube
        //fetch new Server() from db
        //todo: suggest refactoring
        $akwamTubeServerConfig = $this->entityManager->getRepository(Server::class)->findOneBy(['name' => Server::SERVER_AKWAM]);
        if (empty($akwamTubeServerConfig)) {
            $akwamTubeServerConfig = new Server();
            $akwamTubeServerConfig->setName(Server::SERVER_AKWAM);
            $akwamTubeServerConfig->setWebAddress('https://i.akwam.tube');
            $akwamTubeServerConfig->setActive(true);
            //only the first time if server is not saved to db
            $this->entityManager->persist($akwamTubeServerConfig);
            $this->entityManager->flush();
        }
        $this->servers[Server::SERVER_AKWAM] = AkwamTube::getInstance($this->httpClient, $akwamTubeServerConfig);

        //myCima
        //fetch new Server() from db
        //todo: suggest refactoring
        $myCimaServerConfig = $this->entityManager->getRepository(Server::class)->findOneBy(['name' => Server::SERVER_MYCIMA]);

        if (!$myCimaServerConfig) {
            $myCimaServerConfig = new Server();
            $myCimaServerConfig->setName(Server::SERVER_MYCIMA);
            $myCimaServerConfig->setWebAddress('https://wemycema.shop');
            $myCimaServerConfig->setDefaultWebAddress('https://mycima.io');
            //only the first time if server is not saved to db
            $myCimaServerConfig->setActive(true);
            //only the first time if server is not saved to db
            $this->entityManager->persist($myCimaServerConfig);
            $this->entityManager->flush();
        }
        $this->servers[Server::SERVER_MYCIMA] = MyCima::getInstance($this->httpClient, $myCimaServerConfig);

        //todo: other server ...
    }

    private function getMovieListFromDB($query)
    {
        return $this->entityManager->getRepository(Movie::class)->findMainMoviesByTitleLoose($query);
    }

    private function searchAllServers($query)
    {
        //todo: doing it using thread or workers for performance
        /** @var MovieServerInterface $server */
        foreach ($this->servers as $server) {
            $result = $server->search($query);
            //todo: in new process match it with database and add it if missing
            $this->matchMovieList($result, $server);
        }
    }

    private function matchMovieList(array $movieList, $server)
    {
        //todo: in new process match it with database and add it if missing
        foreach ($movieList as $movie) {
            //todo: optimize
            if ($movie->getState() === Movie::STATE_VIDEO || $movie->getState() === Movie::STATE_RESOLUTION){
                continue;
            }

            $this->matchMovie($movie, $server);
        }
    }

    private function getExistingMovie(Movie $movie)
    {
//        $mainMovie = $movie->getMainMovie();
//        if(empty($mainMovie)){
//            $mainMovie = $movie;
//        }
//        else{
//            if(!empty($mainMovie->getMainMovie())){
//                $mainMovie = $mainMovie->getMainMovie();
//                if(!empty($mainMovie->getMainMovie())){
//                    $mainMovie = $mainMovie->getMainMovie();
//                }
//            }
//        }
        //todo: optimize
        $title = $this->getCleanTitle($movie->getTitle());
        $result =  $this->entityManager->getRepository(Movie::class)->findByTitleAndState($title, $movie->getState());
        //dump('getExistingMovie result', $result);
        $matchedMovie = null;

        if (count($result) > 0){
                /** @var Movie $matchedMovie */
         $matchedMovie = $this->detectCorrectMatch($result, $movie);
        }

        return $matchedMovie;
    }

    private function detectCorrectMatch(array $existingMovies, mixed $movie)
    {
        $title = $this->getCleanTitle($movie->getTitle());
        foreach ($existingMovies as $existingMovie){
            $existingTitle = $this->getCleanTitle($existingMovie->getTitle());
           // dump($title. ', '.$existingTitle, $existingTitle === $title);
            if ($existingTitle === $title ) {
                return $existingMovie;
            }
        }
        return null;
    }

    private function matchMovie(Movie $movie, $server)
    {
       // dump('matchMovie', $movie->getTitle());
        $existingMovie = $this->getExistingMovie($movie);

        if ($existingMovie) {
            //todo: match other cases
            if ($movie->getState() === Movie::STATE_ITEM) {
                $itemSources = $existingMovie->getSources();
                foreach ($movie->getSources() as $source) {
                    //means it's they are both in the same level
                    //check if the source exist els add it
                    foreach ($itemSources as $mainSource) {
                        if ($mainSource->getVidoUrl() === $source->getVidoUrl()) {
                            //if exist continue
                            continue;
                        }
                        $source->setMovie($existingMovie);
                        $existingMovie->addSource($source);
                        $this->entityManager->persist($source);
                       // $this->entityManager->flush();
                    }
                }
            }
        } else {
            //if not exist add it
            //only if its an item movie
            //refactor movie to be ready to save
            // $this->refactorMovieForSave($movie, $server);
            //todo: optimize cleaning the title before save and before cleaning title to search in db
            $title = $this->getCleanTitle($movie->getTitle());
            $movie->setTitle($title);
            if ($movie->getSources()->first()) {
                $this->entityManager->persist($movie->getSources()->first());
            }

            $this->entityManager->persist($movie);
            $this->entityManager->flush();
        }
    }

    private function getCleanTitle(?string $title)
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

    private function fetchNextLevelMovie(Movie $movie)
    {
        $subMovies = $movie->getSubMovies();
        //try to get its sub movies from db
        if ($subMovies->count() === 0){
            $subMovies = $this->entityManager->getRepository(Movie::class)->findBy(['mainMovie' => 1]);
        }

        if ($subMovies === null || count($subMovies) === 0){
            if (!empty($movie->getSources())){
                $serverName = $movie->getSources()->get(0)->getServer()->getName();
                /** @var MovieServerInterface $server */
                $server = $this->servers[$serverName];
                //todo: cache
                $subMovies = $server->fetchMovie($movie);
                //todo: in new process match it with database and add it if missing
                $this->matchMovieList($subMovies, $server);
            }
            //todo: we may do something if source is empty
        }
        if ($subMovies instanceof Collection){
            $subMovies = $subMovies->toArray();
        }
        return $subMovies;
    }

    private function fetchMovieSublist(Movie $movie)
    {
        //check if sublist in db else fetch from server
    }
}
