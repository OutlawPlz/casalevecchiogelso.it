@php
/**
 * @var \App\Models\Reservation $reservation
 */
@endphp

<div class="space-y-2">
    @foreach($reservation->price_list as $line)
        <div class="flex justify-between">
            @if($loop->first)
                <span class="underline">@money($line['unit_amount']) x {{ $line['quantity'] }} {{ __('nights') }}</span>
                <span>@money($line['quantity'] * $line['unit_amount'])</span>
            @else
                <span class="underline">
                    {{ __($line['name']) }}
                    @if($line['quantity'] > 1)
                    x {{ $line['quantity'] }}
                    @endif
                </span>
                <span>@money($line['unit_amount'] * $line['quantity'])</span>
            @endif
        </div>
    @endforeach
</div>

<hr>

<div class="flex justify-between font-bold text-lg">
    <span>Tot.</span>
    <span x-text="$({{ $reservation->tot }})"></span>
</div>
