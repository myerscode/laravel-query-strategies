<?php

namespace Myerscode\Laravel\QueryStrategies\Transmute;

use Myerscode\Laravel\QueryStrategies\Strategies\Property;

interface TransmuteInterface
{
    public function transmute(Property $property): Property;
}
