<?php

namespace App\Enum;

enum DataSourceEnum: string
{
    case NEWS_API = 'news-api';

    case THE_GUARDIAN = 'guardian';
}
