<x-app-layout>
    <div class="px-8">
        <section class="py-8 grid grid-cols-5 grid-rows-5 gap-6">
            <div class="rounded-xl bg-gray-100 col-span-3 row-span-3 mt-4"></div>
            <div class="rounded-xl bg-gray-100 row-span-2 aspect-square ml-12"></div>
            <div class="rounded-xl bg-gray-100 row-span-2 aspect-square mt-8"></div>
            <div class="rounded-xl bg-gray-100 col-span-2 row-span-3 mr-12"></div>
            <div class="rounded-xl bg-gray-100 col-span-2 row-span-2 ml-24"></div>
        </section>

        <section class="grid grid-cols-2 gap-16 max-w-screen-lg mx-auto mt-16 pb-16">
            <div class="prose">
                <h1 class="text-balance text-6xl">Casale al Vecchio Gelso</h1>
                <p class="text-lg leading-8 -mt-4">{{ __('Antico casale di fine ‘800 situato nelle campagne del Monferrato, è stato ristrutturato completamente nel 2018. Dispone di un ampio salone, cucina attrezzata, due bagni, tavernetta e cinque camere da letto. Il casale è in una posizione strategica per visitare le Langhe e il Monferrato. Asti è a soli 15 km di distanza; mentre Alba, Acqui Terme e Barolo sono rispettivamente a 35, 40 e 45 min. di macchina. Anche Torino non è lontana, potrete raggiungerla in un’ora di macchina.') }}</p>
            </div>

            <div class="ms-8 p-8 rounded-xl shadow-xl self-start">
                @include('reservation.partials.reservation-form')
            </div>
        </section>
    </div>
</x-app-layout>
