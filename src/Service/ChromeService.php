<?php

namespace App\Service;

use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeDriverService;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Exception\CommunicationException;
use HeadlessChromium\Exception\EvaluationFailed;
use HeadlessChromium\Exception\FilesystemException;
use HeadlessChromium\Exception\JavascriptException;
use HeadlessChromium\Exception\NavigationExpired;
use HeadlessChromium\Exception\NoResponseAvailable;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Exception\ScreenshotFailed;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class ChromeService
{

    public function getPageContents(string $url){

//        $options = new ChromeOptions();
//        $options->addArguments([
//            '--headless',
//            '--no-sandbox'
//        ]);
//
//        $host = 'http://selenium-chrome:4444'; // Replace with Docker container IP if using Docker
//        $capabilities = DesiredCapabilities::chrome();
//        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);  // Use options if defined
//        $driver = RemoteWebDriver::create($host, $capabilities, 100);
////        $service = ChromeDriverService::createDefaultService();
////        $driver = ChromeDriver::start($capabilities, $service);
////dd('ffff');
////        $driver->navigate('https://en.wikipedia.org/wiki/Selenium_(software)');
//        $driver->get('https://www.google.com'); // Open a webpage
//        $title = $driver->getTitle();
//       $cookie =  $driver->manage()->getCookies();
////        $title = $driver->getPageSource();
//
//        dd($title, $cookie, $driver);

//        $browserFactory = new BrowserFactory(__DIR__ . '/../drive/chromedriver');

        $browserFactory = new BrowserFactory();

        $cookieFound = false;
// starts headless Chrome
        $browser = $browserFactory->createBrowser([
            'headless' => true,
            'keepAlive' => true,
            'noSandbox' => false
        ]);


        $url = 'https://www.google.com';
//        dump('getPageContents: '. $url);
        $browserFactory->addOptions([
//            'headless' => true, // disable headless mode
//            'connectionDelay' => 0.8,            // add 0.8 second of delay between each instruction sent to Chrome,
//            'debugLogger'     => 'php://stdout', // will enable verbose mode
////            'windowSize'   => [1920, 1000],
//            'enableImages' => false,
            'noSandbox' => true
        ]);
//        dd('getPageContents: ', $browserFactory->createBrowser());
//// starts headless Chrome
//        $browser = $browserFactory->createBrowser([
//            'headless' => true, // disable headless mode
//            'connectionDelay' => 0.8,            // add 0.8 second of delay between each instruction sent to Chrome,
//            'debugLogger'     => 'php://stdout', // will enable verbose mode
//            'windowSize'   => [1920, 1000],
//            'enableImages' => false,
//            'noSandbox' => true
//        ]);

        try {
            // creates a new page and navigate to an URL
            $page = $browser->createPage();
            dd($page);
            $page->navigate($url)->waitForNavigation();

            // get page title
            $pageTitle = $page->evaluate('document.title')->getReturnValue();
dd('title: ' . $pageTitle);
            // screenshot - Say "Cheese"! 😄
            $page->screenshot()->saveToFile('/foo/bar.png');

            // pdf
            $page->pdf(['printBackground' => false])->saveToFile('/foo/bar.pdf');
        } catch (CommunicationException $e) {
            dd('error: createPage: CommunicationException: '. $e->getMessage());

        } catch (NoResponseAvailable $e) {
            dd('error: NoResponseAvailable: '. $e->getMessage());
        } catch (OperationTimedOut $e) {
            dd('error: OperationTimedOut: '. $e->getMessage());
        } catch (NavigationExpired $e) {
            dd('error: waitForNavigation: NavigationExpired: '. $e->getMessage());
        } catch (EvaluationFailed $e) {
            dd('error: evaluate: EvaluationFailed: '. $e->getMessage());
        } catch (JavascriptException $e) {
            dd('error: JavascriptException: '. $e->getMessage());
        } catch (FilesystemException $e) {
            dd('error: saveToFile: FilesystemException: '. $e->getMessage());
        } catch (ScreenshotFailed $e) {
            dd('error: ScreenshotFailed: '. $e->getMessage());
        } finally {
            // bye
            try {
                $browser->close();
            } catch (\Exception $e) {
                dd('error: browserClose: '. $e->getMessage());
            }
        }
    }

}