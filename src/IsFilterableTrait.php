<?php

namespace Myerscode\Laravel\QueryStrategies;

use Myerscode\Laravel\QueryStrategies\Exceptions\BuilderNotSetException;

trait IsFilterableTrait
{

    public function filter()
    {
        if (empty($this->strategy)) {
            throw new BuilderNotSetException('Need to set $strategy property');
        }

        return filter($this)->with($this->strategy);
    }
}