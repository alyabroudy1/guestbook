<?php

namespace App\Entity\Dto;

class HtmlMovieDto
{


    public function __construct(
        public string $title,
    public string $videoUrl,
    public string $description,
    public string $cardImage,
    public string $rate
    )
    {
    }
}