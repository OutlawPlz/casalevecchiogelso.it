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
                            <label for="check_in">Check-in</label>
                            <input class="rounded-md" type="date" name="check_in" id="check_in">
                        </div>

                        <div class="md:col-span-2">
                            <label for="check_out">Check-out</label>
                            <input type="date" name="check_out" id="check_out">
                        </div>

                        <div>
                            <label for="guests_count">Ospiti</label>
                            <input type="number" name="guests_count" id="guests_count" value="1" min="1" max="10">
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
