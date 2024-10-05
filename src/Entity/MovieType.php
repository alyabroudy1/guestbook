<?php

namespace App\Entity;

enum MovieType: string{
    case Series = 'Series';
    case Season = 'Season';
    case Episode = 'Episode';
    case Film = 'Film';
    case IptvChannel = 'Iptv_channel';
}