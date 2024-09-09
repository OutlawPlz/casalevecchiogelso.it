<?php

namespace App\Models;

use App\Casts\AsDateInterval;
use App\Enums\ReservationStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use function App\Helpers\is_overnight_stay;

/**
 * @property-read int $id
 * @property string $ulid
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property int $guest_count
 * @property CarbonImmutable $check_in
 * @property CarbonImmutable $check_out
 * @property \DateInterval|null $preparation_time
 * @property string $summary
 * @property array{product: string, name: string, description: string, price: string, unit_amount: int, quantity: int}[] $price_list
 * @property ReservationStatus $status
 * @property array<int, string>|null $visited_at
 * @property CarbonImmutable|null $replied_at
 * @property string|null $payment_intent
 * @property-read int $tot
 * @property-read User $user
 * @property-read Collection<Message> $messages
 * @property-read int $nights
 * @property-read CarbonImmutable[] $reservedPeriod
 * @property-read CarbonImmutable[] $checkInPreparationTime
 * @property-read CarbonImmutable[] $checkOutPreparationTime
 */
class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'user_id',
        'name',
        'email',
        'phone',
        'guest_count',
        'check_in',
        'check_out',
        'preparation_time',
        'price_list',
        'summary',
        'status',
        'visited_at',
        'replied_at',
        'payment_intent',
    ];

    /**
     * @param  array  $attributes
     */
    final public function __construct(array $attributes = [])
    {
        $today = today()->format('Y-m-d');

        $this->attributes = [
            'check_in' => $today,
            'check_out' => $today,
            'guest_count' => 1,
        ];

        parent::__construct($attributes);
    }

    /**
     * @return Attribute
     */
    protected function nights(): Attribute
    {
        return Attribute::make(
            get: function () {
                return date_diff($this->check_in, $this->check_out)->d;
            }
        );
    }

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'check_in' => 'immutable_date',
            'check_out' => 'immutable_date',
            'preparation_time' => AsDateInterval::class,
            'price_list' => 'array',
            'status' => ReservationStatus::class,
            'visited_at' => 'array',
            'replied_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return Attribute
     */
    protected function reservedPeriod(): Attribute
    {
        return Attribute::make(
            get: fn () => [$this->check_in, $this->check_out]
        );
    }

    /**
     * @return array
     */
    public function order(): array
    {
        $order = [];

        foreach ($this->price_list as $line) {
            $order[] = [
                'price' => $line['price'],
                'quantity' => is_overnight_stay($line['product']) ? $this->nights : 1
            ];
        }

        return $order;
    }

    /**
     * @return void
     */
    public function toSession(): void
    {
        foreach (['check_in', 'check_out', 'guest_count'] as $attribute) {
            Session::put("reservation.$attribute", $this->$attribute);
        }
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return Attribute
     */
    protected function checkInPreparationTime(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->preparation_time) return [];

                return [
                    $this->check_in->sub($this->preparation_time),
                    $this->check_in
                ];
            }
        );
    }

    /**
     * @return Attribute
     */
    protected function checkOutPreparationTime(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->preparation_time) return [];

                return [
                    $this->check_out,
                    $this->check_out->add($this->preparation_time)
                ];
            }
        );
    }

    /**
     * @return Attribute
     */
    protected function tot(): Attribute
    {
        return Attribute::make(
            get: function () {
                return array_reduce(
                    $this->price_list,
                    fn ($tot, $line) => $tot + ($line['unit_amount'] * $line['quantity']),
                    0
                );
            }
        );
    }

    /**
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * @param  string|ReservationStatus  $status
     * @return bool
     */
    public function inStatus(string|ReservationStatus $status): bool
    {
        if (is_string($status)) {
            $status = ReservationStatus::from($status);
        }

        return $this->status === $status;
    }

    /**
     * @param  User  $user
     * @return bool
     */
    public function hasNewMessageFor(User $user): bool
    {
        if (! $this->visited_at || ! $this->replied_at) return false;
        // Given user has never visited the reservation...
        if (! array_key_exists($user->email, $this->visited_at)) return false;

        $visitedAt = new CarbonImmutable($this->visited_at[$user->email]);

        return $this->replied_at->greaterThan($visitedAt);
    }
}
