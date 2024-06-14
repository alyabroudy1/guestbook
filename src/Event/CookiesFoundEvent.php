<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CookiesFoundEvent extends Event
{
    public function __construct(private bool $cookiesFound, private array $headers) {}

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function isCookiesFound(): bool
    {
        return $this->cookiesFound;
    }
}