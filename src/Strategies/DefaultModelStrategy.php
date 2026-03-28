<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

class DefaultModelStrategy extends Strategy
{
    public function __construct(protected array $config)
    {
        parent::__construct();
    }

    public static function fromConfig(array $config): DefaultModelStrategy
    {
        return new self($config);
    }
}
