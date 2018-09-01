<?php

namespace Myerscode\Laravel\QueryStrategies;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Myerscode\Laravel\QueryStrategies\Commands\MakeClauseCommand;
use Myerscode\Laravel\QueryStrategies\Commands\MakeStrategyCommand;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/Stubs/config.php' => config_path('query-strategies.php')], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands(MakeClauseCommand::class);
            $this->commands(MakeStrategyCommand::class);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(FilterBuilder::class, function ($app) {
            return new FilterBuilder($app->make('Illuminate\Http\Request'), $app->make(StrategyManager::class));
        });

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
