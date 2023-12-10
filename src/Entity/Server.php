<?php

namespace App\Entity;

use App\Repository\ServerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServerRepository::class)]
class Server
{

    public const SERVER_AKWAM = 'AkwamTube';
    public const SERVER_MYCIMA = 'mycima';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $headers = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $cookie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $webAddress = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $rate = null;

    #[ORM\Column(nullable: true)]
    private ?bool $active = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getHeaders(): ?string
    {
        return $this->headers;
    }

    public function setHeaders(?string $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    public function getCookie(): ?string
    {
        return $this->cookie;
    }

    public function setCookie(?string $cookie): static
    {
        $this->cookie = $cookie;

        return $this;
    }

    public function getWebAddress(): ?string
    {
        return $this->webAddress;
    }

    public function setWebAddress(?string $webAddress): static
    {
        $this->webAddress = $webAddress;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(?int $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): static
    {
        $this->active = $active;

        return $this;
    }
}
