<?php

namespace App\Providers;

use App\Contracts\DemandForecastServiceInterface;
use App\Contracts\ResourceRepositoryInterface;
use App\Repositories\EloquentResourceRepository;
use App\Services\DemandForecastService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ResourceRepositoryInterface::class, EloquentResourceRepository::class);
        $this->app->bind(DemandForecastServiceInterface::class, DemandForecastService::class);
    }

    public function boot(): void
    {
        //
    }
}
