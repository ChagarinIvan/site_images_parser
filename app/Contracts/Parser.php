<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface Parser
{
    /**
     * @param string $page
     * @return \Illuminate\Support\Collection
     * @throws \App\Exceptions\ParseException
     */
    public function parseUrls(string $page): Collection;

    /**
     * @param string $page
     * @return \Illuminate\Support\Collection
     * @throws \App\Exceptions\ParseException
     */
    public function parseImages(string $page): Collection;
}
