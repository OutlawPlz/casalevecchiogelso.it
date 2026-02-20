<?php

namespace App\Models;

use App\Enums\CancellationPolicy;
use App\Enums\ReservationStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $ulid
 * @property int $user_id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property int $guest_count
 * @property string $summary
 * @property ReservationStatus $status
 * @property CancellationPolicy $cancellation_policy
 * @property array<int, string>|null $visited_at
 * @property CarbonImmutable|null $replied_at
 * @property string|null $payment_intent
 * @property array{id:string,url:string,expires_at:int}|null $checkout_session
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $check_in
 * @property CarbonImmutable $check_out
 * @property string|null $payout
 * @property array{product: string, name: string, description: string, price: string, unit_amount: int, quantity: int}[] $price_list
 * @property-read Collection<ChangeRequest> $changeRequests
 * @property-read User $user
 * @property-read Collection<Message> $messages
 * @property-read CarbonImmutable[] $refundPeriod
 * @property-read CarbonImmutable $due_date
 * @property-read Collection<Payment> $payments
 * @property-read int $nights
 * @property-read CarbonImmutable[] $reservedPeriod
 * @property-read CarbonImmutable[] $checkInPreparationTime
 * @property-read CarbonImmutable[] $checkOutPreparationTime
 * @property-read int $tot
 */
class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;

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
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'visited_at' => 'array',
            'replied_at' => 'immutable_datetime',
            'cancellation_policy' => CancellationPolicy::class,
            'checkout_session' => 'array',
            'due_date' => 'immutable_datetime',
            'check_in' => 'immutable_datetime',
            'check_out' => 'immutable_datetime',
            'price_list' => 'array',
        ];
    }

    protected function refundPeriod(): Attribute
    {
        $timeWindow = $this->cancellation_policy->timeWindow();

        return Attribute::make(
            get: fn () => [
                $this->check_in->sub($timeWindow),
                $this->check_in,
            ]
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function inStatus(ReservationStatus ...$status): bool
    {
        return in_array($this->status, $status);
    }

    public function hasNewMessageFor(User $user): bool
    {
        if (! $this->visited_at || ! $this->replied_at) {
            return false;
        }

        // The given user has never visited the reservation...
        if (! array_key_exists($user->email, $this->visited_at)) {
            return false;
        }

        $visitedAt = new CarbonImmutable($this->visited_at[$user->email]);

        return $this->replied_at->greaterThan($visitedAt);
    }

    public function visitedBy(User $user): self
    {
        $visitedAt = $this->visited_at ?? [];

        $visitedAt[$user->email] = now()->toDateTimeString();

        $this->visited_at = $visitedAt;

        return $this;
    }

    public function repliedAt(?CarbonImmutable $dateTime = null): self
    {
        $this->replied_at = $dateTime ?? now();

        return $this;
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(ChangeRequest::class);
    }

    public function apply(ChangeRequest $changeRequest): self
    {
        $this->fill($changeRequest->to);

        return $this;
    }

    public function hasBeenPaid(): bool
    {
        return $this->amountPaid() === $this->tot;
    }

    public function amountPaid(): int
    {
        return $this->payments->reduce(
            fn ($tot, Payment $payment) => $tot + $payment->amountPaid,
            0
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'reservation_ulid', 'ulid');
    }

    protected function nights(): Attribute
    {
        return Attribute::make(
            get: function () {
                return date_diff($this->check_in, $this->check_out)->days;
            }
        );
    }

    protected function reservedPeriod(): Attribute
    {
        return Attribute::make(
            get: fn () => [$this->check_in, $this->check_out]
        );
    }

    protected function checkInPreparationTime(): Attribute
    {
        return Attribute::make(
            get: function () {
                $preparationTime = config('reservation.preparation_time');

                if (! $preparationTime) {
                    return [];
                }

                return [
                    $this->check_in->sub($preparationTime),
                    $this->check_in,
                ];
            }
        );
    }

    protected function checkOutPreparationTime(): Attribute
    {
        return Attribute::make(
            get: function () {
                $preparationTime = config('reservation.preparation_time');

                if (! $preparationTime) {
                    return [];
                }

                return [
                    $this->check_out,
                    $this->check_out->add($preparationTime),
                ];
            }
        );
    }

    public function inProgress(): bool
    {
        return now()->between($this->check_in, $this->check_out);
    }

    public function toLineItems(): array
    {
        $order = [];

        foreach ($this->price_list as $line) {
            $order[] = [
                'price' => $line['price'],
                'quantity' => $line['quantity'],
            ];
        }

        return $order;
    }

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
}
