<?php

namespace App\Plugins;

use Illuminate\Support\Str;

class CollectOnlyJpgPlugin extends BasePlugin
{
    /**
     * @param string $image
     * @return bool
     */
    public static function filterImages(string $image): bool
    {
        return Str::endsWith($image, '.jpg');
    }
}
