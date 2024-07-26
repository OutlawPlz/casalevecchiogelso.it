<?php

namespace App\Models;

use App\Services\GoogleTranslate;
use Carbon\CarbonImmutable;
use Google\Cloud\Core\Exception\ServiceException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * @property ?int $user_id
 * @property ?int $reservation_id
 * @property string $channel ULID
 * @property ?array $author
 * @property ?array{content: string} $data
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property array $content
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

    public array $translatable = [
        'content',
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

    /**
     * @param  array  $data
     * @param  string  $language
     * @return string
     */
    public function renderContent(array $data = [], string $language = ''): string
    {
        $rawContent = $this->content['raw'];

        $isTemplate = str_starts_with($rawContent, '/blade');

        if ($isTemplate) {
            $template = explode(':', $rawContent, 2)[1] ?? '';

            if (! $template) return '';

            return view("messages.$template", $data)->render();
        }

        $content = Str::markdown($rawContent, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        if ($language && ! array_key_exists($language, $this->content)) {
            /** @var GoogleTranslate $translator */
            $translator = App::make(GoogleTranslate::class);

            try {
                $translation = $translator->translate($rawContent, ['target' => $language])[0]['text'];

//                $this->update(["content->$language" => $translation]);

                $content = $translation;
            } catch (ServiceException $exception) {
                report($exception);
            }
        }

        return $content;
    }
}
