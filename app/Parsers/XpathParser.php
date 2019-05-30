<?php

namespace App\Parsers;

use App\Contracts\Parser;
use App\Exceptions\ParseException;
use Illuminate\Support\Collection;

class XpathParser implements Parser
{
    /**
     * @param string $page
     * @return \Illuminate\Support\Collection
     * @throws \App\Exceptions\ParseException
     */
    public function parseUrls(string $page): Collection
    {
        try {
            $urls = new Collection();

            $document = new \DOMDocument();
            @$document->loadHTML('<?xml encoding="UTF-8">'.$page);
            $xpath = new \DOMXPath($document);

            $links = $xpath->query('//a');

            if ($links->length > 0) {
                foreach ($links as $linkNode) {
                    /** @var \DOMElement $linkNode */
                    $url = $linkNode->getAttribute('href');

                    if (\in_array($url, ['', '/', '#'], true)) {
                        continue;
                    }

                    if (mb_stripos($url, 'mailto:') !== false) {
                        continue;
                    }

                    if (mb_stripos($url, 'geo:') !== false) {
                        continue;
                    }

                    if ($urls->contains($url)) {
                        continue;
                    }

                    $urls->push($url);
                }
            }

            return $urls;
        }
        catch (\Exception $ex) {
            throw new ParseException($ex->getMessage());
        }
    }

    /**
     * @param string $page
     * @return \Illuminate\Support\Collection
     * @throws \App\Exceptions\ParseException
     */
    public function parseImages(string $page): Collection
    {
        try {
            $images = new Collection();

            $document = new \DOMDocument();
            @$document->loadHTML('<?xml encoding="UTF-8">'.$page);
            $xpath = new \DOMXPath($document);

            $imgList = $xpath->query('//img');

            if ($imgList->length > 0) {
                foreach ($imgList as $imgNode) {
                    /** @var \DOMElement $imgNode */
                    $image = $imgNode->getAttribute('src');

                    if ($images->contains($image)) {
                        continue;
                    }

                    $images->push($image);
                }
            }

            return $images;
        }
        catch (\Exception $ex) {
            throw new ParseException($ex->getMessage());
        }
    }
}
