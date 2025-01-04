<?php

namespace App\Entity;

use App\Repository\EpisodeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: EpisodeRepository::class)]
class Episode extends Movie
{
    #[ORM\ManyToOne(cascade: ['remove', 'persist'], inversedBy: 'episodes')]
    private ?Season $season = null;
    public function __construct()
    {
        parent::__construct();
    }

    #[Groups('movie_export')]
    public function getType(): ?MovieType
    {
        return MovieType::Episode;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): static
    {
        $this->season = $season;

        return $this;
    }
}
