<div class="grid grid-cols-2 gap-4">
    <div>
        <x-input-label>{{ __('First name') }} *</x-input-label>
        <x-text-input name="first_name" required />
    </div>

    <div>
        <x-input-label>{{ __('Last name') }} *</x-input-label>
        <x-text-input name="last_name" required />
    </div>
</div>

<div>
    <x-input-label>{{ __('Email') }} *</x-input-label>
    <x-text-input name="email" required type="email" />
</div>

<div>
    <x-input-label>{{ __('Message') }}</x-input-label>
    <x-textarea name="message" placeholder="{{ __('Hi! I would like to book the farmhouse for the dates indicated.') }}"></x-textarea>
</div>

<div>
    <x-primary-button class="mt-2">{{ __('Ask to book') }}</x-primary-button>
</div>
