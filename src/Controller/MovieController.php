<?php

namespace App\Controller;

use Amp\Http\Client\Connection\DefaultConnectionFactory;
use Amp\Http\Client\HttpClient;
use App\Entity\Film;
use App\Entity\Movie;
use App\Repository\AirmaxCredentialRepository;
use App\Repository\IptvChannelRepository;
use App\servers\IptvServer;
use App\Service\ChromeService;
use App\Service\CookieFinderService;
use Artax\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpClient\AmpHttpClient;
use Symfony\Component\HttpClient\Response\AmpResponse;
use Symfony\Component\HttpClient\Response\CurlResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

    #[Route('/cookie', name: 'app_movie_fetch_cookie')]
//    public function fetchMovie(Movie $movie, ChromeService $chromeService): JsonResponse
    public function fetchCookie(HttpClientInterface $httpClient, CookieFinderService $cookieFinderService): Response
    {
        $url = 'https://www.faselhds.care';
        $cookieFinderService->findCookies($url, null);
    }

    #[Route('/fetch/{id}', name: 'app_movie_fetch_source')]
//    public function fetchMovie(Movie $movie, ChromeService $chromeService): JsonResponse
    public function fetchMovie($id, HttpClientInterface $httpClient, CookieFinderService $cookieFinderService, AirmaxCredentialRepository $credentialRepo): Response
    {
        $httpClient = new AmpHttpClient();
        $requestHeaders = [
            'Icy-MetaData' => 1,
            'User-Agent' => 'airmaxtv',
            'Accept-Encoding' => 'identity',
            'Host' => 'airmax.boats',
            'Connection' => 'Keep-Alive'
        ];
        $credentialUrl = "https://airmax.boats/airmaxtvMAX/airmaxtvXAM/";

        $credentials  = $credentialRepo->findOneBy(['domain' => 'airmax']);

        if ($credentials) {
            $credentialUrl = $credentials->getCredentialUrl();
        }

        $url = $credentialUrl.$id;

        /** @var AmpResponse $response */
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

//        dd($response, $response->getHeaders(), $response->getInfo(), new Response());
        return $this->getRedirectFromAmpResponse($response);
        $videoUrl = $response->getInfo()['url'];
//        $videoUrl = null;
//        $locationKey = 'Location:';
//        foreach ($responseHeaders as $header) {
//            if (str_contains($header, $locationKey)){
//                $videoUrl =trim(str_replace($locationKey, '', $header));
//                break;
//            }
//        }
        if (!$videoUrl){
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }

        // Convert array to URL-encoded query string and replace '&' with '|'
        $queryString = http_build_query($requestHeaders);
        $delimiter = '|';

//        return $this->redirect($videoUrl. $delimiter . $queryString);
         $response = new RedirectResponse($videoUrl . $delimiter . $queryString);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;

//        $response = new JsonResponse(['message' => 'Processing request...']);
//        $chromeService->getPageContents($movie->getLink()->getUrl());

        //todo: check incoming movie state
        //if available in db the next state return it or fetch it and return it

//        $result = $this->serversController->fetchMovie($movie);

//        $json = $this->serialize($result);

//        return JsonResponse::fromJsonString($json);
//        return new JsonResponse();
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

    public function streamMedia($url, $headers): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($url, $headers) {
            $contextOptions = [
                'http' => [
                    'header' => $headers
                ]
            ];
            $context = stream_context_create($contextOptions);

            // Open the file stream
            if ($stream = fopen($url, 'rb', false, $context)) {
                while (!feof($stream)) {
                    echo fread($stream, 8192); // Read and output in chunks
                    flush(); // Ensure data is sent to the client immediately
                }
                fclose($stream); // Close the stream when done
            } else {
                throw new \Exception('Unable to open stream.');
            }
        });

        // Set the appropriate headers for the media type
        $response->headers->set('Content-Type', $headers['content-type']); // Change as needed
        $response->headers->set('Content-Disposition', 'inline; filename="live-stream.mp4"'); // Change filename as needed

        return $response;
    }


        private function getRedirectFromAmpResponse(AmpResponse $response)
    {
        $videoUrl = $response->getInfo()['url'];
        $symfonyResponse = new RedirectResponse($videoUrl, Response::HTTP_FOUND, $response->getHeaders());

        // Set headers from the original response
//        foreach ($response->getInfo() as $name => $values) {
//            if ($name === 'response_headers') {
//                continue;
//            }
//            if (is_array($values) || is_string($values) || $values === null) {
//                $symfonyResponse->headers->set($name, $values);
//            }
//        }
            return $this->streamMedia($videoUrl, $response->getHeaders());
        dd($response);
        $symfonyResponse->headers->set('primary-ip', '77.247.109.133');

        return $symfonyResponse;
    }

}
