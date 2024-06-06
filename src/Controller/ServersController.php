<?php

namespace App\Controller;

use App\Entity\Film;
use App\Entity\Link;
use App\Entity\LinkState;
use App\Entity\Movie;
use App\Entity\Server;
use App\Entity\ServerModel;
use App\Entity\Source;
use App\servers\AbstractServer;
use App\servers\AkwamTube;
use App\servers\MovieMatcher;
use App\servers\MovieServerInterface;
use App\servers\MyCima;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use http\Header\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ServersController extends AbstractController
{
    private $servers;

    public function __construct(private HttpClientInterface $httpClient, private EntityManagerInterface $entityManager, private MovieMatcher $matcher)
    {
        $this->initializeServers();
    }

    public function search($query): array
    {
        //get search result from servers
        //todo: try to get the result from database first and if theres no result then fetch it from the net
        //todo: find a way to update database movies something like fetched the last added movies once a day
        //todo:optimize search
       $movieList = $this->getMovieListFromDB($query);
       dump('getMovieListFromDB: ' . count($movieList));
//        $movieList = [];
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

        if ($movie instanceof Film) {
            return $server->fetchItem($movie);
        }
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
        $akwamTubeServerConfig = $this->entityManager->getRepository(Server::class)->findOneByModel(ServerModel::AkwamTube);
//        dd($akwamTubeServerConfig, empty($akwamTubeServerConfig));
        //        $akwamTubeServerConfig = [];
        if (empty($akwamTubeServerConfig)) {
            $akwamTubeServerConfig = new Server();
            $akwamTubeServerConfig->setName(Server::SERVER_AKWAM);
            $akwamTubeServerConfig->setModel(ServerModel::AkwamTube);
            $akwamTubeServerConfig->setAuthority('https://i.akwam.tube');
            $akwamTubeServerConfig->setDefaultAuthority('https://i.akwam.tube');
            $akwamTubeServerConfig->setActive(true);
            //only the first time if server is not saved to db
            $this->entityManager->persist($akwamTubeServerConfig);
            $this->entityManager->flush();
        }
        $this->servers[ServerModel::AkwamTube->name] = AkwamTube::getInstance($this->httpClient, $akwamTubeServerConfig[0]);

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
            $this->matcher->matchSearchList($result, $server);
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

}
