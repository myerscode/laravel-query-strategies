<?php

namespace DummyNamespace;

use Myerscode\Laravel\QueryStrategies\Strategies\Property;
use Myerscode\Laravel\QueryStrategies\Transmute\TransmuteInterface;

class DummyClass implements TransmuteInterface
{

    public function transmute(Property $value): Property
    {
        return $value;
    }
}
