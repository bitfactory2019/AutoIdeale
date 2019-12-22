<?php

namespace App\Filters;

use App\Library;

class MonthFilter
{
    public function __invoke($number)
    {
        return Library::MONTHS[$number];
    }
}
