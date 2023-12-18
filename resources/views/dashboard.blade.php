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
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                        <div class="md:col-span-2">
                            <x-input-label for="check_in" :value="__('Check-in')" />
                            <x-text-input type="date" id="check_in" name="check_in" required />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="check_out" :value="__('Check-out')" />
                            <x-text-input type="date" name="check_out" id="check_out" required />
                        </div>

                        <div>
                            <x-input-label for="guests_count" :value="__('Guests')" />
                            <x-text-input type="number" name="guests_count" id="guests_count" value="1" min="1" max="10" required />
                        </div>
                    </div>

                    <!--
                    <div class="mt-6">
                        <label class="block mb-1" for="datepicker">Datepicker</label>
                        <input type="text" id="datepicker">
                    </div>
                    -->
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
