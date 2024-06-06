<?php

namespace App\Entity;

enum LinkState: string
{
    case Browse = 'Browse';
    case Video = 'Video';
    case Fetch = 'Fetch';
}
