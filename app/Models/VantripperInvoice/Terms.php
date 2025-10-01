<?php

namespace App\Models\VantripperInvoice;

use Illuminate\Database\Eloquent\Model;

class Terms extends Model
{
    protected $connection = 'vantripper_invoice';
    protected $table = 'terms';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    const UPDATED_AT = null;     
    
    protected $fillable = [
        'category',
        'details'
    ];
}
