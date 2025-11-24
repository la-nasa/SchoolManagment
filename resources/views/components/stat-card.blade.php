@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'blue',
    'trend' => null,
    'trendDirection' => 'up' // 'up' or 'down'
])

@php
    $colors = [
        'blue' => 'text-blue-600',
        'green' => 'text-green-600',
        'yellow' => 'text-yellow-600',
        'red' => 'text-red-600',
        'purple' => 'text-purple-600',
        'pink' => 'text-pink-600',
        'indigo' => 'text-indigo-600',
    ];

    $bgColors = [
        'blue' => 'bg-blue-50',
        'green' => 'bg-green-50',
        'yellow' => 'bg-yellow-50',
        'red' => 'bg-red-50',
        'purple' => 'bg-purple-50',
        'pink' => 'bg-pink-50',
        'indigo' => 'bg-indigo-50',
    ];
@endphp

<div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="p-5">
        <div class="flex items-center">
            @if($icon)
            <div class="shrink-0">
                <div class="{{ $bgColors[$color] ?? 'bg-blue-50' }} p-3 rounded-lg">
                    <svg class="w-6 h-6 {{ $colors[$color] ?? 'text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $icon !!}
                    </svg>
                </div>
            </div>
            @endif
            <div class="{{ $icon ? 'ml-5' : '' }} w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">{{ $title }}</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ $value }}</dd>
                </dl>
            </div>
        </div>
    </div>
    @if($trend)
    <div class="bg-gray-50 px-5 py-3">
        <div class="text-sm">
            <span class="font-medium {{ $trendDirection === 'up' ? 'text-green-600' : 'text-red-600' }} flex items-center">
                @if($trendDirection === 'up')
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                @else
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                </svg>
                @endif
                {{ $trend }}
            </span>
            <span class="text-gray-500 ml-2">depuis le mois dernier</span>
        </div>
    </div>
    @endif
</div>
