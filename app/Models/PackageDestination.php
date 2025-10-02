<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageDestination extends Model
{
    use HasFactory;

    protected $connection = 'vantripper_db';

    protected $fillable = [
        'package_id',
        'destination_id',
        'display_order'
    ];

    protected $casts = [
        'package_id' => 'integer',
        'destination_id' => 'integer',
        'display_order' => 'integer'
    ];

    public function package()
    {
        return $this->belongsTo(Tour::class, 'package_id');
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}