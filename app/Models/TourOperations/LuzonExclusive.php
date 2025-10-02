<?php

namespace App\Models\TourOperations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LuzonExclusive extends Model
{
    use HasFactory;

    protected $connection = 'vantripper_invoice';
    protected $table = 'luzon_exclusive';
    public $timestamps = false;

    protected $fillable = [
        'travel_dates',
        'destination',
        'days',
        'pax',
        'with_coordinator',
        'lead_guest',
        'contact',
        'pickup_point',
        'balance',
        'payment_status',
        'accommodation',
        'room_setup',
        'booked_accommodation',
        'van_details_sent',
        'assigned_team',
        'status',
        'notes'
    ];

    protected $casts = [
        'days' => 'integer',
        'pax' => 'integer',
        'balance' => 'decimal:2',
        'booked_accommodation' => 'boolean',
        'van_details_sent' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function scopeByDestination($query, $destination)
    {
        return $query->where('destination', 'like', "%{$destination}%");
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopeWithCoordinator($query)
    {
        return $query->where('with_coordinator', 'With');
    }

    public function scopeWithoutCoordinator($query)
    {
        return $query->where('with_coordinator', 'None');
    }

    public function scopeByTeam($query, $team)
    {
        return $query->where('assigned_team', $team);
    }

    public function scopeAccommodationBooked($query)
    {
        return $query->where('booked_accommodation', true);
    }

    public function scopeVanDetailsSent($query)
    {
        return $query->where('van_details_sent', true);
    }

    public function getFormattedBalanceAttribute()
    {
        return $this->balance ? number_format($this->balance, 2) : null;
    }

    public function getPaymentStatusDisplayAttribute()
    {
        return match($this->payment_status) {
            'Partially Paid' => 'Partially Paid',
            'Fully Paid' => 'Fully Paid',
            default => $this->payment_status
        };
    }

    public function getWithCoordinatorDisplayAttribute()
    {
        return $this->with_coordinator === 'With' ? 'Yes' : 'No';
    }

    public function getStatusDisplayAttribute()
    {
        return ucfirst($this->status);
    }
}