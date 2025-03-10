<?php

namespace App\Entity;

use App\Repository\IptvChannelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: IptvChannelRepository::class)]
#[ORM\Table(name: 'iptv_channel')]
class IptvChannel extends Movie
{

    #[Groups('movie_export')]
    #[ORM\Column(length: 70, nullable: true)]
    private ?string $tvgName = null;

    #[Groups('movie_export')]
    #[ORM\Column(length: 512, nullable: true)]
    private ?string $tvgLogo = null;

    #[Groups('movie_export')]
    #[ORM\Column(length: 70, nullable: true)]
    private ?string $groupTitle = null;

    #[Groups('movie_export')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fileName = null;

    #[Groups('movie_export')]
    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $credentialUrl = null;

    #[Groups('movie_export')]
    public function getType(): ?MovieType
    {
        return MovieType::IptvChannel;
    }

    public function getTvgName(): ?string
    {
        return $this->tvgName;
    }

    public function setTvgName(?string $tvgName): static
    {
        $this->tvgName = $tvgName;

        return $this;
    }

    public function getTvgLogo(): ?string
    {
        return $this->tvgLogo;
    }

    public function setTvgLogo(?string $tvgLogo): static
    {
        $this->tvgLogo = $tvgLogo;

        return $this;
    }

    public function getGroupTitle(): ?string
    {
        return $this->groupTitle;
    }

    public function setGroupTitle(?string $groupTitle): static
    {
        $this->groupTitle = $groupTitle;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getCredentialUrl(): ?string
    {
        return $this->credentialUrl;
    }

    public function setCredentialUrl(?string $credentialUrl): static
    {
        $this->credentialUrl = $credentialUrl;

        return $this;
    }
}
