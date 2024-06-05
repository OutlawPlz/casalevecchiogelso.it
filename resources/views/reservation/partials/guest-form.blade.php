<div class="grid grid-cols-2 gap-4">
    <div>
        <x-input-label>{{ __('First name') }} *</x-input-label>
        <x-text-input name="first_name" required value="{{ old('first_name') }}" />
        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label>{{ __('Last name') }} *</x-input-label>
        <x-text-input name="last_name" required value="{{ old('last_name') }}"/>
        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
    </div>
</div>

<div>
    <x-input-label>{{ __('Email') }} *</x-input-label>
    <x-text-input name="email" required type="email" value="{{ old('email') }}" />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<div>
    <x-input-label>{{ __('Message') }}</x-input-label>
    <x-textarea name="message" placeholder="{{ __('Hi! I would like to book the farmhouse for the dates indicated.') }}">{{ old('message') }}</x-textarea>
    <x-input-error :messages="$errors->get('message')" class="mt-2" />
</div>

<div>
    <x-primary-button class="mt-2">{{ __('Ask to book') }}</x-primary-button>
</div>
