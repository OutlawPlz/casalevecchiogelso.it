<?php

use App\Models\Product;
use App\Services\Calendar;
use App\View\Components\ReservationQuote;

beforeEach(function () {
    Product::factory()->overnightStay()->create();

    $this->mock(Calendar::class)
        ->shouldReceive('unavailableDates')
        ->andReturn([]);
});

test('renders with unavailable dates', function () {
    $this->mock(Calendar::class)
        ->shouldReceive('unavailableDates')
        ->andReturn(['2026-06-02', '2026-06-03']);

    $this->component(ReservationQuote::class)
        ->assertSee('2026-06-02')
        ->assertSee('Overnight Stay');
});

test('renders without unavailable dates', function () {
    $this->component(ReservationQuote::class)
        ->assertSee('Overnight Stay');
});

test('renders additional price list items in breakdown', function () {
    Product::factory()->create(['name' => 'Cleaning Fee']);

    $this->component(ReservationQuote::class)
        ->assertSee('Cleaning Fee');
});
