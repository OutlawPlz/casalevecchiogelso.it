<?php

namespace App\Models;

use App\Services\GoogleTranslate;
use Carbon\CarbonImmutable;
use Google\Cloud\Core\Exception\ServiceException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property ?int $user_id
 * @property ?int $reservation_id
 * @property string $channel ULID
 * @property ?array $author
 * @property ?array{content: string} $data
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property ?array $content
 * @property ?array $media
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
        'content',
        'media',
        'locale',
    ];

    /**
     * @return array
     */
    protected function casts(): array
    {
        return [
            'author' => 'array',
            'content' => 'array',
            'media' => 'array',
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
