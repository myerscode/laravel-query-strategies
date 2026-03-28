<?php

namespace Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Myerscode\Laravel\QueryStrategies\IsFilterableTrait;

class TodoList extends Model
{
    use IsFilterableTrait;
}
