<?php

namespace Myerscode\Laravel\QueryStrategies;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Myerscode\Laravel\QueryStrategies\Commands\MakeClauseCommand;
use Myerscode\Laravel\QueryStrategies\Commands\MakeStrategyCommand;
use Myerscode\Laravel\QueryStrategies\Commands\MakeTransmuteCommand;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([__DIR__ . '/Stubs/config.php' => config_path('query-strategies.php')], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands(MakeClauseCommand::class);
            $this->commands(MakeStrategyCommand::class);
            $this->commands(MakeTransmuteCommand::class);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->bind(FilterBuilder::class, static fn($app): FilterBuilder => new FilterBuilder($app->make(Request::class), $app->make(StrategyManager::class)));

        $this->app->alias(FilterBuilder::class, 'Query');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            FilterBuilder::class,
            'Query'
        ];
    }
}
