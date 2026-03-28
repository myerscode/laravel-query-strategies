<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

use Illuminate\Database\Eloquent\Model;

class DefaultModelStrategy extends Strategy
{

    public function __construct(protected array $parameters)
    {
        parent::__construct();
    }

    public static function fromConfig(array $config): DefaultModelStrategy
    {
        return new self($config);
    }
}
