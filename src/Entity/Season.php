<?php

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeasonRepository::class)]
#[ORM\Table(name: 'season')]
class Season extends Movie
{

    #[ORM\ManyToOne(inversedBy: 'seasons')]
    private ?Series $series = null;

    #[ORM\OneToMany(mappedBy: 'season', targetEntity: Episode::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $episodes;

    public function __construct()
    {
        parent::__construct();
        $this->episodes = new ArrayCollection();
    }

    public function getType(): ?MovieType
    {
        return MovieType::Season;
    }

    public function getSeries(): ?Series
    {
        return $this->series;
    }

    public function setSeries(?Series $series): static
    {
        $this->series = $series;

        return $this;
    }

    /**
     * @return Collection<int, Episode>
     */
    public function getEpisodes(): Collection
    {
        return $this->episodes;
    }

    public function addEpisode(Episode $episode): static
    {
        if (!$this->episodes->contains($episode)) {
            $this->episodes->add($episode);
            $episode->setSeason($this);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode): static
    {
        if ($this->episodes->removeElement($episode)) {
            // set the owning side to null (unless already changed)
            if ($episode->getSeason() === $this) {
                $episode->setSeason(null);
            }
        }

        return $this;
    }
}
