<?php

namespace App\Models;

use App\Enums\CourseAccreditingBody;
use App\Enums\CourseDuration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function learners(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    // Alias for Filament's default inverse naming on AttachAction
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}


