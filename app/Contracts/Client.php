<?php

namespace App\Contracts;

interface Client
{
    /**
     * @param string $url
     * @return string
     * @throws \App\Exceptions\ClientException
     */
    public function getPage(string $url): string;
}
