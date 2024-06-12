<?php

namespace App\Entity;

use App\Repository\ServerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ServerRepository::class)]
class Server
{

    public const SERVER_AKWAM = 'AkwamTube';
    public const SERVER_MYCIMA = 'mycima';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('movie_export')]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('movie_export')]
    private ?string $name = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $headers = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $cookie = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('movie_export')]
    private ?string $authority = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $rate = null;

    #[ORM\Column(nullable: true)]
    private ?bool $active = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $defaultAuthority = null;

    #[ORM\Column(type: 'string', unique: true, nullable: true, enumType: ServerModel::class)]
    private ?ServerModel $model = null;

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

    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    public function setHeaders(?array $headers): static
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

    public function getAuthority(): ?string
    {
        return $this->authority;
    }

    public function setAuthority(?string $authority): static
    {
        $this->authority = $authority;

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

    public function getDefaultAuthority(): ?string
    {
        return $this->defaultAuthority;
    }

    public function setDefaultAuthority(?string $defaultAuthority): static
    {
        $this->defaultAuthority = $defaultAuthority;

        return $this;
    }

    public function getModel(): ?ServerModel
    {
        return $this->model;
    }

    public function setModel(?ServerModel $model): static
    {
        $this->model = $model;

        return $this;
    }
}
