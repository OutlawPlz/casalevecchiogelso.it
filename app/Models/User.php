<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property ?string $stripe_id
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return HasMany
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return string
     * @throws ApiErrorException
     */
    public function createAsStripeCustomer(): string
    {
        if ($this->hasStripeId()) {
            return $this->stripe_id;
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        $customer = $stripe->customers->create([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->stripe_id = $customer->id;

        $this->save();

        return $this->stripe_id;
    }

    /**
     * @return bool
     */
    public function hasStripeId(): bool
    {
        return ! is_null($this->stripe_id);
    }
}
