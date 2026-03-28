<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

class Property
{
    private mixed $value;

    public function __construct(private mixed $originalValue)
    {
        $this->value = $originalValue;
    }

    /**
     * @return mixed
     */
    public function getOriginalValue()
    {
        return $this->originalValue;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }
}
