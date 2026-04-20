<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-ctp-red border border-transparent rounded-lg font-medium text-sm text-night hover:bg-ctp-maroon active:bg-ctp-red focus:outline-none focus:ring-2 focus:ring-ctp-red focus:ring-offset-2 focus:ring-offset-night disabled:cursor-not-allowed disabled:opacity-60 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
