@extends('layouts.app')

@section('title', $subject->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="shrink-0 h-16 w-16 bg-purple-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-xl font-bold">{{ substr($subject->name, 0, 1) }}</span>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $subject->name }}</h1>
                        <p class="text-sm text-gray-500">{{ $subject->description }}</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    @can('update', $subject)
                    <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn-secondary">
                        Modifier
                    </a>
                    @endcan
                    <a href="{{ route('admin.subjects.index') }}" class="btn-secondary">
                        Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Subject Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Informations de la Matière</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Code</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $subject->code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Coefficient</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $subject->coefficient }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Note Maximale</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $subject->max_mark }}/{{ $subject->max_mark }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Statut</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $subject->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $subject->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $subject->description ?: 'Aucune description' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Teachers -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Enseignants Assignés</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    @if(isset($subject->teachers) && $subject->teachers->count() > 0)
                    <ul role="list" class="divide-y divide-gray-200">
                        @foreach($subject->teachers as $teacher)
                        <li class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="shrink-0">
                                    <div class="h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-medium">{{ substr($teacher->user->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $teacher->user->name }}</p>
                                    <p class="text-sm text-gray-500 truncate">{{ $teacher->matricule }}</p>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $teacher->classes_count }} classe(s)
                                    </span>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-sm text-gray-500 text-center py-4">Aucun enseignant assigné à cette matière</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Statistics -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Statistiques</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Enseignants</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $subject->teachers_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Classes</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $subject->classes_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Évaluations</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $subject->evaluations_count }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Recent Evaluations -->
<div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg font-medium leading-6 text-gray-900">Évaluations Récentes</h3>
    </div>
    <div class="px-4 py-5 sm:p-6">
        @if($subject->evaluations && $subject->evaluations->count() > 0)
        <div class="space-y-3">
            @foreach($subject->evaluations->take(3) as $evaluation)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $evaluation->title }}</p>
                    <p class="text-sm text-gray-500">{{ $evaluation->class->name }}</p>
                </div>
                <span class="text-sm text-gray-500">{{ $evaluation->date->format('d/m/Y') }}</span>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-4">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500">Aucune évaluation récente</p>
        </div>
        @endif
    </div>
</div>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
