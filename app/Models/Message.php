<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property ?int $user_id
 * @property ?int $reservation_id
 * @property string $channel ULID
 * @property ?array $author
 * @property ?array{content: string} $data
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

    protected array $messageActions = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

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

    /**
     * @param  array  $data
     * @return string
     */
    public function renderContent(array $data): string
    {
        $content = $this->data['content'];

        $isTemplate = str_starts_with($content, '/blade');

        if (! $isTemplate) {
            return Str::markdown($content, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
        }

        $template = explode(':', $content, 2)[1] ?? '';

        if (! $template) return '';

        return view("messages.$template", $data)->render();
    }
}
