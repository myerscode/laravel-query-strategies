<?php

namespace Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Myerscode\Laravel\QueryStrategies\IsFilterableTrait;
use Tests\Support\Strategies\BasicConfigQueryStrategy;

class Register extends Model
{
    use IsFilterableTrait;

    public $strategy = BasicConfigQueryStrategy::class;
}
