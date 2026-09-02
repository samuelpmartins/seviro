<?php

namespace App\Enums;

enum TableAssignmentType: int
{
    case Automatic = 1;
    case Manual = 2;
    case Transfer = 3;
}
