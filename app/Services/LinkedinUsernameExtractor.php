<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LinkedinUsernameExtractor
{
    public function handle(string $content): Collection
    {
        return collect(explode("\n", $content))
            ->map(fn($link) => trim(Str::after($link, "https://www.linkedin.com/in/"), "/"))
            ->unique()
            ->filter()
            ->values();
    }
}
