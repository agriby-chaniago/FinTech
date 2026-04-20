@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-byzantine text-sm font-medium leading-5 text-platinum focus:outline-none focus:border-byzantine-light transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-platinum/75 hover:text-platinum hover:border-raisin3 focus:outline-none focus:text-platinum focus:border-raisin3 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
