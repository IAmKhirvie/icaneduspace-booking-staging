@props(['value'])

<label {{ $attributes->merge(['class' => 'block mb-1 auth-label']) }}>
    {{ $value ?? $slot }}
</label>
