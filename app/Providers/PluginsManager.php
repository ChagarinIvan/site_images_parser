<?php

namespace App\Providers;

use App\Plugins\CollectOnlyGifPlugin;
use App\Plugins\CollectOnlyJpgPlugin;
use App\Plugins\CropPlugin;
use App\Plugins\ParseOnlyOnePage;

class PluginsManager
{
    protected $plugins = [
        //CropPlugin::class,
        //CollectOnlyGifPlugin::class,
        //CollectOnlyJpgPlugin::class,
        //ParseOnlyOnePage::class,
    ];

    /**
     * @return \Generator|\App\Contracts\Plugin[]
     */
    public function getPlugins()
    {
        foreach ($this->plugins as $plugin) {
            yield $plugin;
        }
    }
}
