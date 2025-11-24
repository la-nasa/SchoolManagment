@extends('layouts.app')

@section('title', 'Détails de l\'Audit')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">Détails de l'Événement d'Audit</h1>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Informations Générales</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID</dt>
                            <dd class="text-sm text-gray-900">{{ $audit->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Utilisateur</dt>
                            <dd class="text-sm text-gray-900">{{ $audit->user ? $audit->user->name : 'Système' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Événement</dt>
                            <dd class="text-sm text-gray-900">
                                <span class="px-2 py-1 rounded-full text-xs font-medium 
                                    {{ $audit->event === 'created' ? 'bg-green-100 text-green-800' : 
                                       ($audit->event === 'updated' ? 'bg-blue-100 text-blue-800' : 
                                       'bg-red-100 text-red-800') }}">
                                    {{ $audit->event }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Modèle</dt>
                            <dd class="text-sm text-gray-900">{{ $audit->auditable_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID du Modèle</dt>
                            <dd class="text-sm text-gray-900">{{ $audit->auditable_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date et Heure</dt>
                            <dd class="text-sm text-gray-900">{{ $audit->created_at->format('d/m/Y H:i:s') }}</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Informations Techniques</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Adresse IP</dt>
                            <dd class="text-sm text-gray-900">{{ $audit->ip_address ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                            <dd class="text-sm text-gray-900">{{ $audit->user_agent ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">URL</dt>
                            <dd class="text-sm text-gray-900">{{ $audit->url ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if($audit->getModified())
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Modifications</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ json_encode($audit->getModified(), JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif

            <div class="mt-8 flex justify-end">
                <a href="{{ route('admin.audit.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors">
                    Retour à la liste
                </a>
            </div>
        </div>
    </div>
</div>
@endsection