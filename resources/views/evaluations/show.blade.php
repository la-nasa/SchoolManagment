@extends('layouts.app')

@section('title', $evaluation->title ?? 'Détails de l\'Évaluation')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="shrink-0 h-16 w-16 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-xl font-bold">{{ substr($evaluation->title ?? 'E', 0, 1) }}</span>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $evaluation->title ?? 'Évaluation sans titre' }}</h1>
                        <p class="text-sm text-gray-500">
                            {{ $evaluation->subject->name ?? 'Matière inconnue' }} -
                            {{ $evaluation->class->name ?? 'Classe inconnue' }}
                        </p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    @can('update', $evaluation)
                    <a href="{{ route('evaluations.edit', $evaluation) }}" class="btn-secondary">
                        Modifier
                    </a>
                    @endcan
                    <a href="{{ route('admin.marks.create', ['evaluation_id' => $evaluation->id]) }}" class="btn-primary">
                        Saisir les notes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Evaluation Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Informations de l'Évaluation</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Matière</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $evaluation->subject->name ?? 'Non spécifiée' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Classe</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $evaluation->class->name ?? 'Non spécifiée' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $evaluation->examType->name ?? ($evaluation->type ?? 'Non spécifié') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Trimestre</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $evaluation->term->name ?? ($evaluation->trimester ? $evaluation->trimester . 'ème trimestre' : 'Non spécifié') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($evaluation->exam_date)
                                    {{ \Carbon\Carbon::parse($evaluation->exam_date)->format('d/m/Y') }}
                                @elseif($evaluation->evaluation_date)
                                    {{ \Carbon\Carbon::parse($evaluation->evaluation_date)->format('d/m/Y') }}
                                @else
                                    Non spécifiée
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Note maximale</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $evaluation->max_marks ?? ($evaluation->max_mark ?? 0) }} points</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Note de passage</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $evaluation->pass_marks ?? ($evaluation->weight ?? 0) }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $evaluation->description ?: 'Aucune description' }}</dd>
                        </div>
                        @if($evaluation->instructions)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Instructions</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $evaluation->instructions }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Marks Overview -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Notes des Élèves</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    @if($evaluation->marks && $evaluation->marks->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Élève</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Appréciation</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarques</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($evaluation->marks as $mark)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $mark->student->first_name ?? '' }} {{ $mark->student->last_name ?? '' }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $mark->student->matricule ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ number_format($mark->mark, 2) }}/{{ $evaluation->max_marks ?? $evaluation->max_mark ?? 20 }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            @php
                                                $maxMark = $evaluation->max_marks ?? $evaluation->max_mark ?? 20;
                                                $normalizedMark = $maxMark > 0 ? ($mark->mark / $maxMark) * 20 : 0;
                                            @endphp
                                            {{ number_format($normalizedMark, 1) }}/20
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($mark->appreciation)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $mark->appreciation == 'Excellent' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $mark->appreciation == 'Très bien' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $mark->appreciation == 'Bien' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $mark->appreciation == 'Assez bien' ? 'bg-orange-100 text-orange-800' : '' }}
                                            {{ $mark->appreciation == 'Passable' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $mark->appreciation == 'Insuffisant' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ $mark->appreciation }}
                                        </span>
                                        @else
                                        <span class="text-xs text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $mark->remarks ?: '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune note</h3>
                        <p class="mt-1 text-sm text-gray-500">Aucune note n'a été saisie pour cette évaluation.</p>
                        <div class="mt-6">
                            <a href="{{ route('admin.marks.create', ['evaluation_id' => $evaluation->id]) }}" class="btn-primary">
                                Saisir les notes
                            </a>
                        </div>
                    </div>
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
                        @php
                            $marksCount = $evaluation->marks ? $evaluation->marks->count() : 0;
                            $studentsCount = $evaluation->class && $evaluation->class->students ? $evaluation->class->students->count() : 0;
                            $completionRate = $studentsCount > 0 ? ($marksCount / $studentsCount) * 100 : 0;
                            $averageMark = $evaluation->marks && $evaluation->marks->count() > 0 ? $evaluation->marks->avg('mark') : 0;
                            $maxMark = $evaluation->marks && $evaluation->marks->count() > 0 ? $evaluation->marks->max('mark') : 0;
                            $minMark = $evaluation->marks && $evaluation->marks->count() > 0 ? $evaluation->marks->min('mark') : 0;
                        @endphp

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Notes saisies</dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                {{ $marksCount }}/{{ $studentsCount }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Taux de complétion</dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                {{ number_format($completionRate, 0) }}%
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Moyenne</dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                {{ number_format($averageMark, 2) }}/{{ $evaluation->max_marks ?? $evaluation->max_mark ?? 20 }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Meilleure note</dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                {{ number_format($maxMark, 2) }}/{{ $evaluation->max_marks ?? $evaluation->max_mark ?? 20 }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Plus basse note</dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                {{ number_format($minMark, 2) }}/{{ $evaluation->max_marks ?? $evaluation->max_mark ?? 20 }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Teacher Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Enseignant</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    @if($evaluation->teacher)
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-12 w-12 bg-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-medium">
                                {{ substr($evaluation->teacher->name ?? 'E', 0, 1) }}
                            </span>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ $evaluation->teacher->name ?? 'Enseignant non spécifié' }}</div>
                            <div class="text-sm text-gray-500">{{ $evaluation->teacher->matricule ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500">{{ $evaluation->teacher->email ?? '' }}</div>
                        </div>
                    </div>
                    @else
                    <div class="text-center text-sm text-gray-500">
                        Aucun enseignant assigné
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Actions</h3>
                </div>
                <div class="px-4 py-5 sm:p-6 space-y-3">
                    <a href="{{ route('admin.marks.create', ['evaluation_id' => $evaluation->id]) }}" class="w-full btn-primary justify-center">
                        Saisir les notes
                    </a>
                    @can('update', $evaluation)
                    <a href="{{ route('evaluations.edit', $evaluation) }}" class="w-full btn-secondary justify-center">
                        Modifier l'évaluation
                    </a>
                    @endcan
                    <a href="{{ route('admin.evaluations.index') }}" class="w-full btn-secondary justify-center">
                        Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.btn-primary {
    @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200;
}

.btn-secondary {
    @apply inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200;
}
</style>
@endpush
