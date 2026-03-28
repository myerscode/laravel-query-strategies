<?php

namespace Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    public array $strategyConfig = [
            'foo',
            'bar',
        ];

}
