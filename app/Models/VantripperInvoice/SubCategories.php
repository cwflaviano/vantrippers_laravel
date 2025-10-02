<?php

namespace App\Models\VantripperInvoice;

use Illuminate\Database\Eloquent\Model;

class SubCategories extends Model
{
    protected $connection = 'vantripper_invoice';
    protected $table = 'subcategories';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    
    protected $fillable = [
        'category_id',
        'subcategory_name',
        'details'
    ];
}
