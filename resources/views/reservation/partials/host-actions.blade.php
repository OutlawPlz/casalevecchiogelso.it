@php
    /**
     * @var App\Models\User $authUser
     * @var App\Models\Reservation $reservation
     */
@endphp

@use('\App\Enums\ReservationStatus as Status')

<div class="flex flex-col gap-3">
    @switch($reservation->status)
        @case(Status::QUOTE)
            <button
                x-data x-on:click.prevent="$dispatch('open-modal', 'pre-approve')"
                type="button"
                class="clear hover:underline flex items-center gap-2 cursor-pointer"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ __('Pre-approve') }}</span>
            </button>

            <x-modal name="pre-approve" class="max-w-xl">
                <form
                    action="{{ route('reservation.status', [$reservation]) }}"
                    class="p-6"
                    method="POST"
                >
                    @csrf
                    <div>
                        <h2 class="font-semibold text-xl text-zinc-900">{{ __('Pre-approve') }}</h2>

                        <p class="mt-2 text-zinc-500">
                            {{ __('Do you want to pre-approve the request?') }} <br>
                            {{ __('The guest will have 24 hours to confirm the reservation.') }}
                        </p>

                        <div class="border rounded-lg py-2.5 mt-6 px-4">
                            <div>
                                <span class="font-bold">{{ $reservation->user->name }}</span>
                                <span class="tracking-wider text-zinc-600 uppercase pl-1 text-xs">{{ $reservation->status }}</span>
                            </div>

                            <div class="text-zinc-600">
                                {{ $reservation->check_in->format('d M') }} - {{ $reservation->check_out->format('d M') }} ({{ $reservation->nights }} {{ __('nights') }}) <br>
                                {{ $reservation->guest_count }} {{ __('guests') }} • Tot. <span x-currency="{{ $reservation->tot }}"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 space-x-3 flex justify-end">
                        <button
                            class="ghost"
                            x-on:click="$dispatch('close')"
                            type="button"
                        >
                            {{ __('Close') }}
                        </button>

                        <button
                            class="primary"
                            value="{{ Status::PENDING }}"
                            name="status"
                        >
                            {{ __('Pre-approve') }}
                        </button>
                    </div>
                </form>
            </x-modal>

            <button
                x-data x-on:click.prevent="$dispatch('open-modal', 'reject')"
                type="clear"
                class="clear hover:underline flex items-center gap-2 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
                <span>{{ __('Reject the booking') }}</span>
            </button>

            <x-modal name="reject" class="max-w-xl">
                <form
                    action="{{ route('reservation.status', [$reservation]) }}"
                    class="p-6"
                    method="POST"
                >
                    @csrf
                    <div>
                        <h2 class="text-lg font-bold text-zinc-900">{{ __('Reject') }}</h2>
                        <p class="mt-1 text-zinc-600">{{ __('Are you sure you want to decline this booking?') }}</p>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            class="ghost"
                            x-on:click="$dispatch('close')"
                            type="button"
                        >
                            {{ __('Close') }}
                        </button>

                        <button
                            class="primary"
                            value="{{ Status::REJECTED }}"
                            name="status"
                        >
                            {{ __('Reject') }}
                        </button>
                    </div>
                </form>
            </x-modal>

            @break

        @case(Status::CONFIRMED)
            <a
                class="hover:underline flex items-center gap-2"
                href="{{ route('change_request.create', [$reservation]) }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                </svg>
                <span>{{ __('Change the booking') }}</span>
            </a>

            <a
                href="{{ route('reservation.delete', [$reservation]) }}"
                class="hover:underline flex items-center gap-2"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ __('Cancel the booking') }}</span>
            </a>

            @break
    @endswitch
</div>
