<?php

namespace App\Models;

use App\Enums\CancellationPolicy;
use App\Enums\ReservationStatus;
use App\Traits\HasPriceList;
use App\Traits\HasStartEndDates;
use Carbon\CarbonImmutable;
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
 * @property array $payment_intents
 * @property array{id:string,url:string,expires_at:int}|null $checkout_session
 * @property-read Collection<ChangeRequest> $changeRequests
 * @property-read User $user
 * @property-read Collection<Message> $messages
 * @property-read CarbonImmutable[] $refundPeriod
 * @property-read CarbonImmutable $dueDate
 */
class Reservation extends Model
{
    use HasFactory, HasPriceList, HasStartEndDates;

    protected $fillable = [
        'ulid',
        'user_id',
        'name',
        'email',
        'phone',
        'guest_count',
        'summary',
        'status',
        'visited_at',
        'replied_at',
        'payment_intents',
        'cancellation_policy',
        'checkout_session',
    ];

    final public function __construct(array $attributes = [])
    {
        $today = today()->format('Y-m-d');

        $this->attributes = [
            'check_in' => $today,
            'check_out' => $today,
            'guest_count' => 1,
            'payment_intents' => '[]',
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
            'payment_intents' => 'array',
        ];
    }

    protected function refundPeriod(): Attribute
    {
        $timeWindow = $this->cancellation_policy->timeWindow();

        return Attribute::make(
            get: fn () => [
                $this->check_in->sub($timeWindow) ,
                $this->check_in
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
        if (! $this->visited_at || ! $this->replied_at) return false;
        // The given user has never visited the reservation...
        if (! array_key_exists($user->email, $this->visited_at)) return false;

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
        $this->fill([
            'check_in' => $changeRequest->check_in,
            'check_out' => $changeRequest->check_out,
            'guest_count' => $changeRequest->guest_count,
        ]);

        return $this;
    }

    protected function dueDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->check_in->sub($this->cancellation_policy->timeWindow())
        );
    }
}
