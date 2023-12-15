<?php

namespace App\Controller;

use App\Entity\Movie;

use App\Entity\Source;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Encoder\EncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class MovieController
 *
 * This class represents a controller for managing movies.
 * It extends the AbstractController class.
 * it offers two api end points:
 * - search(query) which returns a list of result movies.
 * - fetch(movie) which returns the next level of movie state
 */
#[Route('/movie')]
class MovieController extends AbstractController
{
    public function __construct(
        private ServersController $serversController,
        private SerializerInterface $serializer
    )
    {
    }
    /**
     * Searches for movies based on the provided query.
     *
     * @param string $query The search query.
     * @return JsonResponse The JSON response containing the search results.
     */
    #[Route('/search/{query}', name: 'app_movie_search')]
    public function search($query, Request $request): JsonResponse
    {
        $movieList = $this->serversController->search($query);
        $json = $this->serializer->serialize(
            $movieList,
            'json',
            [
                AbstractNormalizer::GROUPS => 'movie_export',
                JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ]
        );
dd($json);
        return new JsonResponse([
            'type' => 'search',
            "title" => $query,
            "result" => $json,
        ]);
    }

    #[Route('/fetchMovie/{movie}', name: 'app_movie_fetch_movie')]
    public function fetchMovie(Movie $movie): JsonResponse
    {
        //todo: validate input of the movie id
        $movieList =  $this->serversController->fetchMovie($movie);

        return $this->json([
            'message' => 'Welcome to fetch',
            'path' => 'src/Controller/MovieController.php',
        ]);
    }

    #[Route('/fetchSource/{source}', name: 'app_movie_fetch_source')]
    public function fetchSource(Source $source): JsonResponse
    {
        $movieList =  $this->serversController->fetchSource($source);
        return $this->json([
            'message' => 'Welcome to fetch',
            'path' => 'src/Controller/MovieController.php',
        ]);
    }

}
