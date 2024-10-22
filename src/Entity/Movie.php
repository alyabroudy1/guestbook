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
#[ORM\MappedSuperclass]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', enumType: MovieType::class)]
#[ORM\DiscriminatorMap(['Series' => Series::class, 'Season' => Season::class, 'Episode' => Episode::class, 'Film' => Film::class, 'Iptv_channel' => IptvChannel::class])]
#[ORM\Table(name: 'movie')]
abstract class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('movie_export')]
    private ?int $id = null;

    public final const STATE_GROUP_OF_GROUP = 0;
    public final const STATE_GROUP = 1;
    public final const STATE_ITEM = 2;
    public final const STATE_RESOLUTION = 3;
    public final const STATE_VIDEO = 4;
    public final const STATE_BROWSE = 5;
    public final const STATE_RESULT= 6;
    public final const STATE_COOKIE = 7;
    public final const URL_DELIMITER = '|';

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('movie_export')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups('movie_export')]
    private ?string $description = null;

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
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    #[Groups('movie_export')]
    private ?\DateTimeImmutable $updatedAt;

    #[ORM\OneToOne(targetEntity: Link::class, inversedBy: 'movie', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'link_id', referencedColumnName: 'id', onDelete: 'CASCADE')]  // Movie is the owning side
    #[MaxDepth(1)]
    #[Groups('movie_export')]
    private Link $link;

    #[ORM\ManyToMany(targetEntity: Category::class, cascade: ['persist'])]
    #[MaxDepth(1)]
    #[Groups('movie_export')]
    private Collection $categories;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $searchContext = null;

    #[ORM\Column(length: 1500, nullable: true)]
    #[Groups('movie_export')]
    private ?string $url = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->categories = new ArrayCollection();
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

    public function setTotalTime(?int $totalTime): static
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

    public function getSearchContext(): ?string
    {
        return $this->searchContext;
    }

    public function setSearchContext(?string $searchContext): static
    {
        $this->searchContext = $searchContext;

        return $this;
    }

//    public abstract function getType(): ?MovieType;
//
//    public function setType(MovieType $type): static
//    {
//        $this->type = $type;
//        return $this;
//    }

    public function getLink(): Link
    {
        return $this->link;
    }

    public function setLink(Link $link): void
    {
        $this->link = $link;
    }

    public function getId(): ?int
    {
        return $this->id;
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

}
