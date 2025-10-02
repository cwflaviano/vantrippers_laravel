<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermsQuestion extends Model
{
    use HasFactory;

    protected $connection = 'vantripper_tnc';
    protected $table = 'terms_questions';

    protected $fillable = [
        'package_id',
        'question_text',
        'yes_option',
        'no_option',
        'sort_order'
    ];

    protected $casts = [
        'package_id' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = [
        'package_name'
    ];

    public function package()
    {
        return $this->belongsTo(\DB::connection('vantripper_tnc')->table('packages'), 'package_id');
    }

    public function submissionAnswers()
    {
        return $this->hasMany(SubmissionAnswer::class, 'question_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function getPackageNameAttribute()
    {
        $package = \DB::connection('vantripper_tnc')
            ->table('packages')
            ->where('id', $this->package_id)
            ->first();

        if (!$package) {
            return 'Unknown Package';
        }

        // Handle different possible name columns
        return $package->name ?? $package->package_name ?? $package->title ?? 'Package ' . $package->id;
    }
}