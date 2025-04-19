<?php

namespace App\Models;

use App\Enums\ChangeRequestStatus;
use App\Traits\HasPriceList;
use App\Traits\HasStartEndDates;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $reservation_id
 * @property CarbonImmutable $check_in
 * @property CarbonImmutable $check_out
 * @property int $guest_count
 * @property array{id:string,url:string,expires_at:int}|null $checkout_session
 * @property ChangeRequestStatus $status
 * @property-read Reservation $reservation
 */
class ChangeRequest extends Model
{
    use HasFactory, HasPriceList, HasStartEndDates;

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'check_in' => 'immutable_datetime',
            'check_out' => 'immutable_datetime',
            'checkout_session' => 'array',
            'price_list' => 'array',
            'status' => ChangeRequestStatus::class,
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function inStatus(ChangeRequestStatus ...$status): bool
    {
        return in_array($this->status, $status);
    }
}
