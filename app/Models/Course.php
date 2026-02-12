<?php

namespace App\Models;

use App\Enums\CourseAccreditingBody;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'accrediting_body',
        'description',
        'validity_period',
        'course_category_id',
    ];

    protected function casts(): array
    {
        return [
            'validity_period' => 'integer',
            'accrediting_body' => CourseAccreditingBody::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }


    public function variables(): HasMany
    {
        return $this->hasMany(CourseVariable::class);
    }

    // Alias for Filament's default inverse naming on AttachAction
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}


