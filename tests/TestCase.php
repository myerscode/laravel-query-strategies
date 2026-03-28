<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Myerscode\Laravel\QueryStrategies\Filter;
use Myerscode\Laravel\QueryStrategies\Strategies\StrategyInterface;
use Myerscode\Laravel\QueryStrategies\StrategyManager;
use Myerscode\Laravel\QueryStrategies\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function filter($builderOrModel, string|StrategyInterface|Model $strategyClass, $request = null, $config = []): Filter
    {
        $strategy = $this->strategyManager()->findStrategy($strategyClass);
        return new Filter($builderOrModel, $strategy, $request ?? [], $config);
    }

    public function getRawSqlFromBuilder(Builder $builder): string
    {
        $query = str_replace(['?'], ["'%s'"], $builder->toSql());
        return vsprintf($query, $builder->getBindings());
    }

    public function request(array $replace = [])
    {
        $request = $this->app->make(Request::class);
        $request->replace($replace);
        return $request;
    }

    public function simpleDatabase(Application $application): void
    {
        $application['db']->connection()->getSchemaBuilder()->create('items', static function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('name');
            $blueprint->string('likes');
            $blueprint->timestamps();
        });
    }

    public function softDeletableDatabase(Application $application): void
    {
        $application['db']->connection()->getSchemaBuilder()->create('soft_items', static function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('name');
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });
    }

    public function strategyManager(): StrategyManager
    {
        return new StrategyManager();
    }
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }
}
