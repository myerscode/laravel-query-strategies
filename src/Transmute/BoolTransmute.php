<?php

namespace Myerscode\Laravel\QueryStrategies\Transmute;

use Myerscode\Laravel\QueryStrategies\Strategies\Property;

class BoolTransmute implements TransmuteInterface
{

    public function transmute(Property $value): Property
    {
        if ($this->isTrue($value->getOriginalValue())) {
            $value->setValue(1);
        } else {
            $value->setValue(0);
        }

        return $value;
    }

    protected function isTrue($value): bool
    {
        return in_array($value, ['ok']) || (true === filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
    }
}
