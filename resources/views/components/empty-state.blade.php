@props([
    'title' => 'Aucune donnée',
    'description' => 'Aucune donnée disponible pour le moment.',
    'icon' => null,
    'action' => null,
    'actionLabel' => 'Ajouter'
])

<div class="text-center py-12">
    @if($icon)
    <div class="mx-auto h-12 w-12 text-gray-400">
        {!! $icon !!}
    </div>
    @else
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    @endif
    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $title }}</h3>
    <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
    @if($action)
    <div class="mt-6">
        <a href="{{ $action }}" class="btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            {{ $actionLabel }}
        </a>
    </div>
    @endif
</div>
