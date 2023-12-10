<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie
{
    public final const STATE_GROUP_OF_GROUP = 0;
    public final const STATE_GROUP = 1;
    public final const STATE_ITEM = 2;
    public final const STATE_RESOLUTION = 3;
    public final const STATE_VIDEO = 4;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $state = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cardImage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $backgroundImage = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $rate = null;

    #[ORM\Column(nullable: true)]
    private ?int $playedTime = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalTime = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'movie', targetEntity: Source::class, cascade: ['remove', 'persist'])]
    private Collection $sources;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'mainMovie')]
    private ?self $mainMovie = null;

    #[ORM\OneToMany(mappedBy: 'mainMovie', targetEntity: self::class, cascade: ['remove', 'persist'])]
    private Collection $subMovies;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->sources = new ArrayCollection();
        $this->subMovies = new ArrayCollection();
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(?int $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getCardImage(): ?string
    {
        return $this->cardImage;
    }

    public function setCardImage(?string $cardImage): static
    {
        $this->cardImage = $cardImage;

        return $this;
    }

    public function getBackgroundImage(): ?string
    {
        return $this->backgroundImage;
    }

    public function setBackgroundImage(?string $backgroundImage): static
    {
        $this->backgroundImage = $backgroundImage;

        return $this;
    }

    public function getRate(): ?string
    {
        return $this->rate;
    }

    public function setRate(?string $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function getPlayedTime(): ?int
    {
        return $this->playedTime;
    }

    public function setPlayedTime(?int $playedTime): static
    {
        $this->playedTime = $playedTime;

        return $this;
    }

    public function getTotalTime(): ?int
    {
        return $this->totalTime;
    }

    public function setTotalTime(int $totalTime): static
    {
        $this->totalTime = $totalTime;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Source>
     */
    public function getSources(): Collection
    {
        return $this->sources;
    }

    public function addSource(Source $source): static
    {
        if (!$this->sources->contains($source)) {
            $this->sources->add($source);
            $source->setMovie($this);
        }

        return $this;
    }

    public function removeSource(Source $source): static
    {
        if ($this->sources->removeElement($source)) {
            // set the owning side to null (unless already changed)
            if ($source->getMovie() === $this) {
                $source->setMovie(null);
            }
        }

        return $this;
    }

    public function getMainMovie(): ?self
    {
        return $this->mainMovie;
    }

    public function setMainMovie(?self $mainMovie): static
    {
        $this->mainMovie = $mainMovie;

        return $this;
    }

    /**
     * @return Collection<int, Source>
     */
    public function getSubMovies(): Collection
    {
        return $this->subMovies;
    }

    public function addSubMovie(self $subMovie): static
    {
        if (!$this->subMovies->contains($subMovie)) {
            $this->subMovies->add($subMovie);
            $subMovie->setMainMovie($this);
        }

        return $this;
    }

    public function removeSubMovie(self $subMovie): static
    {
        if ($this->subMovies->removeElement($subMovie)) {
            // set the owning side to null (unless already changed)
            if ($subMovie->getMainMovie() === $this) {
                $subMovie->setMainMovie(null);
            }
        }

        return $this;
    }
}
