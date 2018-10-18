<?php

namespace Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Myerscode\Laravel\QueryStrategies\IsFilterableTrait;
use Tests\Support\Strategies\BasicConfigQueryStrategy;

class TodoList extends Model
{
    use IsFilterableTrait;
}
