<?php

namespace App\Providers;

use App\Clients\GuzzleClient;
use App\Contracts\Client;
use App\Contracts\Parser;
use App\Parsers\XpathParser;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Client::class, GuzzleClient::class);
        $this->app->bind(Parser::class, XpathParser::class);
    }
}
