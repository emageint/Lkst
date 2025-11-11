<?php

namespace App\Models;

use App\Enums\CourseAccreditingBody;
use App\Enums\CourseDuration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'accrediting_body',
        'description',
        'duration',
        'validity_period',
    ];

    protected function casts(): array
    {
        return [
            'validity_period' => 'integer',
            'duration' => CourseDuration::class,
            'accrediting_body' => CourseAccreditingBody::class,
        ];
    }
}
