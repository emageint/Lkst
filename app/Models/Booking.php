<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'course_id',
        'customer_id',
        'instructor_id',
        'job_datetime',
        'training_location_line1',
        'training_location_line2',
        'training_location_line3',
        'training_location_city',
        'training_location_postcode',
        'number_of_learners',
        'notes',
        'status',
    ];

    protected $casts = [
        'number_of_learners' => 'integer',
        'status' => BookingStatus::class,
    ];


    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
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
