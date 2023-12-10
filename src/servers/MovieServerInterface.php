<?php

namespace App\servers;

use App\Entity\Movie;
use App\Entity\Server;
use App\Entity\Source;

interface MovieServerInterface
{
    public function getServerConfig(): Server;
    public function search(String $query): array;

    public function fetchMovie(Movie $movie);
    public function fetchSource(Source $source);
}