<?php

namespace Myerscode\Laravel\QueryStrategies\Stubs;

use Myerscode\Laravel\QueryStrategies\Strategies\Property;
use Myerscode\Laravel\QueryStrategies\Transmute\TransmuteInterface;

class DummyClass implements TransmuteInterface
{

    public function transmute(Property $property): Property
    {
        return $property;
    }
}
