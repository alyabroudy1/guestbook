<?php

namespace App\Entity;

use App\Repository\SeriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeriesRepository::class)]
#[ORM\Table(name: 'series')]
class Series extends Movie
{
    #[ORM\OneToMany(mappedBy: 'series', targetEntity: Season::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $seasons;

    public function __construct()
    {
        parent::__construct();
        $this->seasons = new ArrayCollection();
    }

    public function getType(): ?MovieType
    {
        return MovieType::Series;
    }

    /**
     * @return Collection<int, Season>
     */
    public function getSeasons(): Collection
    {
        return $this->seasons;
    }

    public function addSeason(Season $season): static
    {
        if (!$this->seasons->contains($season)) {
            $this->seasons->add($season);
            $season->setSeries($this);
        }

        return $this;
    }

    public function removeSeason(Season $season): static
    {
        if ($this->seasons->removeElement($season)) {
            // set the owning side to null (unless already changed)
            if ($season->getSeries() === $this) {
                $season->setSeries(null);
            }
        }

        return $this;
    }
}
