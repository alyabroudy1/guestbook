<?php

namespace App\Service;

use App\Entity\Dto\ChromeWebContentDTO;
use App\Entity\Server;
use App\Event\CookiesFoundEvent;
use App\Repository\ServerRepository;
use App\servers\AbstractServer;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Communication\Message;
use HeadlessChromium\Exception\CommunicationException;
use HeadlessChromium\Exception\NavigationExpired;
use HeadlessChromium\Exception\NoResponseAvailable;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Page;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CookieFinderService
{
    public function __construct(private EventDispatcherInterface        $eventDispatcher,
                                private readonly ServerRepository       $serverRepository,
                                private readonly EntityManagerInterface $entityManager)
    {
    }

    public function findCookies(string $url, ?Server $server ): ChromeWebContentDTO{
        $url = 'https://wecima.show/watch/%d9%81%d9%8a%d9%84%d9%85-sonic-the-hedgehog-2020-%d9%85%d8%aa%d8%b1%d8%ac%d9%85-1/';
//        $browserFactory = new BrowserFactory('/usr/bin/chromedriver');
        $browserFactory = new BrowserFactory();

        $cookieFound = false;
// starts headless Chrome

        $browser = $browserFactory->addOptions([
            'userDataDir' => $userDataDir
        ])
            ->createBrowser([
            'headless' => true,
            'keepAlive' => false,
            'noSandbox' => true,
//            'userDataDir' => '/var/www/.local/chrome-user-data',
//            'userCrashDumpsDir' => '/var/www/.local/crashpad',
//            'noCrashpad' => true,
            'disableGpu' => true,
            'disable-dev-shm-usage' => true,
            'disableCrashReporter' => true,
        ]);

        try {
            try {
                $page = $browser->createPage();
            } catch (CommunicationException|NoResponseAvailable|OperationTimedOut $e) {
                dump('error: ' . $e->getMessage());
                return new JsonResponse(['message' => 'Error creating page']);
            }
            try {
                $page->getSession()->sendMessage(new Message(
                    'Network.enable',
                ));
            } catch (CommunicationException $e) {
                // do nothing
            }


            $referer = [];
            $client = new \GuzzleHttp\Client();
            $eventDispatcher = $this->eventDispatcher;
            $session = $page->getSession();
            $session->on('method:Network.requestWillBeSent', function (array $params) use (
                &$response,
                &$client,
                $url,
                $browser,
                $eventDispatcher,
                $session,
                &$chromeWebContentDto,
                &$server
            ): void {
//                dump(  $params);
                $headers = $params['request']['headers'];


                if ($browser != null) {
                    try {
//                $url = 'https://wecima.show/watch/%d9%81%d9%8a%d9%84%d9%85-sonic-the-hedgehog-2020-%d9%85%d8%aa%d8%b1%d8%ac%d9%85-1/';
                        $cResponse = $client->get($url, [
//                    'cookies' => $jar, // Pass the entire CookieJar to the request
                            'headers' => $headers,
                        ]);

                        if ($cResponse->getStatusCode() === 200) {
                            dump('success closing in listener');
                            $response->setContent(json_encode(['message' => 'Cookies found!', 'foundCookies' => $headers]));
                            $response->setStatusCode(Response::HTTP_OK);
//
                            $event = new CookiesFoundEvent(true, $headers);
                            $eventDispatcher->dispatch($event);
                            $chromeWebContentDto->headers = $headers;
                            $chromeWebContentDto->content = $cResponse->getBody()->getContents();
                            dd($headers);
                            $server->setHeaders($headers);
                            $this->entityManager->persist($server);
                            $this->entityManager->flush();
                            try {
                                $session->removeAllListeners('method:Network.requestWillBeSent');
                            } catch (\Exception $e) {
                                dump('fail closing in listener: ' . $e->getMessage());
                            }
                        }

//                dd($response->getStatusCode(), $params, $response->getBody()->getContents());

                    } catch (GuzzleException $e) {
                        dump('client failed: ', $e->getCode());
                    }

                }

            });


            $selector = 'singlecontainerright'; // Replace with your actual element selector
            try {
                $page->navigate($url)->waitForNavigation(Page::DOM_CONTENT_LOADED);
                $page->waitForReload(Page::FIRST_CONTENTFUL_PAINT);
            } catch (OperationTimedOut $e) {
                dump('fail waiting für element: ' . $e->getMessage());
            } catch (CommunicationException\CannotReadResponse $e) {
                dump('fail waiting für element: ' . $e->getMessage());
            } catch (CommunicationException\InvalidResponse $e) {
                dump('fail waiting für element: ' . $e->getMessage());
            } catch (CommunicationException $e) {
            } catch (NavigationExpired $e) {
            } catch (NoResponseAvailable $e) {
            }
//            dd($browser->getConnection());

//            dd($referer, $browser->getConnection());
            // get page title
//            $pageTitle = $page->evaluate('document.title')->getReturnValue();
//            $browser->close();
            // pdf
//            $page->pdf(['printBackground' => false])->saveToFile('/foo/bar.pdf');
        } finally {
            // bye
            try {
                $browser->close();
            } catch (\Exception $e) {
                dump('fail closinf in finally');
            }
        }
        return $chromeWebContentDto;
    }
    public function findCookies_old(string $url, ?Server $server ): ChromeWebContentDTO
    {
        dump('Cookies Found');
        $response = new JsonResponse(['message' => 'Processing request...']);
        $chromeWebContentDto = new ChromeWebContentDTO('', []);

        $url = 'https://wecima.show/watch/%d9%81%d9%8a%d9%84%d9%85-sonic-the-hedgehog-2020-%d9%85%d8%aa%d8%b1%d8%ac%d9%85-1/';
        $browserFactory = new BrowserFactory();

        $cookieFound = false;
// starts headless Chrome
        $browser = $browserFactory->createBrowser([
            'headless' => true,
            'keepAlive' => false,
            'noSandbox' => false,
            'userDataDir' => '/var/www/.local/chrome-user-data',
            'userCrashDumpsDir' => '/var/www/.local/crashpad',
            'noCrashpad' => true,
            'disableGpu' => true,
            'disable-dev-shm-usage' => true,
            'disableCrashReporter' => true,
        ]);

        try {
            try {
                $page = $browser->createPage();
            } catch (CommunicationException|NoResponseAvailable|OperationTimedOut $e) {
                dump('error: ' . $e->getMessage());
                return new JsonResponse(['message' => 'Error creating page']);
            }
            try {
                $page->getSession()->sendMessage(new Message(
                    'Network.enable',
                ));
            } catch (CommunicationException $e) {
                // do nothing
            }


            $referer = [];
            $client = new \GuzzleHttp\Client();
            $eventDispatcher = $this->eventDispatcher;
            $session = $page->getSession();
            $session->on('method:Network.requestWillBeSent', function (array $params) use (
                &$response,
                &$client,
                $url,
                $browser,
                $eventDispatcher,
                $session,
                &$chromeWebContentDto,
                &$server
            ): void {
//                dump(  $params);
                $headers = $params['request']['headers'];


                if ($browser != null) {
                    try {
//                $url = 'https://wecima.show/watch/%d9%81%d9%8a%d9%84%d9%85-sonic-the-hedgehog-2020-%d9%85%d8%aa%d8%b1%d8%ac%d9%85-1/';
                        $cResponse = $client->get($url, [
//                    'cookies' => $jar, // Pass the entire CookieJar to the request
                            'headers' => $headers,
                        ]);

                        if ($cResponse->getStatusCode() === 200) {
                            dump('success closing in listener');
                            $response->setContent(json_encode(['message' => 'Cookies found!', 'foundCookies' => $headers]));
                            $response->setStatusCode(Response::HTTP_OK);
//
                            $event = new CookiesFoundEvent(true, $headers);
                            $eventDispatcher->dispatch($event);
                            $chromeWebContentDto->headers = $headers;
                            $chromeWebContentDto->content = $cResponse->getBody()->getContents();
                            dd($headers);
                            $server->setHeaders($headers);
                            $this->entityManager->persist($server);
                            $this->entityManager->flush();
                            try {
                                $session->removeAllListeners('method:Network.requestWillBeSent');
                            } catch (\Exception $e) {
                                dump('fail closing in listener: ' . $e->getMessage());
                            }
                        }

//                dd($response->getStatusCode(), $params, $response->getBody()->getContents());

                    } catch (GuzzleException $e) {
                        dump('client failed: ', $e->getCode());
                    }

                }

            });


            $selector = 'singlecontainerright'; // Replace with your actual element selector
            try {
                $page->navigate($url)->waitForNavigation(Page::DOM_CONTENT_LOADED);
                $page->waitForReload(Page::FIRST_CONTENTFUL_PAINT);
            } catch (OperationTimedOut $e) {
                dump('fail waiting für element: ' . $e->getMessage());
            } catch (CommunicationException\CannotReadResponse $e) {
                dump('fail waiting für element: ' . $e->getMessage());
            } catch (CommunicationException\InvalidResponse $e) {
                dump('fail waiting für element: ' . $e->getMessage());
            } catch (CommunicationException $e) {
            } catch (NavigationExpired $e) {
            } catch (NoResponseAvailable $e) {
            }
//            dd($browser->getConnection());

//            dd($referer, $browser->getConnection());
            // get page title
//            $pageTitle = $page->evaluate('document.title')->getReturnValue();
//            $browser->close();
            // pdf
//            $page->pdf(['printBackground' => false])->saveToFile('/foo/bar.pdf');
        } finally {
            // bye
            try {
                $browser->close();
            } catch (\Exception $e) {
                dump('fail closinf in finally');
            }
        }
        return $chromeWebContentDto;
    }
}
