<?php

namespace Myerscode\Laravel\QueryStrategies\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeTransmuteCommand extends GeneratorCommand
{

    /**
     * {@inheritDoc}
     */
    protected $signature = 'make:transmute {name}';

    /**
     * {@inheritDoc}
     */
    protected $description = 'Create a new query property transmute handler.';


    /**
     * {@inheritDoc}
     */
    protected function getPath($name)
    {
        return parent::getPath('Queries/Transmute/' . $name);
    }

    /**
     * {@inheritDoc}
     */
    protected function rootNamespace()
    {
        return $this->laravel->getNamespace() . 'Queries\\Transmute\\';
    }

    /**
     * {@inheritDoc}
     */
    protected function qualifyClass($name)
    {
        $name = ucwords($name);
        if (!Str::endsWith($name, 'Transmute')) {
            $name .= 'Transmute';
        }

        return parent::qualifyClass($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function getStub()
    {
        return __DIR__ . '/../Stubs/Transmute.php';
    }
}
