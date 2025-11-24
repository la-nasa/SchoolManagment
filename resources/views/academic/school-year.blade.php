@extends('layouts.app')

@section('title', 'Gestion des Années Scolaires')

@section('page-title', 'Années Scolaires')

@section('page-actions')
<a href="{{ route('admin.academic.school-years.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-circle me-2"></i>Nouvelle année scolaire
</a>
@endsection

@section('content')
<div class="space-y-6">
    @if($schoolYears->count() > 0)
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @foreach($schoolYears as $schoolYear)
        <div class="bg-white shadow rounded-lg overflow-hidden {{ $schoolYear->is_current ? 'border-2 border-blue-500' : '' }}">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 {{ $schoolYear->is_current ? 'bg-blue-50' : 'bg-gray-50' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900">{{ $schoolYear->year }}</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $schoolYear->start_date->format('d/m/Y') }} - {{ $schoolYear->end_date->format('d/m/Y') }}
                        </p>
                    </div>
                    @if($schoolYear->is_current)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        Année actuelle
                    </span>
                    @endif
                </div>
            </div>

            <div class="px-4 py-5 sm:p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Trimestres</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $schoolYear->terms->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Statut</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </dd>
                    </div>
                </dl>

                <div class="mt-6 flex space-x-3">
                    <a href="{{ route('admin.academic.terms', $schoolYear) }}" class="flex-1 btn btn-secondary text-center">
                        <i class="bi bi-calendar me-1"></i>Trimestres
                    </a>
                    <a href="{{ route('admin.academic.school-years.edit', $schoolYear) }}" class="flex-1 btn btn-outline-secondary text-center">
                        <i class="bi bi-pencil me-1"></i>Modifier
                    </a>
                    @if(!$schoolYear->is_current)
                    <form action="{{ route('admin.academic.school-years.destroy', $schoolYear) }}" method="POST" class="flex-1" onsubmit="return confirm('Êtes-vous sûr?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full btn btn-outline-danger text-center">
                            <i class="bi bi-trash me-1"></i>Supprimer
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune année scolaire</h3>
            <p class="mt-1 text-sm text-gray-500">Créez une nouvelle année scolaire pour commencer.</p>
            <div class="mt-6">
                <a href="{{ route('admin.academic.school-years.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Créer une année scolaire
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection