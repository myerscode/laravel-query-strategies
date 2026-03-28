<?php

namespace Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletableItem extends Model
{
    use SoftDeletes;

    protected $table = 'soft_items';
}
