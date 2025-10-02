<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Submission extends Model
{
    use HasFactory;

    protected $connection = 'vantripper_tnc';
    public $timestamps = false;

    protected $fillable = [
        'package_type',
        'email',
        'lead_guest',
        'fb_name',
        'contact_number',
        'payment_date',
        'payment_amount',
        'has_payment_receipt',
        'archived',
        'created_at'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'payment_amount' => 'decimal:2',
        'has_payment_receipt' => 'boolean',
        'archived' => 'boolean'
    ];

    public function companions()
    {
        return $this->hasMany(Companion::class);
    }

    public function submissionAnswers()
    {
        return $this->hasMany(SubmissionAnswer::class);
    }

    public function paymentReceipts()
    {
        return $this->hasMany(PaymentReceipt::class);
    }

    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('archived', true);
    }

    public function scopeByPackageType($query, $packageType)
    {
        return $query->where('package_type', $packageType);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('email', 'like', "%{$search}%")
              ->orWhere('lead_guest', 'like', "%{$search}%")
              ->orWhere('fb_name', 'like', "%{$search}%")
              ->orWhere('contact_number', 'like', "%{$search}%");
        });
    }

    public function scopeDateRange($query, $dateFrom, $dateTo)
    {
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        return $query;
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? Carbon::parse($this->created_at)->format('M d, Y') : null;
    }

    public function getFormattedPaymentDateAttribute()
    {
        return $this->payment_date ? $this->payment_date->format('M d, Y') : null;
    }

    public function getFormattedPaymentAmountAttribute()
    {
        return $this->payment_amount ? number_format($this->payment_amount, 2) : null;
    }

    public function archive()
    {
        $this->update(['archived' => true]);
    }

    public function restore()
    {
        $this->update(['archived' => false]);
    }
}