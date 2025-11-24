@extends('layouts.app')

@section('title', 'Trimestres - ' . $schoolYear->year)

@section('page-title', 'Trimestres - ' . $schoolYear->year)

@section('page-actions')
    <div class="flex space-x-2">
        <a href="{{ route('admin.academic.school-years') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Retour
        </a>
        <a href="{{ route('admin.academic.terms.create', $schoolYear) }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nouveau trimestre
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        @if ($terms->count() > 0)
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                @foreach ($terms as $term)
                    <div
                        class="bg-white shadow rounded-lg overflow-hidden {{ $term->is_current ? 'border-2 border-green-500' : '' }}">
                        <div
                            class="px-4 py-5 sm:px-6 border-b border-gray-200 {{ $term->is_current ? 'bg-green-50' : 'bg-gray-50' }}">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">{{ $term->name }}</h3>
                                @if ($term->is_current)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        En cours
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="px-4 py-5 sm:p-6">
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Ordre</dt>
                                    <dd class="text-sm text-gray-900">Trimestre {{ $term->order }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Période</dt>
                                    <dd class="text-sm text-gray-900">{{ $term->start_date->format('d/m/Y') }} -
                                        {{ $term->end_date->format('d/m/Y') }}</dd>
                                </div>
                            </dl>

                            <div class="mt-6 flex space-x-2">
                                <a href="{{ route('admin.academic.terms.edit', $term) }}"
                                    class="flex-1 btn btn-outline-secondary text-center text-sm">
                                    <i class="bi bi-pencil me-1"></i>Modifier
                                </a>
                                @if (!$term->is_current)
                                    <form action="{{ route('admin.academic.terms.destroy', $term) }}" method="POST"
                                        class="flex-1" onsubmit="return confirm('Êtes-vous sûr?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full btn btn-outline-danger text-center text-sm">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun trimestre</h3>
                    <p class="mt-1 text-sm text-gray-500">Créez un nouveau trimestre pour cette année scolaire.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.academic.terms.create', $schoolYear) }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Créer un trimestre
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
