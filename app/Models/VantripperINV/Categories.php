<?php

namespace App\Models\VantripperINV;

use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $connection = 'vantripper_invoice';
    protected $table = 'categories';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'category_name',
        'description'
    ];
}
