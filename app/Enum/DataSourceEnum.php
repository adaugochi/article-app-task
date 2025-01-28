<?php

namespace App\Enum;

enum DataSourceEnum: string
{
    case NEWS_API = 'news_api';

    case THE_GUARDIAN = 'guardian';

    case NEW_YORK_TIMES = 'new_york_times';
}
