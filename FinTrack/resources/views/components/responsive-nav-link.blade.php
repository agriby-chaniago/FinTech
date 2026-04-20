@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-byzantine text-start text-base font-medium text-platinum bg-raisin2 focus:outline-none focus:text-platinum focus:bg-raisin2 focus:border-byzantine-light transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-platinum/75 hover:text-platinum hover:bg-raisin hover:border-raisin3 focus:outline-none focus:text-platinum focus:bg-raisin focus:border-raisin3 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
