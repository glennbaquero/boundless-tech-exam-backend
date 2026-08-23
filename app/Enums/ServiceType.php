<?php

namespace App\Enums;

enum ServiceType: string
{
    case OneWay = 'one_way';
    case Hourly = 'hourly';
}
