<?php

namespace App\Models\VantripperInvoice;

use Illuminate\Database\Eloquent\Model;

class Invoice_Packages extends Model
{
    protected $connection = 'vantripper_invoice';
    protected $table = 'invoice_package';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'sku',
        'quantity',
        'category',
        'items',
        'item_full_details',
        'price'
    ];
}
