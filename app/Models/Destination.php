<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;

    protected $connection = 'vantripper_db';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function tours()
    {
        return $this->hasMany(Tour::class);
    }

    public function packageDestinations()
    {
        return $this->hasMany(PackageDestination::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}