<?php

namespace Myerscode\Laravel\QueryStrategies\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Override;

class MakeClauseCommand extends GeneratorCommand
{

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected $signature = 'make:clause {name}';

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected $description = 'Create a new query clause';


    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function getPath($name)
    {
        return parent::getPath('Queries/Clause/' . $name);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function rootNamespace()
    {
        return $this->laravel->getNamespace() . 'Queries\\Clause\\';
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
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
