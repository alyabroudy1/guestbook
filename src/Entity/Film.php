<?php

namespace App\Entity;

use App\Repository\FilmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FilmRepository::class)]
#[ORM\Table(name: 'film')]
class Film extends Movie
{
    public function __construct()
    {
        parent::__construct();
    }


    public function getType(): ?MovieType
    {
        return MovieType::Film;
    }
}
