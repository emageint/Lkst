<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseVariable extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'type',
        'course_duration',
        'max_delegates',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
