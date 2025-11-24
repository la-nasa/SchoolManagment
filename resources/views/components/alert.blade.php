@props([
    'type' => 'info', // 'success', 'error', 'warning', 'info'
    'message' => '',
    'dismissible' => false
])

@php
    $styles = [
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-200',
            'text' => 'text-green-800',
            'icon' => 'text-green-400',
            'icon_path' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-200',
            'text' => 'text-red-800',
            'icon' => 'text-red-400',
            'icon_path' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-200',
            'text' => 'text-yellow-800',
            'icon' => 'text-yellow-400',
            'icon_path' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'text' => 'text-blue-800',
            'icon' => 'text-blue-400',
            'icon_path' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
        ]
    ];

    $style = $styles[$type] ?? $styles['info'];
@endphp

<div class="rounded-md {{ $style['bg'] }} p-4 border {{ $style['border'] }}" x-data="{ show: true }" x-show="show">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="w-5 h-5 {{ $style['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $style['icon_path'] }}"/>
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm font-medium {{ $style['text'] }}">
                {{ $message }}
            </p>
        </div>
        @if($dismissible)
        <div class="ml-auto pl-3">
            <div class="-mx-1.5 -my-1.5">
                <button @click="show = false" class="inline-flex rounded-md {{ $style['bg'] }} p-1.5 {{ $style['text'] }} hover:{{ $style['bg'] }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-{{ $style['bg'] }} focus:ring-{{ $style['text'] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        @endif
    </div>
</div>
