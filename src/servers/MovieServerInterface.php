<?php

namespace App\servers;

use App\Entity\Movie;
use App\Entity\Server;
use App\Entity\Source;
use Symfony\Component\HttpFoundation\Request;

interface MovieServerInterface
{
    public function getServerConfig(): Server;
    public function search(String $query): array;
    public function fetchSource(Source $source);
    public function fetchGroupOfGroup(Source $source);
    public function fetchGroup(Source $source);
}