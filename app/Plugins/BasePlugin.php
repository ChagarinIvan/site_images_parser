<?php

namespace App\Plugins;

use App\Contracts\Plugin;

class BasePlugin implements Plugin
{
    /**
     * @param string $url
     * @return bool
     */
    public static function filterUrls(string $url): bool
    {
        return true;
    }

    /**
     * @param string $image
     * @return bool
     */
    public static function filterImages(string $image): bool
    {
        return true;
    }

    /**
     * @param string $name
     * @param string $imageContent
     * @return void
     */
    public static function imagePreProcessing(string $name, string $imageContent): void
    {
    }
}
