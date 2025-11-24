@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
        <div class="flex space-x-3">
            <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn-secondary">
                    Tout marquer comme lu
                </button>
            </form>
            <form action="{{ route('notifications.clear') }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirmAction('Êtes-vous sûr de vouloir supprimer toutes les notifications ?')" class="btn-danger">
                    Tout supprimer
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-4">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM10.5 3.75a6 6 0 100 12 6 6 0 000-12z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $notifications->total() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Non lues</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $unreadCount }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Aujourd'hui</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $todayCount }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Cette semaine</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $weekCount }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Mes Notifications</h3>
            <p class="mt-1 text-sm text-gray-500">Toutes vos notifications système</p>
        </div>

        @if($notifications->count() > 0)
        <div class="divide-y divide-gray-200">
            @foreach($notifications as $notification)
            <div class="px-4 py-4 sm:px-6 hover:bg-gray-50 transition-colors {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }}">
                <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-3 flex-1">
                        <!-- Notification Icon -->
                        <div class="flex-shrink-0">
                            @switch($notification->data['type'] ?? 'info')
                                @case('success')
                                    <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    @break
                                @case('warning')
                                    <div class="h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    @break
                                @case('error')
                                    <div class="h-8 w-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    @break
                                @default
                                    <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                            @endswitch
                        </div>

                        <!-- Notification Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900 {{ $notification->read_at ? '' : 'font-semibold' }}">
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                </p>
                                <div class="flex items-center space-x-2 ml-4">
                                    @if(!$notification->read_at)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Nouveau
                                    </span>
                                    @endif
                                    <span class="text-xs text-gray-500">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ $notification->data['message'] ?? $notification->data['description'] ?? 'Aucun message' }}
                            </p>

                            <!-- Notification Actions -->
                            <div class="mt-2 flex items-center space-x-4">
                                @if($notification->data['action_url'] ?? false)
                                <a href="{{ $notification->data['action_url'] }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500 transition-colors">
                                    {{ $notification->data['action_text'] ?? 'Voir' }}
                                </a>
                                @endif

                                @if(!$notification->read_at)
                                <form action="{{ route('notifications.markAsRead', $notification) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-500 transition-colors">
                                        Marquer comme lu
                                    </button>
                                </form>
                                @endif

                                <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center text-sm font-medium text-red-600 hover:text-red-500 transition-colors">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="px-4 py-4 sm:px-6 border-t border-gray-200">
            {{ $notifications->links() }}
        </div>
        @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM10.5 3.75a6 6 0 100 12 6 6 0 000-12z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune notification</h3>
            <p class="mt-1 text-sm text-gray-500">Vous n'avez aucune notification pour le moment.</p>
        </div>
        @endif
    </div>

    <!-- Notification Types -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Types de Notifications</h3>
            <p class="mt-1 text-sm text-gray-500">Configuration des préférences de notification</p>
        </div>
        <div class="px-4 py-5 sm:p-6">
            <form action="{{ route('notifications.preferences.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Nouvelles évaluations</label>
                            <p class="text-sm text-gray-500">Recevoir des notifications pour les nouvelles évaluations</p>
                        </div>
                        <input type="checkbox" name="new_evaluations" value="1"
                               {{ old('new_evaluations', auth()->user()->notification_preferences['new_evaluations'] ?? true) ? 'checked' : '' }}
                               class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                               role="switch">
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Notes manquantes</label>
                            <p class="text-sm text-gray-500">Alertes pour les évaluations sans notes</p>
                        </div>
                        <input type="checkbox" name="missing_marks" value="1"
                               {{ old('missing_marks', auth()->user()->notification_preferences['missing_marks'] ?? true) ? 'checked' : '' }}
                               class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                               role="switch">
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Rapports générés</label>
                            <p class="text-sm text-gray-500">Notifications lorsque les rapports sont prêts</p>
                        </div>
                        <input type="checkbox" name="reports_generated" value="1"
                               {{ old('reports_generated', auth()->user()->notification_preferences['reports_generated'] ?? true) ? 'checked' : '' }}
                               class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                               role="switch">
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Affectations de classes</label>
                            <p class="text-sm text-gray-500">Notifications pour les nouvelles affectations</p>
                        </div>
                        <input type="checkbox" name="class_assignments" value="1"
                               {{ old('class_assignments', auth()->user()->notification_preferences['class_assignments'] ?? true) ? 'checked' : '' }}
                               class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                               role="switch">
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Alertes de performance</label>
                            <p class="text-sm text-gray-500">Notifications sur les performances des élèves</p>
                        </div>
                        <input type="checkbox" name="performance_alerts" value="1"
                               {{ old('performance_alerts', auth()->user()->notification_preferences['performance_alerts'] ?? true) ? 'checked' : '' }}
                               class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                               role="switch">
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-primary">
                        Enregistrer les préférences
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle switches
    const toggles = document.querySelectorAll('input[type="checkbox"][role="switch"]');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            this.classList.toggle('bg-gray-200');
            this.classList.toggle('bg-blue-600');
        });

        // Set initial state
        if (this.checked) {
            this.classList.remove('bg-gray-200');
            this.classList.add('bg-blue-600');
        }
    });
});
</script>
@endpush
