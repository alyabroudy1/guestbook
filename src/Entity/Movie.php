<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie
{
    public final const STATE_GROUP_OF_GROUP = 0;
    public final const STATE_GROUP = 1;
    public final const STATE_ITEM = 2;
    public final const STATE_RESOLUTION = 3;
    public final const STATE_VIDEO = 4;
    public final const STATE_BROWSE = 5;
    public final const STATE_RESULT= 6;
    public final const STATE_COOKIE = 7;
    public final const URL_DELIMITER = '||';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('movie_export')]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('movie_export')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups('movie_export')]
    private ?string $description = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups('movie_export')]
    private ?int $state = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('movie_export')]
    private ?string $cardImage = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('movie_export')]
    private ?string $backgroundImage = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups('movie_export')]
    private ?string $rate = null;

    #[ORM\Column(nullable: true)]
    #[Groups('movie_export')]
    private ?int $playedTime = null;

    #[ORM\Column(nullable: true)]
    #[Groups('movie_export')]
    private ?int $totalTime = null;

    #[ORM\Column(nullable: true)]
    #[Groups('movie_export')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups('movie_export')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'movie', targetEntity: Source::class, cascade: ['remove', 'persist'])]
    #[Groups('movie_export')]
    #[MaxDepth(1)]
    private Collection $sources;

    #[ORM\ManyToOne(targetEntity: Movie::class, inversedBy: 'subMovies')]
    private ?self $mainMovie = null;

    #[ORM\OneToMany(mappedBy: 'mainMovie', targetEntity: Movie::class, cascade: ['remove', 'persist'])]
    #[Groups('movie_export')]
    #[MaxDepth(1)]
    private Collection $subMovies;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('movie_export')]
    private ?string $videoUrl = null;

    #[ORM\ManyToMany(targetEntity: Category::class)]
    private Collection $categories;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->sources = new ArrayCollection();
        $this->subMovies = new ArrayCollection();
        $this->categories = new ArrayCollection();
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

    public function cloneMovie($withSource = false, $withSubMovie = false): self
    {
        $clone = new self();

        $clone->title = $this->title;
        $clone->description = $this->description;
        $clone->state = $this->state;
        $clone->cardImage = $this->cardImage;
        $clone->backgroundImage = $this->backgroundImage;
        $clone->rate = $this->rate;
        $clone->playedTime = $this->playedTime;
        $clone->totalTime = $this->totalTime;
        $clone->createdAt = $this->createdAt; // You might want to set this to the current time
        $clone->updatedAt = $this->updatedAt; // You might want to set this to the current time

        // For the collections, you'll need to clone each item
        if ($withSource){
            foreach ($this->sources as $source) {
                $cloneSource = clone $source;
                $clone->sources->add($cloneSource);
                $cloneSource->setMovie($clone);
            }
        }

        // If mainMovie is not null, clone it
        if ($this->mainMovie !== null) {
            $clone->mainMovie = clone $this->mainMovie;
        }

        // For the subMovies collection, you'll need to clone each item
        if ($withSubMovie) {
            foreach ($this->subMovies as $subMovie) {
                $cloneSubMovie = clone $subMovie;
                $clone->subMovies->add($cloneSubMovie);
                $cloneSubMovie->setMainMovie($clone);
            }
        }
        return $clone;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

}
