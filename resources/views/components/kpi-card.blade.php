@props([
    'label',
    'value',
    'color' => 'green' // verde o rojo opcional
])

@php
    $textColor = match($color) {
        'green' => 'text-green-600',
        'red' => 'text-red-600',
        default => 'text-gray-800',
    };
@endphp

<div class="bg-white rounded-xl shadow p-5">
    <p class="text-sm text-green-500">{{ $label }}</p>
    <p class="text-2xl font-bold {{ $textColor }}">{{ $value }}</p>
</div>
