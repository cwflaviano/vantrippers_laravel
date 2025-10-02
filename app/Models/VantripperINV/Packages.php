<?php

namespace App\Models\VantripperINV;

use Illuminate\Database\Eloquent\Model;

class Packages extends Model
{
    protected $connection = 'vantripper_invoice';
    protected $table = 'invoice_package';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'sku',
        'quantity',
        'category',
        'items',
        'items_full_details',
        'price',
        'created_at'
    ];
}
