<?php

namespace App\Models\VantripperINV;

use Illuminate\Database\Eloquent\Model;

class Terms extends Model
{
    protected $connection = 'vantripper_invoice';
    protected $table = 'terms';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'category',
        'details',
        'created_at'
    ];
}
