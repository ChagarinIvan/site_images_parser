<?php

namespace App\Plugins;

use Illuminate\Support\Str;

class CollectOnlyGifPlugin extends BasePlugin
{
    /**
     * @param string $image
     * @return bool
     */
    public static function filterImages(string $image): bool
    {
        return Str::endsWith($image, '.gif');
    }
}
