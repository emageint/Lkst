<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Filament\Facades\Filament;


class User extends Authenticatable implements HasName
{

    use HasFactory, Notifiable;
    use HasRoles;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'title',
        'email',
        'company_name',
        'password',
        'first_name',
        'last_name',
        'address_line1',
        'address_line2',
        'address_line3',
        'city',
        'postcode',
        'phone',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }


    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)
            ->withPivot([ 'date_completed', 'certificate_path', 'notes' ])
            ->withTimestamps();
    }

    // Audit relations
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function booted(): void
    {
        static::creating(function (User $model) {
            if (Filament::auth()->check()) {
                $userId = Filament::auth()->id();
                $model->created_by = $model->created_by ?? $userId;
                $model->updated_by = $model->updated_by ?? $userId;
            }
        });

        static::updating(function (User $model) {
            if (Filament::auth()->check()) {
                $model->updated_by = Filament::auth()->id();
            }
        });
    }


}


