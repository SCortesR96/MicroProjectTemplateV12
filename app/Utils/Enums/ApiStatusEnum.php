<?php

namespace App\Utils\Enums;

enum ApiStatusEnum: string
{
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case ERROR = 'error';
}
