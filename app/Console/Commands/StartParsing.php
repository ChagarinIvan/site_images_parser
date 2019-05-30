<?php

namespace App\Console\Commands;

use App\Contracts\Client;
use App\Contracts\Parser;
use App\Exceptions\ClientException;
use App\Exceptions\ParseException;
use App\Providers\PluginsManager;
use \Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StartParsing extends Command
{
    private const NET_PREFIXES = [
        'http://',
        'https://',
        'www.',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse {site} {--restart}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'start parsing process';

    /**
     * @var \App\Contracts\Client
     */
    private $client;

    /**
     * @var \App\Contracts\Parser
     */
    private $parser;

    /**
     * @var \Illuminate\Cache\CacheManager
     */
    private $cache;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $storage;

    /**
     * @var \App\Providers\PluginsManager
     */
    private $pluginManager;

    /**
     * StartParsing constructor.
     *
     * @param \App\Contracts\Client $client
     * @param \App\Contracts\Parser $parser
     * @param \Illuminate\Cache\Repository $cache
     * @param \Illuminate\Filesystem\Filesystem $storage
     */
    public function __construct(Client $client, Parser $parser, CacheRepository $cache, Filesystem $storage)
    {
        parent::__construct();

        $this->client = $client;
        $this->parser = $parser;
        $this->cache = $cache;
        $this->storage = $storage;
        $this->pluginManager = new PluginsManager();
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle()
    {
        $baseUrl = $this->argument('site');
        $restart = $this->option('restart');

        $imagesPath = storage_path('images');

        if ($restart) {
            $this->cache->clear();

            if ($this->storage->exists($imagesPath)) {
                $this->storage->deleteDirectory($imagesPath);
            }
        }

        $this->info("Start grabbing images from site {$baseUrl}");

        if (!$this->storage->exists($imagesPath)) {
            $this->storage->makeDirectory($imagesPath, 0777);
        }

        $state = $this->cache->has("{$baseUrl}_state") ?
            $this->cache->get("{$baseUrl}_state") + 1 :
            0;


        $urls = $this->cache->has("{$baseUrl}_urls") ?
            $this->cache->get("{$baseUrl}_urls") :
            new Collection([$baseUrl]);

        $images = $this->cache->has("{$baseUrl}_images") ?
            $this->cache->get("{$baseUrl}_images") :
            new Collection();

        $count = $this->cache->has("{$baseUrl}_count") ?
            $this->cache->get("{$baseUrl}_count") :
            0;

        for ($i = $state; $i < $urls->count(); $i++) {
            $url = $urls->get($i);

            try {
                $page = $this->client->getPage($url);
            }
            catch (ClientException $ex) {
                $this->error("You have an client error when load page {$url}: {$ex->getMessage()}");
                continue;
            }

            try {
                $newUrls = $this->parser->parseUrls($page);
            }
            catch (ParseException $ex) {
                $this->error("You have a parsing error when parse urls from page {$url}: {$ex->getMessage()}");
                continue;
            }

            $newUrls = $newUrls->transform(function(string $url) use ($baseUrl) {
                if (Str::startsWith($url, '/')) {
                    return $baseUrl . $url;
                }

                if (!Str::startsWith($url, self::NET_PREFIXES)) {
                    return "{$baseUrl}/{$url}";
                }

                return $url;
            })
                ->unique()
                ->filter(function(string $url) use ($baseUrl) {
                    $baseUrlPosition = mb_stripos($url, $baseUrl);

                    if ($baseUrlPosition === false) {
                        return !Str::startsWith($url, self::NET_PREFIXES);
                    }

                    if ($baseUrlPosition === 0) {
                        return true;
                    }

                    foreach (self::NET_PREFIXES as $prefix) {
                        if (Str::startsWith($url, $prefix)) {
                            return $baseUrlPosition === \strlen($prefix);
                        }
                    }

                    return false;
                });

            foreach ($this->pluginManager->getPlugins() as $plugin) {
                $newUrls = $newUrls->filter(function(string $url) use ($plugin) {
                    return $plugin::filterUrls($url);
                });
            }

            try {
                $newImages = $this->parser->parseImages($page);
            }
            catch (ParseException $ex) {
                $this->error("You have a parsing error when parse images from page {$url}: {$ex->getMessage()}");
                continue;
            }

            $newImages->transform(function(string $image) use ($baseUrl) {
                    if (Str::startsWith($image, '/')) {
                        return $baseUrl . $image;
                    }

                    if (!Str::startsWith($image, self::NET_PREFIXES)) {
                        return "{$baseUrl}/{$image}";
                    }

                    return $image;
                })
                ->unique();

            foreach ($this->pluginManager->getPlugins() as $plugin) {
                $newImages = $newImages->filter(function(string $image) use ($plugin) {
                    return $plugin::filterImages($image);
                });
            }

            foreach ($newUrls->all() as $newUrl) {
                if ($urls->contains($newUrl)) {
                    continue;
                }

                $urls->push($newUrl);
            }

            foreach ($newImages->all() as $newImage) {
                if ($images->contains($newImage)) {
                    continue;
                }

                $images->push($newImage);
            }

            $this->info("{$i}. {$url} - {$newUrls->count()} - {$newImages->count()}");

            $this->cache->put("{$baseUrl}_state", $i);
            $this->cache->put("{$baseUrl}_urls", $urls);
            $this->cache->put("{$baseUrl}_images", $images);
        }

        foreach ($images as $key => $image) {
            $imageName = str_replace(['/', ':', '?', '-', '	', '\\'], '_', $image);

            try {
                $content = $this->client->getPage($image);
            }
            catch (ClientException $ex) {
                $this->error("You have an client error when load image {$image}: {$ex->getMessage()}");
                continue;
            }

            foreach ($this->pluginManager->getPlugins() as $plugin) {
                $plugin::imagePreProcessing($imageName, $content);
            }

            $this->storage->put("{$imagesPath}\\{$imageName}", $content);
            unset($images[$key]);
            $count++;
            $this->cache->put("{$baseUrl}_count", $count);
            $this->cache->put("{$baseUrl}_images", $images);
        }

        $this->info("Grabbing process complete: grab {$count} images");
    }
}
