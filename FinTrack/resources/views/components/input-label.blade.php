@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-platinum/85']) }}>
    {{ $value ?? $slot }}
</label>
