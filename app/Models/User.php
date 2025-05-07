<?php

namespace App\Models;

 use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
 use Illuminate\Http\RedirectResponse;
 use Illuminate\Notifications\Notifiable;
 use Illuminate\Support\Collection;
 use Illuminate\Support\Facades\App;
 use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property ?string $stripe_id
 * @property string $role
 * @property string $locale
 */
class User extends Authenticatable implements HasLocalePreference, MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

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
     * @throws ApiErrorException
     */
    public function createAsStripeCustomer(): string
    {
        if ($this->hasStripeId()) {
            return $this->stripe_id;
        }

        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $customer = $stripe->customers->create([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->stripe_id = $customer->id;

        $this->save();

        return $this->stripe_id;
    }

    public function hasStripeId(): bool
    {
        return ! is_null($this->stripe_id);
    }

    public function isHost(): bool
    {
        return $this->role === 'host';
    }

    public function isGuest(): bool
    {
        return $this->role === 'guest';
    }

    public function preferredLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @throws ApiErrorException
     */
    public function redirectToBillingPortal($returnUrl = null, array $options = []): RedirectResponse
    {
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        if (! $this->hasStripeId()) {
            throw new \RuntimeException(class_basename($this).' is not a Stripe customer yet. See the createAsStripeCustomer method.');
        }

        $url = $stripe->billingPortal->sessions->create([
            'customer' => $this->stripe_id,
            'return_url' => $returnUrl ?? route('home'),
        ], $options)['url'];

        return redirect($url);
    }

    /**
     * @throws ApiErrorException
     */
    public function paymentMethods(?string $type = null, $parameters = []): array
    {
        if (! $this->hasStripeId()) return [];

        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $parameters = array_merge(['limit' => 24], $parameters);

        $paymentMethods = $stripe->paymentMethods->all(
            array_filter(['customer' => $this->stripe_id, 'type' => $type]) + $parameters
        );

        return $paymentMethods->data;
    }
}
