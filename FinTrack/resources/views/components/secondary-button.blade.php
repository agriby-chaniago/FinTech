<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-raisin2 border border-raisin3 rounded-lg font-medium text-sm text-platinum shadow-sm hover:bg-raisin focus:outline-none focus:ring-2 focus:ring-byzantine focus:ring-offset-2 focus:ring-offset-night disabled:cursor-not-allowed disabled:opacity-60 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
