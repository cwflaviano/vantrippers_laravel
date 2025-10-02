<?php

namespace App\Models\TourOperations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VantripperINV\Customer;
use App\Models\VantripperINV\Invoice;

class CompletedTour extends Model
{
    use HasFactory;

    protected $connection = 'vantripper_invoice';
    protected $table = 'completed_tours';
    public $timestamps = false;

    protected $fillable = [
        'tour_id',
        'assigned_team',
        'followup_status',
        'tail_end',
        'completion_date',
        'notes',
        'customer_assigned',
        'invoice_no',
        'travel_dates',
        'destination',
        'tour_type',
        'days',
        'pax',
        'lead_guest'
    ];

    protected $casts = [
        'tour_id' => 'integer',
        'completion_date' => 'datetime',
        'customer_assigned' => 'boolean',
        'days' => 'integer',
        'pax' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'invoice_no', 'invoice_no');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_no', 'invoice_no');
    }

    public function scopeByTeam($query, $team)
    {
        return $query->where('assigned_team', $team);
    }

    public function scopeByFollowupStatus($query, $status)
    {
        return $query->where('followup_status', $status);
    }

    public function scopeByTailEnd($query, $tailEnd)
    {
        return $query->where('tail_end', $tailEnd);
    }

    public function scopeByDestination($query, $destination)
    {
        return $query->where('destination', 'like', "%{$destination}%");
    }

    public function scopeByTourType($query, $tourType)
    {
        return $query->where('tour_type', $tourType);
    }

    public function scopeWithCustomer($query)
    {
        return $query->where('customer_assigned', true);
    }

    public function scopeWithoutCustomer($query)
    {
        return $query->where('customer_assigned', false);
    }

    public function getFormattedCompletionDateAttribute()
    {
        return $this->completion_date ? $this->completion_date->format('M d, Y') : null;
    }

    public function getFollowupStatusDisplayAttribute()
    {
        return match($this->followup_status) {
            '1st Follow up sent' => '1st Follow-up Sent',
            '1st Text Sent' => '1st Text Sent',
            'No Follow Up' => 'No Follow-up',
            default => $this->followup_status
        };
    }

    public function getTailEndDisplayAttribute()
    {
        return match($this->tail_end) {
            'No Review' => 'No Review',
            'With Review Posted' => 'Review Posted',
            'With Photos' => 'Photos Shared',
            'FB Feedback Only' => 'FB Feedback Only',
            'No Review, No Feedback, No Photo' => 'No Activity',
            'ALL GOOD POSTED' => 'All Good - Posted',
            default => $this->tail_end
        };
    }
}