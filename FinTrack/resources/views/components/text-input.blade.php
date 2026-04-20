@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-raisin3 bg-raisin text-platinum placeholder:text-ctp-overlay1 focus:border-byzantine focus:ring-byzantine rounded-md shadow-sm']) }}>
