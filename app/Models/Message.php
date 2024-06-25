<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ?int $user_id
 * @property ?int $reservation_id
 * @property string $channel ULID
 * @property ?array $author
 * @property ?array $data
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read ?User $user
 * @property-read ?Reservation $reservation
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reservation_id',
        'channel',
        'author',
        'data',
    ];

    /**
     * @return array
     */
    protected function casts(): array
    {
        return [
            'author' => 'array',
            'data' => 'array',
        ];
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
