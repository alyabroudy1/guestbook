<?php

namespace App\EventListener;

use App\Event\MatchMoviesEvent;
use App\servers\MovieMatcher;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class MatchMoviesEventListener
{

    public function __construct(private MovieMatcher $movieMatcher)
    {
    }

    public function __invoke(MatchMoviesEvent $event): void
    {
        $this->movieMatcher->matchMovies($event->getMovies(), $event->getServer());
    }

}