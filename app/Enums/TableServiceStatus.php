<?php

namespace App\Enums;

enum TableServiceStatus: int
{
    case Active = 1;
    case Finished = 2;
    case Transferred = 3;
    case Cancelled = 4;
}
