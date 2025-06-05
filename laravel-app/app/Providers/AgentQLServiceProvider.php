<?php

namespace App\Providers;

use App\Clients\AgentQLHttpClient;
use App\Services\AgentQL\PortfolioDataExtractor;
use Illuminate\Support\ServiceProvider;

class AgentQLServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->app->singleton(AgentQLHttpClient::class, function ($app) {
            return new AgentQLHttpClient(
                baseUrl: config('agentql.base_url'),
                apiKey: config('agentql.api_key'),
                timeout: config('agentql.timeout')
            );
        });

        $this->app->singleton(PortfolioDataExtractor::class, function ($app) {
            return new PortfolioDataExtractor(
                $app->make(AgentQLHttpClient::class)
            );
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        //
    }
} 