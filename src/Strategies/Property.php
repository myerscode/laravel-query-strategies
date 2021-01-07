<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

class Property
{

    private $originalValue;

    private $value;

    public function __construct($value)
    {
        $this->originalValue = $value;
        $this->value = $value;
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
     * @param  mixed  $value
     *
     * @return Property
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
