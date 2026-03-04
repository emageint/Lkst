<?php

namespace App\Models;

use App\Observers\HolidayObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(HolidayObserver::class)]
class Holiday extends Model
{
    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'note',
    ];


    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
