<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

class DefaultModelStrategy extends Strategy
{
    /**
     * @param array<int|string, array<string, mixed>|string> $config
     */
    public function __construct(protected array $config)
    {
        parent::__construct();
    }

    /**
     * @param array<int|string, array<string, mixed>|string> $config
     */
    public static function fromConfig(array $config): DefaultModelStrategy
    {
        return new self($config);
    }
}
