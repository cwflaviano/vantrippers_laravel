<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Package extends Model
{
    use HasFactory;

    protected $connection = 'vantripper_db';
    protected $table = 'packages';

    protected $fillable = [
        'title',
        'slug',
        'duration',
        'subtitle',
        'description',
        'inclusions',
        'exclusions',
        'destination_id',
        'package_type',
        'tour_type',
        'frontend_category',
        'image',
        'image_alt',
        'active',
        'featured',
        'display_order'
    ];

    protected $casts = [
        'destination_id' => 'integer',
        'active' => 'boolean',
        'featured' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function combinedDestinations()
    {
        return $this->hasMany(PackageDestination::class, 'package_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('package_type', $type);
    }

    public function scopeByTourType($query, $tourType)
    {
        return $query->where('tour_type', $tourType);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('frontend_category', $category);
    }

    public function scopeByDestination($query, $destinationId)
    {
        return $query->where('destination_id', $destinationId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('subtitle', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('created_at', 'desc');
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        return url('storage/' . $this->image);
    }

    public function getFormattedTourTypeAttribute()
    {
        if (!$this->tour_type) {
            return 'Standard';
        }

        return ucfirst(str_replace('_', ' ', $this->tour_type));
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('M d, Y');
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at->format('M d, Y H:i');
    }

    public function getCombinedDestinationsListAttribute()
    {
        if ($this->package_type !== 'combined') {
            return null;
        }

        return $this->combinedDestinations()
            ->with('destination')
            ->orderBy('display_order')
            ->get()
            ->pluck('destination.name')
            ->implode(', ');
    }

    public function toggleActive()
    {
        $this->update(['active' => !$this->active]);
    }

    public function toggleFeatured()
    {
        $this->update(['featured' => !$this->featured]);
    }

    public function deleteImage()
    {
        if ($this->image && Storage::exists($this->image)) {
            Storage::delete($this->image);
        }
    }
}
