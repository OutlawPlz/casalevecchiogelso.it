<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mt-6 grid grid-cols-3 gap-6" id="daterange">
                        <div>
                            <label class="block mb-1">Check-in</label>
                            <input class="rounded w-full" type="text" name="check_in">
                        </div>

                        <div>
                            <label class="block mb-1">Check-out</label>
                            <input class="rounded w-full" type="text" name="check_out">
                        </div>

                        <div>
                            <label class="block mb-1">Guests</label>
                            <input class="rounded w-full" type="number" name="guest_count" min="1" value="1">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
