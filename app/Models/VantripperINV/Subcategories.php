<?php

namespace App\Models\VantripperINV;

use Illuminate\Database\Eloquent\Model;

class Subcategories extends Model
{
    protected $connection = 'vantripper_invoice';
    protected $table = 'subcategories';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'subcategory_name',
        'details'
    ];
}
