<?php

namespace App\Support;

class BookingMiscData
{
    /**
     * Decide whether the submitted data represents a Miscellaneous booking.
     */
    public static function isMiscellaneous(array $data): bool
    {
        return ($data['booking_mode'] ?? 'course') === 'misc';
    }

    /**
     * Clean up data before saving: null out irrelevant fields based on booking mode.
     */
    public static function forSave(array $data): array
    {
        if (static::isMiscellaneous($data)) {
            $data['course_id'] = null;
            $data['course_variable_type'] = null;
            $data['course_duration'] = null;
            $data['max_delegates'] = null;
            $data['customer_id'] = null;
            $data['price'] = null;
            $data['status'] = 'confirmed';
        } else {
            $data['title'] = null;
            $data['description'] = null;
        }

        return $data;
    }
}
