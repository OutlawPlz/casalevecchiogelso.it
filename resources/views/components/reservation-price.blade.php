@php
/**
 * @var \App\Models\Reservation $reservation
 */
@endphp

<div class="space-y-2">
    @foreach($reservation->price_list as $line)
        <div class="flex justify-between" x-data>
            @if($loop->first)
                <div class="underline">
                    <span x-currency="{{ $line['unit_amount'] }}"></span> x {{ $line['quantity'] }} {{ __('nights') }}
                </div>
                <div>
                    <span x-currency="{{ $line['quantity'] * $line['unit_amount'] }}"></span>
                </div>
            @else
                <span class="underline">
                    {{ __($line['name']) }}
                    @if($line['quantity'] > 1)
                    x {{ $line['quantity'] }}
                    @endif
                </span>
                <span x-currency="{{ $line['quantity'] * $line['unit_amount'] }}"></span>
            @endif
        </div>
    @endforeach
</div>

<hr>

<div class="flex justify-between font-bold text-lg">
    <span>Tot.</span>
    <span x-currency="{{ $reservation->tot }}"></span>
</div>
