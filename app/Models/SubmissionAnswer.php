<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionAnswer extends Model
{
    use HasFactory;

    protected $connection = 'vantripper_tnc';
    public $timestamps = false;

    protected $fillable = [
        'submission_id',
        'question_id',
        'answer'
    ];

    protected $casts = [
        'submission_id' => 'integer',
        'question_id' => 'integer'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function question()
    {
        return $this->belongsTo(TermsQuestion::class, 'question_id');
    }
}