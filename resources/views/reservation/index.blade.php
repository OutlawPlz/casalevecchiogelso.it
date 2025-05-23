@php
/**
 * @var \App\Models\User $authUser
 * @var \Illuminate\Pagination\Paginator $reservations
 * @var \App\Models\Reservation $reservation
 */
@endphp

@use('App\Enums\ReservationStatus as Status')

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-800 leading-tight">
            {{ __('Reservations') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="divide-y divide-zinc-100">
                    @foreach($reservations as $reservation)
                    <a
                        href="{{ route('reservation.show', [$reservation]) }}"
                        class="flex justify-between flex space-x-4 p-4 sm:p-6"
                    >
                        <div @class(['line-through decoration-zinc-500' => $reservation->inStatus(Status::REJECTED, Status::CANCELLED)])>
                            <h4 class="text-xl capitalize">{{ $reservation->check_in->diffForHumans() }}</h4>

                            <div class="flex flex-col md:flex-row md:space-x-4 mt-1">
                                <div class="flex items-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4 text-zinc-400">
                                        <path d="M8.5 4.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0ZM10.9 12.006c.11.542-.348.994-.9.994H2c-.553 0-1.01-.452-.902-.994a5.002 5.002 0 0 1 9.803 0ZM14.002 12h-1.59a2.556 2.556 0 0 0-.04-.29 6.476 6.476 0 0 0-1.167-2.603 3.002 3.002 0 0 1 3.633 1.911c.18.522-.283.982-.836.982ZM12 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />
                                    </svg>
                                    <span class="text-sm text-zinc-700">{{ $reservation->guest_count }} {{ __('guests') }}</span>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4 text-zinc-400">
                                        <path fill-rule="evenodd" d="M4 1.75a.75.75 0 0 1 1.5 0V3h5V1.75a.75.75 0 0 1 1.5 0V3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2V1.75ZM4.5 6a1 1 0 0 0-1 1v4.5a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1h-7Z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm text-zinc-700">{{ $reservation->check_in->format('d M') }} / {{ $reservation->check_out->format('d M Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            @if($reservation->hasNewMessageFor($authUser))
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-zinc-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-zinc-500"></span>
                            </span>
                            @endif
                            <span class="capitalize rounded-full bg-zinc-400/10 px-2 py-1 text-xs font-medium text-zinc-500 ring-1 ring-zinc-400/20">{{ $reservation->status }}</span>
                            <svg class="size-5 text-zinc-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                            </svg>
                        </div>
                </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto p-6 flex flex-col">
        <div class="divide-y mt-6">
        </div>

        {{ $reservations->links() }}
    </div>
</x-app-layout>
