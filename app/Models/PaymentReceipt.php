<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PaymentReceipt extends Model
{
    use HasFactory;

    protected $connection = 'vantripper_tnc';
    public $timestamps = false;

    protected $fillable = [
        'submission_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type'
    ];

    protected $casts = [
        'submission_id' => 'integer',
        'file_size' => 'integer'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function getFileUrlAttribute()
    {
        if (!$this->file_path) {
            return null;
        }

        return url('storage/' . $this->file_path);
    }

    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function deleteFile()
    {
        if ($this->file_path && Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }
    }
}