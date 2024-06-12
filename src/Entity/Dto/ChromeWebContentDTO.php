<?php

namespace App\Entity\Dto;

class ChromeWebContentDTO
{

    public function __construct(public string $content,
                                public array  $headers)
    {
    }
}