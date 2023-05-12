<?php

namespace Myerscode\Laravel\QueryStrategies;

use Myerscode\Laravel\QueryStrategies\Exceptions\BuilderNotSetException;
use Myerscode\Laravel\QueryStrategies\Exceptions\FilterStrategyNotFoundException;
use Myerscode\Laravel\QueryStrategies\Exceptions\InvalidStrategyException;

trait IsFilterableTrait
{

    /**
     * @throws BuilderNotSetException
     * @throws FilterStrategyNotFoundException
     * @throws InvalidStrategyException
     */
    public function filter(): Filter
    {
        if (empty($this->strategy)) {
            throw new BuilderNotSetException('Need to set $strategy property');
        }

        return filter($this)->with($this->strategy);
    }
}
