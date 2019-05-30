<?php

namespace App\Plugins;

use Faker\Provider\File;
use Illuminate\Filesystem\Filesystem;
use Imagick;

class CropPlugin extends BasePlugin
{
    private const THUMBNAIL_SIZE = 64;

    /**
     * @var
     */
    private static $storage;

    /**
     * @param string $name
     * @param string $imageContent
     * @throws \ImagickException
     */
    public static function imagePreProcessing(string $name, string $imageContent): void
    {
        if (!self::$storage instanceof Filesystem) {
            self::$storage = new Filesystem();
        }

        $thumbnailsPath = storage_path('thumbnails');

        if (!self::$storage->exists($thumbnailsPath)) {
            self::$storage->makeDirectory($thumbnailsPath, 0777);
        }

        $imagick = new Imagick();
        $imagick->readImageBlob($imageContent);
        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();

        $wK = $width / self::THUMBNAIL_SIZE;
        $hK = $height / self::THUMBNAIL_SIZE;
        $k = $wK > $hK ? $wK : $hK;

        $imagick->cropThumbnailImage(intdiv($width, $k), intdiv($height, $k));
        $imagick->writeImage("{$thumbnailsPath}\\{$name}");
    }
}
