<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

class Property
{

    private $value;

    public function __construct(private $originalValue)
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

    /**
     * @return Property
     */
    public function setValue(mixed $value)
    {
        $this->value = $value;

        return $this;
    }
}
