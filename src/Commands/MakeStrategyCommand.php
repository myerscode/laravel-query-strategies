<?php

namespace Myerscode\Laravel\QueryStrategies\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeStrategyCommand extends GeneratorCommand
{

    /**
     * {@inheritDoc}
     */
    protected $signature = 'make:strategy {name}';

    /**
     * {@inheritDoc}
     */
    protected $description = 'Create a new query strategy';


    /**
     * {@inheritDoc}
     */
    protected function getPath($name)
    {
        return parent::getPath('Queries/Strategies/' . $name);
    }

    /**
     * {@inheritDoc}
     */
    protected function rootNamespace()
    {
        return $this->laravel->getNamespace() . 'Queries\\Strategies\\';
    }

    /**
     * {@inheritDoc}
     */
    protected function qualifyClass($name)
    {
        $name = ucwords($name);
        if (!Str::endsWith($name, 'Strategy')) {
            $name .= 'Strategy';
        }
        return parent::qualifyClass($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function getStub()
    {
        return __DIR__ . '/../Stubs/Strategy.php';
    }
}
