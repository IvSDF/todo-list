<?php

namespace App\Entity;

enum Status: string
{
    case New = 'new';
    case Done = 'done';
    case Rejected = 'rejected';
}