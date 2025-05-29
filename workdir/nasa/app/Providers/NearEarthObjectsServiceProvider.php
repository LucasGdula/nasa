<?php

namespace App\Providers;

use App\Clients\NASA\NearEarthObjectsApiClient;
use App\Services\NASA\NearEarthObjectsService;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\ServiceProvider;

class NearEarthObjectsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NearEarthObjectsApiClient::class, function () {
            return new NearEarthObjectsApiClient(
                new GuzzleClient()
            );
        });

        $this->app->singleton(NearEarthObjectsService::class, function ($app) {
            return new NearEarthObjectsService(
                $app->make(NearEarthObjectsApiClient::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
