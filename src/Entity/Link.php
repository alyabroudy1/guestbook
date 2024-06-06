<?php

namespace App\Entity;

use App\Repository\LinkRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
class Link
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('movie_export')]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('movie_export')]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('movie_export')]
    private ?string $url = null;

    #[ORM\Column(type: 'string', nullable: true, enumType: LinkState::class)]
    #[Groups('movie_export')]
    private ?LinkState $state = null;

    /**
     * in case the link url is splittable like:
     * @example : https://faselhd.com/sonic => [ server => Faselhd,  url => /sonic, authority => null, splittable => true]
     * @example : https://google.com/sonic => [ server => Faselhd,  url => /sonic, authority => https://google.com, splittable => false]
     * @var bool|null
     */
    #[ORM\Column(nullable: true)]
    #[Groups('movie_export')]
    private ?bool $splittable = null;


    #[ORM\ManyToOne]
    #[Groups('movie_export')]
    private ?Server $server = null;

    #[ORM\Column(length: 70, nullable: true)]
    #[Groups('movie_export')]
    private ?string $authority = null;

    #[ORM\ManyToOne]
    private ?Movie $movie = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getState(): ?LinkState
    {
        return $this->state;
    }

    public function setState(?LinkState $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function isSplittable(): ?bool
    {
        return $this->splittable;
    }

    public function setSplittable(?bool $splittable): static
    {
        $this->splittable = $splittable;

        return $this;
    }

    public function getServer(): ?Server
    {
        return $this->server;
    }

    public function setServer(?Server $server): static
    {
        $this->server = $server;

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

    public function getMovie(): ?Movie
    {
        return $this->movie;
    }

    public function setMovie(?Movie $movie): static
    {
        $this->movie = $movie;

        return $this;
    }
}
