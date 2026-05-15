<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Observers\BookingObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


#[ObservedBy(BookingObserver::class)]
class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'course_id',
        'customer_id',
        'instructor_id',
        'start',
        'end',
        'training_location_line1',
        'training_location_line2',
        'training_location_line3',
        'training_location_city',
        'training_location_postcode',
        'course_variable_type',
        'course_duration',
        'max_delegates',
        'notes',
        'status',
        'outlook_event_id',
        'delegates_submitted',
        'location_lkst_yard',
        'form_expires_at',
        'reminder_sent_at',
        'ref_number',
        'price',
        'po_number',
    ];


    protected $casts = [
        'course_duration' => 'integer',
        'max_delegates' => 'integer',
        'status' => BookingStatus::class,
        'start' => 'datetime',
        'end' => 'datetime',
        'delegates_submitted' => 'boolean',
        'location_lkst_yard' => 'boolean',
        'form_expires_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];


    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class)->withTrashed();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function delegates(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'booking_user')->withTimestamps();
    }


    public function getTrainingLocationAttribute(): string
    {
        $address = [
            $this->training_location_line1,
            $this->training_location_line2,
            $this->training_location_line3,
            $this->training_location_city,
            $this->training_location_postcode,
        ];

        return implode(", ", array_filter($address));
    }
}
