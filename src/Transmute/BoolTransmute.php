<?php

namespace Myerscode\Laravel\QueryStrategies\Transmute;

use Myerscode\Laravel\QueryStrategies\Strategies\Property;

class BoolTransmute implements TransmuteInterface
{

    public function transmute(Property $property): Property
    {
        if ($this->isTrue($property->getOriginalValue())) {
            $property->setValue(1);
        } else {
            $property->setValue(0);
        }

        return $property;
    }

    protected function isTrue($value): bool
    {
        return $value == 'ok' || (true === filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
    }
}
