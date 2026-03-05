<?php

namespace App\Enums;

enum Role: string
{
    case Owner       = 'owner';
    case Admin       = 'admin';
    case Manager     = 'manager';
    case Employee    = 'employee';
    case Placeholder = 'placeholder';
}
