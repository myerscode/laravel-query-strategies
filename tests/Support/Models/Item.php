<?php

namespace Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{

    public function owner()
    {
        return $this->hasOne(Owner::class);
    }
}
