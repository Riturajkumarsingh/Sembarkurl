<?php

namespace App\Models;

use App\Enums\Role;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'original_url',
        'short_code',
        'hits',
    ];

    protected function casts(): array
    {
        return [
            'hits' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Restricts the query to rows the given user is allowed to see.
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            Role::SuperAdmin => $query,
            Role::Admin => $query->where('short_urls.company_id', $user->company_id),
            Role::Member => $query->where('short_urls.user_id', $user->id),
            default => $query->whereRaw('1 = 0'),
        };
    }

    // Filters by a named calendar interval; null means no filter applied.
    public function scopeInInterval(Builder $query, ?string $interval): Builder
    {
        return match ($interval) {
            'today' => $query->whereDate('short_urls.created_at', Carbon::today()),
            'last_week' => $query->whereBetween('short_urls.created_at', [
                Carbon::now()->startOfWeek()->subWeek(),
                Carbon::now()->startOfWeek()->subSecond(),
            ]),
            'this_month' => $query->whereMonth('short_urls.created_at', Carbon::now()->month)
                ->whereYear('short_urls.created_at', Carbon::now()->year),
            'last_month' => $query->whereMonth('short_urls.created_at', Carbon::now()->subMonth()->month)
                ->whereYear('short_urls.created_at', Carbon::now()->subMonth()->year),
            default => $query,
        };
    }
}
