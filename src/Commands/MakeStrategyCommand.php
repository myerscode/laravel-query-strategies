<?php

namespace Myerscode\Laravel\QueryStrategies\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Override;

class MakeStrategyCommand extends GeneratorCommand
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected $description = 'Create a new query strategy';

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected $signature = 'make:strategy {name}';


    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function getPath($name)
    {
        return parent::getPath('Queries/Strategies/' . $name);
    }

    /**
     * {@inheritDoc}
     */
    protected function getStub()
    {
        return __DIR__ . '/../Stubs/Strategy.php';
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
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
    #[Override]
    protected function rootNamespace()
    {
        return $this->laravel->getNamespace() . 'Queries\\Strategies\\';
    }
}
