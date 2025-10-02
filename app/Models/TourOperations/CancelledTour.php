<?php

namespace App\Models\TourOperations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancelledTour extends Model
{
    use HasFactory;

    protected $connection = 'vantripper_invoice';
    protected $table = 'cancelled_tours';
    public $timestamps = false;

    protected $fillable = [
        'tour_id',
        'cancellation_person',
        'cancellation_reason',
        'refund_status',
        'cancellation_date',
        'days',
        'pax',
        'with_coordinator',
        'pickup_point',
        'balance',
        'payment_status',
        'accommodation',
        'room_setup',
        'booked_accommodation',
        'van_details_sent',
        'assigned_team',
        'status',
        'notes',
        'lead_guest',
        'contact',
        'destination'
    ];

    protected $casts = [
        'tour_id' => 'integer',
        'cancellation_date' => 'datetime',
        'days' => 'integer',
        'pax' => 'integer',
        'balance' => 'decimal:2',
        'booked_accommodation' => 'boolean',
        'van_details_sent' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function scopeByRefundStatus($query, $status)
    {
        return $query->where('refund_status', $status);
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

    public function scopeByDestination($query, $destination)
    {
        return $query->where('destination', 'like', "%{$destination}%");
    }

    public function scopeByTeam($query, $team)
    {
        return $query->where('assigned_team', $team);
    }

    public function getFormattedCancellationDateAttribute()
    {
        return $this->cancellation_date ? $this->cancellation_date->format('M d, Y') : null;
    }

    public function getFormattedBalanceAttribute()
    {
        return $this->balance ? number_format($this->balance, 2) : null;
    }

    public function getRefundStatusDisplayAttribute()
    {
        return match($this->refund_status) {
            'Pending' => 'Pending',
            'Processing' => 'Processing',
            'Completed' => 'Completed',
            'Not Applicable' => 'Not Applicable',
            default => $this->refund_status
        };
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
}