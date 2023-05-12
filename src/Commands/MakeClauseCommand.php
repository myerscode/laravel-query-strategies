<?php

namespace Myerscode\Laravel\QueryStrategies\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeClauseCommand extends GeneratorCommand
{

    /**
     * {@inheritDoc}
     */
    protected $signature = 'make:clause {name}';

    /**
     * {@inheritDoc}
     */
    protected $description = 'Create a new query clause';


    /**
     * {@inheritDoc}
     */
    protected function getPath($name)
    {
        return parent::getPath('Queries/Clause/' . $name);
    }

    /**
     * {@inheritDoc}
     */
    protected function rootNamespace()
    {
        return $this->laravel->getNamespace() . 'Queries\\Clause\\';
    }

    /**
     * {@inheritDoc}
     */
    protected function qualifyClass($name)
    {
        $name = ucwords($name);
        if (!Str::endsWith($name, 'Clause')) {
            $name .= 'Clause';
        }

        return parent::qualifyClass($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function getStub()
    {
        return __DIR__ . '/../Stubs/Clause.php';
    }
}
