<?php

namespace App\servers;

use App\Entity\Movie;
use App\Entity\Server;
use App\Entity\Source;
use Symfony\Component\HttpFoundation\Request;

interface MovieServerInterface
{
    public function getServerConfig(): Server;
    public function search(String $query, Request $request): array;

    public function fetchMovie(Movie $movie);
    public function fetchSource(Source $source);
}