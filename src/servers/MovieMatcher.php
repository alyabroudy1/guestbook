<?php

namespace App\servers;

use App\Entity\Link;
use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;

class MovieMatcher
{
    public function __construct( private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param Movie|null $mainMovie
     * @param Movie $episode
     * @return void
     */
    private function matchMovie(Movie $movie): Movie
    {
        $existingLink = $this->entityManager->getRepository(Link::class)
            ->findOneBy(['url' => $movie->getLink()->getUrl()]);
//dd($existingMovie);
        if (!$existingLink){
            $this->entityManager->persist($movie);
            $this->entityManager->flush();
            return $movie;
        }
        return $existingLink->getMovie();

//        $existingSubIndex = $this->isSubMovieExist($mainMovie, $episode);
//        if ($existingSubIndex === null) {
//            $episode->addSource($source);
//            $mainMovie->addSubMovie($episode);
//            $this->entityManager->persist($source);
//            $this->entityManager->persist($episode);
//        } else {
//            $matchedExistingMovie = $mainMovie->getSubMovies()->get($existingSubIndex);
//            if (null === $this->isSourceExist($matchedExistingMovie, $source)) {
//                $this->entityManager->persist($source);
//                $matchedExistingMovie->addSource($source);
//            }
//        }

    }

    private function isSubMovieExist(?Movie $mainMovie, Movie $newSubMovie) :?int
    {
        foreach ($mainMovie->getSubMovies() as $oldMovie){
            if ($newSubMovie->getTitle() === $oldMovie->getTitle()){
                return $mainMovie->getSubMovies()->indexOf($oldMovie);
            }
        }
        return null;
    }

    private function isSourceExist(mixed $mainMovie, Source $newSource):?int
    {
        foreach ($mainMovie->getSources() as $oldSource){
            if ($newSource->getTitle() === $oldSource->getTitle()){
                return $mainMovie->getSources()->indexOf($oldSource);
            }
        }
        return null;
    }

    public function matchSearchList(array $result, AbstractServer $server)
    {
        /*
         * todo: make movie title unique
         * 1.case group:
         * series => save if title not exist
         *
         * todo: if in search a season found, a series need to be created and name of the series
         *  to be adjust to avoid season titling
         * season => generate series name and
         * find existing series
         *      exist? -> no -> save series
         *             -> yes -> check if to be saved season match seasons of that series
         *                  if no match save it
         *
         * 2.case item:
         * episode: => generate season name and
         * find existing season
         *      exist? -> no -> save series
         *             -> yes -> check if to be saved episode match episode of that season
         *                  if no match save it
         */



        for($i = 0; $i < count($result); $i++){
            $result[$i] = $this->matchMovie($result[$i]);

//            /** @var Movie $existingMovie */
//            $existingMovie = $this->getExistingMovie($movie);
//            if ($existingMovie){
//                dump('matchSearchList: existingMovie');
////                $newSources = $this->findNewSources($existingMovie, $movie);
////                foreach ($newSources as $nSource){
////                    $existingMovie->getSources()->add($nSource);
////                    $this->entityManager->persist($nSource);
////                }
//            }else{
//                dump('matchSearchList: new Movie');
//                $title = $this->getCleanTitle($movie->getTitle());
//                $movie->setTitle($title);
////                if ($movie->getSources()->first()) {
////                    $this->entityManager->persist($movie->getSources()->first());
////                }
//
//                $this->entityManager->persist($movie);
//                $this->entityManager->flush();
//            }
        }
        return $result;
    }

    private function getExistingMovie(Movie $movie)
    {
        $title = $this->getCleanTitle($movie->getTitle());
        $result = $this->entityManager->getRepository(Movie::class)->findByTitleAndType($title, $movie->getType());
        dump('getExistingMovie result', $result);
        $matchedMovie = null;

        if (count($result) > 0) {
            /** @var Movie $matchedMovie */
            $matchedMovie = $this->detectCorrectMatch($result, $movie);
        }

        return $matchedMovie;
    }

    private function detectCorrectMatch(array $existingMovies, mixed $movie)
    {
        $title = $this->getCleanTitle($movie->getTitle());
        foreach ($existingMovies as $existingMovie) {
            $existingTitle = $this->getCleanTitle($existingMovie->getTitle());
            // dump($title. ', '.$existingTitle, $existingTitle === $title);
            if ($existingTitle === $title) {
                return $existingMovie;
            }
        }
        return null;
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

    public function matchMovies(?array $movies, AbstractServer $getServer)
    {
        $movieList = [];
        foreach ($movies as $movie){
            $movieList [] = $this->matchMovie($movie);
        }
        return $movieList;
    }

    private function findNewSources(Movie $existingMovie, mixed $movie)
    {
        $newSources = [];
        /** @var Source $xSource */
        foreach ($movie->getSources() as $xSource){
            $isNew = true;
            /** @var Source $nSource */
            foreach ($existingMovie->getSources() as $nSource){
                $titleCond = $nSource->getTitle() === $xSource->getTitle();
                $serverCond = $nSource->getServer() !== $xSource->getServer();
                if ($titleCond && $serverCond){
                    $isNew = false;
                    break;
                }
            }
            if ($isNew){
                $newSources[] = $nSource;
            }
        }
        return $newSources;
    }
}