<?php

namespace App\Models;

use App\Enums\ChangeRequestStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ChangeRequestFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $ulid
 * @property int $reservation_id
 * @property int $user_id
 * @property array{check_in:string,check_out:string,guest_count:int,price_list:array} $from
 * @property array{check_in:string,check_out:string,guest_count:int,price_list:array} $to
 * @property array{id:string,url:string,expires_at:int}|null $checkout_session
 * @property ChangeRequestStatus $status
 * @property-read Reservation $toReservation
 * @property-read Reservation $fromReservation
 * @property-read Reservation $reservation
 * @property-read User $user
 * @property CarbonImmutable $created_at
 */
class ChangeRequest extends Model
{
    /** @use HasFactory<ChangeRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'ulid',
        'reservation_id',
        'user_id',
        'from',
        'to',
        'checkout_session',
        'status',
        'reason',
    ];

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'checkout_session' => 'array',
            'status' => ChangeRequestStatus::class,
            'from' => 'array',
            'to' => 'array',
        ];
    }

    final public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inStatus(ChangeRequestStatus ...$status): bool
    {
        return in_array($this->status, $status);
    }

    protected function fromReservation(): Attribute
    {
        return Attribute::make(
            get: fn () => new Reservation($this->from)
        );
    }

    protected function toReservation(): Attribute
    {
        return Attribute::make(
            get: fn () => new Reservation($this->to)
        );
    }

    public function priceDifference(): int
    {
        return $this->toReservation->tot - $this->fromReservation->tot;
    }

    public static function for(Reservation $reservation): static
    {
        return new static([
            'ulid' => Str::ulid(),
            'reservation_id' => $reservation->id,
            'from' => [
                'check_in' => $reservation->check_in,
                'check_out' => $reservation->check_out,
                'guest_count' => $reservation->guest_count,
                'price_list' => $reservation->price_list,
            ]
        ]);
    }

    public function toLineItems(): array
    {
        return [
            'quantity' => 1,
            'price_data' => [
                'unit_amount' => $this->priceDifference(),
                'currency' => config('services.stripe.currency'),
                'product_data' => ['name' => 'Change Request'],
            ],
        ];
    }
}
