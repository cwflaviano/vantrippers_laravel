<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Companion extends Model
{
    use HasFactory;

    protected $connection = 'vantripper_tnc';
    public $timestamps = false;

    protected $fillable = [
        'submission_id',
        'full_name'
    ];

    protected $casts = [
        'submission_id' => 'integer'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }
}