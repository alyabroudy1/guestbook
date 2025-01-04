<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Film;
use App\Entity\Link;
use App\Entity\LinkState;
use App\Entity\Movie;
use App\Entity\Server;
use App\Entity\ServerModel;
use App\Entity\Source;
use App\Event\MatchMoviesEvent;
use App\servers\AbstractServer;
use App\servers\AkwamTube;
use App\servers\MovieMatcher;
use App\servers\MovieServerInterface;
use App\servers\MyCima;
use App\Service\CookieFinderService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\InvalidArgumentException;
use http\Header\Parser;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ServersController extends AbstractController
{
    private $servers;

    public function __construct(private HttpClientInterface  $httpClient, private EntityManagerInterface $entityManager,
                                private MovieMatcher         $matcher,
                                private  CookieFinderService $cookieFinderService, private readonly EventDispatcherInterface $eventDispatcher)
    {
        $this->initializeServers();
    }

    public function search($query): array
    {
        //get search result from servers
        //todo: try to get the result from database first and if theres no result then fetch it from the net
        //todo: find a way to update database movies something like fetched the last added movies once a day
        //todo:optimize search
//       $movieList = $this->getMovieListFromDB($query);
//       dump('getMovieListFromDB: ' . count($movieList));
//        $movieList = [];
//        if (empty($movieList)) {
            //search all server and add result to db
            $this->searchAllServers($query);
            //fetch result again from db
            $movieList = $this->getMovieListFromDB($query);
//            dd('$movieList', $movieList);

//        }

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

    /**
     * @param Movie $movie
     * @return Link[]
     * @throws TransportExceptionInterface
     */
    public function fetchMovie(Movie $movie): array
    {

        $link = $movie->getLink();
        /** @var AbstractServer $server */
        $server = $this->servers[$link->getServer()->getModel()->name];

        if ($movie instanceof Film || $movie instanceof Episode) {
            try {
                return $server->fetchItem($movie, $this->cookieFinderService);
            }catch (\Exception $exception){
                dump('fetchMovie: '. $exception->getMessage(), get_class($exception));
                return [];
            }
        }

        $fetchGroupResult = $server->fetchGroup($movie, $this->cookieFinderService);

        $fetchGroupResult = $this->matcher->matchMovies($fetchGroupResult, $server);
        return $fetchGroupResult;
dd('nope');
//        /** @var Movie $result */
//        $result = match ($movie->getState()) {
//            Movie::STATE_GROUP_OF_GROUP => $server->fetchGroupOfGroup($movie),
//            Movie::STATE_GROUP => $server->fetchGroup($movie),
//        };

        $this->entityManager->refresh($movie);;

        return $movie;
    }


    public function fetchSource(Source $source): Movie
    {
        /** @var MovieServerInterface $server */
        $server = $this->servers[$source->getServer()->getName()];

        if ($source->getState() === Movie::STATE_ITEM) {
            return $server->fetchItem($source);
        }

        /** @var Movie $result */
        $result = match ($source->getState()) {
            Movie::STATE_GROUP_OF_GROUP => $server->fetchGroupOfGroup($source),
            Movie::STATE_GROUP => $server->fetchGroup($source),
        };

        $this->entityManager->refresh($source->getMovie());;

        return $source->getMovie();
    }

    private function initializeServers()
    {
        //akwamTube
        //fetch new Server() from db
        //todo: suggest refactoring
//        $this->initializeServer(ServerModel::AkwamTube, 'https://i.akwam.tube', 'https://i.akwam.tube');
        // $this->initializeServer(ServerModel::Mycima, 'https://wecima.show', 'https://mycima.io');
        $this->initializeServer(ServerModel::Mycima, 'https://wecima.show', 'https://mycima.io');
        // $this->servers[ServerModel::Iptv->name] = $this->generateServer($serverModel, $serverConfig);
//        $this->initializeServer(ServerModel::Mycima, 'https://i.akwam.tube', 'https://i.akwam.tube');

//        //myCima
//        //fetch new Server() from db
//        //todo: suggest refactoring
//        $myCimaServerConfig = $this->entityManager->getRepository(Server::class)->findOneBy(['name' => Server::SERVER_MYCIMA]);
//
//        if (!$myCimaServerConfig) {
//            $myCimaServerConfig = new Server();
//            $myCimaServerConfig->setName(Server::SERVER_MYCIMA);
//            $myCimaServerConfig->setWebAddress('https://wemycema.shop');
//            $myCimaServerConfig->setDefaultWebAddress('https://mycima.io');
//            //only the first time if server is not saved to db
//            $myCimaServerConfig->setActive(true);
//            //only the first time if server is not saved to db
//            $this->entityManager->persist($myCimaServerConfig);
//            $this->entityManager->flush();
//        }
//        $this->servers[Server::SERVER_MYCIMA] = MyCima::getInstance($this->httpClient, $myCimaServerConfig, $this->matcher);

        //todo: other server ...
    }

    private function getMovieListFromDB($query)
    {
        return $this->entityManager->getRepository(Movie::class)->findMoviesByTitleLoose($query);
    }

    private function searchAllServers($query)
    {

        //todo: doing it using thread or workers for performance
        /** @var AbstractServer $server */
        foreach ($this->servers as $server) {
            $result = $server->search($query);
//            dd($result);
//            $this->eventDispatcher->dispatch(new MatchMoviesEvent($result, $server));
            $result = $this->matcher->matchMovies($result, $server);
//            dd('searchAllServers: ' . $server->getConfig()->getModel()->name,
//                $result);
        }
    }

    public function getHomepageMovies()
    {
        $result = [];
//        if (isset($this->servers[Server::SERVER_MYCIMA])){
//            $result = $this->getMovieListFromDB('/seriestv/');
//
//            //$movieList = [];
//            if (empty($result)) {
//                /** @var MovieServerInterface $server */
//                $server = $this->servers[Server::SERVER_MYCIMA];
//                $result = $server->search($server->getServerConfig()->getAuthority().'/seriestv/');
//                $this->matcher->matchSearchList($result, $server);
//            }
//        }
        return $this->entityManager->getRepository(Movie::class)->findLastThirtyMovies();
    }

    public function initializeServer(ServerModel $serverModel, string $authority, string $defaultAuthority): void
    {
        $serverConfig = $this->entityManager->getRepository(Server::class)->findOneByModel($serverModel);
//        dd($serverConfig);

        if (empty($serverConfig)) {
            $serverConfig = new Server();
            $serverConfig->setName($serverModel->name);
            $serverConfig->setModel($serverModel);
            $serverConfig->setAuthority($authority);
            $serverConfig->setDefaultAuthority($defaultAuthority);
            $serverConfig->setActive(true);
            //only the first time if server is not saved to db
            $this->entityManager->persist($serverConfig);
            $this->entityManager->flush();
        }
        $this->servers[$serverModel->name] = $this->generateServer($serverModel, $serverConfig);
    }

    private function generateServer(ServerModel $serverModel, Server $serverConfig)
    {
        return match ($serverModel) {
            ServerModel::AkwamTube => AkwamTube::getInstance($this->httpClient, $serverConfig),
            ServerModel::Mycima => MyCima::getInstance($this->httpClient, $serverConfig, $this->matcher),
            default => throw new InvalidArgumentException("Unsupported ServerModel")
        };
    }

}
