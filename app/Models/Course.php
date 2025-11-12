<?php

namespace App\Models;

use App\Enums\CourseAccreditingBody;
use App\Enums\CourseDuration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'name',
        'accrediting_body',
        'description',
        'duration',
        'validity_period',
        'course_category_id',
    ];

    protected function casts(): array
    {
        return [
            'validity_period' => 'integer',
            'duration' => CourseDuration::class,
            'accrediting_body' => CourseAccreditingBody::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }
}

