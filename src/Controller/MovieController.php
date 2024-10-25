<?php

namespace App\Controller;

use App\Entity\Film;
use App\Entity\Movie;
use App\Repository\IptvChannelRepository;
use App\servers\IptvServer;
use App\Service\ChromeService;
use App\Service\CookieFinderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Encoder\EncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


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
    public const FETCH_PATH = 'fetch';
    public const MOVIE_PATH = 'movie';

    public function __construct(
        private ServersController   $serversController,
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
    public function search($query, Request $request, IptvChannelRepository $iptvRepo): JsonResponse
    {
        $isTv = $request->query->get('tv');

//        return new Response($query);
        // $movieList = $this->serversController->search($query);

//        $movieList = $iptvRepo->search($query);
        $categoryList = $iptvRepo->search($query);

//        foreach ($categoryList as $category) {
//
//        }
//        $data = [
//            ['type' => 'search',
//            'title' => $query,
//            'result' => $movieList,]
//        ];


        $json = $this->serialize($categoryList);
        return JsonResponse::fromJsonString($json);
    }

    /**
     * Searches for movies based on the provided query.
     *
     * @param string $query The search query.
     * @return JsonResponse The JSON response containing the search results.
     */
    #[Route('/homepage', name: 'app_movie_homepage')]
    public function homepage(IptvChannelRepository $iptvRepo): JsonResponse
    {
        $movieList = $this->serversController->getHomepageMovies();

//        $json = $this->serialize($movieList);
//        $data = [
//            [
//'type' => 'search',
//            'title' => 'homepage',
//            'result' => $movieList,
//            ]
//        ];

        $categoryList = $iptvRepo->getHomepageResults(false);

        $json = $this->serialize($categoryList);
        return JsonResponse::fromJsonString($json);
    }

    #[Route('/fetch/{id}', name: 'app_movie_fetch_source')]
//    public function fetchMovie(Movie $movie, ChromeService $chromeService): JsonResponse
    public function fetchMovie($id, HttpClientInterface $httpClient, CookieFinderService $cookieFinderService): Response
    {
        $requestHeaders = [
            'Icy-MetaData' => 1,
            'User-Agent' => 'airmaxtv',
            'Accept-Encoding' => 'identity',
            'Host' => 'airmax.boats',
            'Connection' => 'Keep-Alive'
        ];


        $url = "https://airmax.boats/airmaxtvXXSW/airmaxtvWWSX/518.ts";
        $response = $httpClient->request('GET', $url, [
            'headers' => $requestHeaders,
        ]);
        //needs to be called in order to fetch headers
        $response->getHeaders();
        $responseHeaders = $response->getInfo()['response_headers'];
        if (!$responseHeaders){
            // fail to fetch video url
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }
        $videoUrl = null;
        $locationKey = 'Location:';
        foreach ($responseHeaders as $header) {
            if (str_contains($header, $locationKey)){
                $videoUrl =trim(str_replace($locationKey, '', $header));
                break;
            }
        }
        if (!$videoUrl){
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }

        // Convert array to URL-encoded query string and replace '&' with '|'
        $queryString = http_build_query($requestHeaders);
        $delimiter = '|';

        return $this->redirect($videoUrl. $delimiter . $queryString);

//        $response = new JsonResponse(['message' => 'Processing request...']);
//        $chromeService->getPageContents($movie->getLink()->getUrl());

        //todo: check incoming movie state
        //if available in db the next state return it or fetch it and return it

//        $result = $this->serversController->fetchMovie($movie);

//        $json = $this->serialize($result);

//        return JsonResponse::fromJsonString($json);
        return new JsonResponse();
    }
//
//    #[Route('/fetchSource/{source}', name: 'app_movie_fetch_source')]
//    public function fetchSource(Source $source): JsonResponse
//    {
//        $movieList =  $this->serversController->fetchSource($source);
//        return $this->json([
//            'message' => 'Welcome to fetch',
//            'path' => 'src/Controller/MovieController.php',
//        ]);
//    }
    /**
     * @param array $movieList
     * @return string
     */
    public function serialize(array $movieList): string
    {
        $json = $this->serializer->serialize(
            $movieList,
            'json',
            [
                AbstractNormalizer::GROUPS => 'movie_export',
                JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                    //  if ($object instanceof Movie)
                    return $object->getId();
                },
                AbstractNormalizer::CALLBACKS => [
                    'type' => function ($object) {
                        // Get the class name without the namespace
//                        $className = (new \ReflectionClass($object))->getShortName();
//                        // Convert the class name into the corresponding MovieType value
//                        return constant("App\\Enum\\MovieType::$className");
                        return $object->value;
                    },
                ],
            ]
        );

        return $json;
    }

}
