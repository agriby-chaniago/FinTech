@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-ctp-green']) }}>
        {{ $status }}
    </div>
@endif
