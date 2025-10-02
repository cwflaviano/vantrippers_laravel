<?php

namespace App\Models\VantripperInvoice;

use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $connection = 'vantripper_invoice';
    protected $table = 'categories';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    
    protected $fillable = [
        'category_name',
        'description'
    ];
}


