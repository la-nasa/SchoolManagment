@props([
    'value' => 0,
    'max' => 100,
    'color' => 'blue', // 'blue', 'green', 'yellow', 'red', 'purple'
    'showLabel' => false,
    'size' => 'medium' // 'small', 'medium', 'large'
])

@php
    $percentage = min(100, max(0, ($value / $max) * 100));

    $colors = [
        'blue' => 'bg-blue-600',
        'green' => 'bg-green-600',
        'yellow' => 'bg-yellow-600',
        'red' => 'bg-red-600',
        'purple' => 'bg-purple-600',
    ];

    $sizes = [
        'small' => 'h-1',
        'medium' => 'h-2',
        'large' => 'h-4',
    ];
@endphp

<div class="w-full">
    @if($showLabel)
    <div class="flex justify-between text-sm text-gray-600 mb-1">
        <span>{{ $value }}/{{ $max }}</span>
        <span>{{ number_format($percentage, 1) }}%</span>
    </div>
    @endif
    <div class="w-full bg-gray-200 rounded-full {{ $sizes[$size] }}">
        <div
            class="{{ $colors[$color] }} {{ $sizes[$size] }} rounded-full transition-all duration-300 ease-in-out"
            style="width: {{ $percentage }}%"
        ></div>
    </div>
</div>
