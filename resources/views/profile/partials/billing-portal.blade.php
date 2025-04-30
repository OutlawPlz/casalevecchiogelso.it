<section>
    <header>
        <h2 class="text-lg font-medium text-zinc-900">
            {{ __('Billing portal') }}
        </h2>

        <p class="mt-1 text-sm text-zinc-600">
            {{ __('Manage payment methods, and view billing history with ease.') }}
        </p>
    </header>

    <form method="POST" action="{{ route('billing_portal') }}" class="mt-6 space-y-6">
        @csrf

        <button class="primary">{{ __('Access') }}</button>
    </form>
</section>
