<?php

namespace App\Plugins;

class ParseOnlyOnePage extends BasePlugin
{
    /**
     * @param string $url
     * @return bool
     */
    public static function filterUrls(string $url): bool
    {
        return false;
    }
}
