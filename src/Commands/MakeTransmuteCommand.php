<?php

namespace Myerscode\Laravel\QueryStrategies\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Override;

class MakeTransmuteCommand extends GeneratorCommand
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected $description = 'Create a new query property transmute handler.';

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected $signature = 'make:transmute {name}';


    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function getPath($name)
    {
        return parent::getPath('Queries/Transmute/' . $name);
    }

    /**
     * {@inheritDoc}
     */
    protected function getStub()
    {
        return __DIR__ . '/../Stubs/Transmute.php';
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
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
    #[Override]
    protected function rootNamespace()
    {
        return $this->laravel->getNamespace() . 'Queries\\Transmute\\';
    }
}
