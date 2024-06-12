<?php

namespace App\Event;

use App\servers\AbstractServer;
use Symfony\Contracts\EventDispatcher\Event;

class MatchMoviesEvent extends Event
{

    /**
     * @param array|null $movies
     * @param AbstractServer $server
     */
    public function __construct(private ?array $movies, private AbstractServer $server)
    {
    }

    public function getMovies(): ?array
    {
        return $this->movies;
    }

    public function getServer(): AbstractServer
    {
        return $this->server;
    }
}