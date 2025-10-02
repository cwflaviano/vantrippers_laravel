<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TermsAndCondition extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     * Using vantripper_invoice database where terms_and_conditions table exists
     */
    protected $connection = 'vantripper_invoice';

    /**
     * The table associated with the model.
     */
    protected $table = 'terms_and_conditions';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'content',
        'pdf_file_path',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [];

    /**
     * Scope to get only active terms and conditions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only inactive terms and conditions
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get the PDF file name from the path
     */
    public function getPdfFileNameAttribute()
    {
        if ($this->pdf_file_path) {
            return basename($this->pdf_file_path);
        }
        return null;
    }

    /**
     * Check if terms has PDF attachment
     */
    public function getHasPdfAttribute()
    {
        return !empty($this->pdf_file_path);
    }

    /**
     * Get full PDF URL
     */
    public function getPdfUrlAttribute()
    {
        if ($this->pdf_file_path) {
            return url('storage/' . $this->pdf_file_path);
        }
        return null;
    }

    /**
     * Get formatted created date
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('M d, Y');
    }

    /**
     * Get formatted updated date
     */
    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at->format('M d, Y');
    }
}
