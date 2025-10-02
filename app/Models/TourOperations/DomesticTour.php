<?php

namespace App\Models\TourOperations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomesticTour extends Model
{
    use HasFactory;

    protected $connection = 'vantripper_invoice';
    protected $table = 'domestic_tours';
    public $timestamps = false;

    protected $fillable = [
        'travel_dates',
        'destination',
        'days',
        'pax',
        'lead_guest',
        'contact',
        'pickup_details',
        'balance',
        'payment_status',
        'accommodation',
        'booked_accommodation',
        'coordinated_with_supplier',
        'hotel_balance',
        'transfer_details_sent',
        'handled_by',
        'status',
        'notes'
    ];

    protected $casts = [
        'days' => 'integer',
        'pax' => 'integer',
        'balance' => 'decimal:2',
        'hotel_balance' => 'decimal:2',
        'booked_accommodation' => 'boolean',
        'coordinated_with_supplier' => 'boolean',
        'transfer_details_sent' => 'boolean',
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

    public function scopeByHandledBy($query, $handler)
    {
        return $query->where('handled_by', $handler);
    }

    public function scopeAccommodationBooked($query)
    {
        return $query->where('booked_accommodation', true);
    }

    public function scopeCoordinatedWithSupplier($query)
    {
        return $query->where('coordinated_with_supplier', true);
    }

    public function scopeTransferDetailsSent($query)
    {
        return $query->where('transfer_details_sent', true);
    }

    public function getFormattedBalanceAttribute()
    {
        return $this->balance ? number_format($this->balance, 2) : null;
    }

    public function getFormattedHotelBalanceAttribute()
    {
        return $this->hotel_balance ? number_format($this->hotel_balance, 2) : null;
    }

    public function getPaymentStatusDisplayAttribute()
    {
        return match($this->payment_status) {
            'Partially Paid' => 'Partially Paid',
            'Fully Paid' => 'Fully Paid',
            default => $this->payment_status
        };
    }

    public function getStatusDisplayAttribute()
    {
        return ucfirst($this->status);
    }

    public function getCoordinatedWithSupplierDisplayAttribute()
    {
        return $this->coordinated_with_supplier ? 'Yes' : 'No';
    }
}