<?php

namespace App\Contracts;

interface Plugin
{
    /**
     * @param string $url
     * @return bool
     */
    public static function filterUrls(string $url): bool;

    /**
     * @param string $image
     * @return bool
     */
    public static function filterImages(string $image): bool;

    /**
     * @param string $name
     * @param string $imageContent
     * @return void
     */
    public static function imagePreProcessing(string $name, string $imageContent): void;
}
