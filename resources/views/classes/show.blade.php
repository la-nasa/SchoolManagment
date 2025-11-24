@extends('layouts.app')

@section('title', $classe->name)

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="shrink-0 h-16 w-16 bg-blue-500 rounded-lg flex items-center justify-center">
                            <span class="text-white text-xl font-bold">{{ $classe->level }}</span>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $classe->name }}</h1>
                            <p class="text-sm text-gray-500">Année scolaire: {{ $classe->schoolYear?->year ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        @can('update', $classe)
                            <a href="{{ route('admin.classes.edit', $classe) }}" class="btn-secondary">
                                Modifier
                            </a>
                        @endcan
                        <a href="{{ route('admin.classes.index') }}" class="btn-secondary">
                            Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Class Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Informations de la Classe</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Niveau</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $classe->level }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Année scolaire</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $classe->schoolYear?->year ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Capacité</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $classe->capacity }} élèves</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Élèves inscrits</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $classe->students?->count() ?? 0 }} élèves</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Enseignant titulaire</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $classe->teacher?->name ?? 'Aucun titulaire' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Statut</dt>
                                <dd class="mt-1">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classe->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $classe->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $classe->description ?: 'Aucune description' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Students List -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Élèves de la Classe</h3>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $classe->students?->count() ?? 0 }} élèves
                            </span>
                        </div>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        @if ($classe->students && $classe->students->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Élève</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date de naissance</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Contact parent</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Moyenne</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($classe->students as $student)
                                            <tr class="table-row-hover">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div
                                                            class="shrink-0 h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center">
                                                            <span class="text-white font-medium">
                                                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                                            </span>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $student->first_name }} {{ $student->last_name }}
                                                            </div>
                                                            <div class="text-sm text-gray-500">{{ $student->matricule }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $student->date_of_birth?->format('d/m/Y') ?? 'N/A' }}</div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $student->date_of_birth?->age ?? 'N/A' }} ans</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $student->parent_phone ?? 'N/A' }}</div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $student->parent_email ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <span
                                                        class="text-gray-900">{{ number_format($student->average_mark ?? 0, 2) }}/20</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun élève</h3>
                                <p class="mt-1 text-sm text-gray-500">Aucun élève n'est inscrit dans cette classe.</p>
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
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Élèves</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ $classe->students?->count() ?? 0 }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Moyenne de classe</dt>
                                <dd class="text-2xl font-semibold text-gray-900">
                                    {{ number_format($classStats['average'] ?? 0, 2) }}/20</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Minimum</dt>
                                <dd class="text-2xl font-semibold text-gray-900">
                                    {{ number_format($classStats['min'] ?? 0, 2) }}/20</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Maximum</dt>
                                <dd class="text-2xl font-semibold text-gray-900">
                                    {{ number_format($classStats['max'] ?? 0, 2) }}/20</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Teachers -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Enseignants</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        @if ($classe->teacherAssignments && $classe->teacherAssignments->count() > 0)
                            <ul role="list" class="divide-y divide-gray-200">
                                @foreach ($classe->teacherAssignments as $assignment)
                                    <li class="py-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="shrink-0">
                                                <div
                                                    class="h-8 w-8 bg-blue-500 rounded-full flex items-center justify-center">
                                                    <span class="text-white text-sm font-medium">
                                                        {{ substr($assignment->teacher?->name, 0, 1) ?? 'T' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $assignment->teacher?->name ?? 'N/A' }}</p>
                                                <p class="text-sm text-gray-500 truncate">
                                                    {{ $assignment->subject?->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">Aucun enseignant assigné</p>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Actions</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6 space-y-3">
                        <a href="{{ route('admin.students.create', ['class_id' => $classe->id]) }}"
                            class="w-full btn-primary justify-center">
                            Ajouter un élève
                        </a>
                        <a href="{{ route('admin.evaluations.create', ['class_id' => $classe->id]) }}"
                            class="w-full btn-secondary justify-center">
                            Nouvelle évaluation
                        </a>
                        <a href="{{ route('admin.reports.bulletin', ['class_id' => $classe->id , 'student' => $classe->students ]) }}"
                            class="w-full btn-secondary justify-center">
                            Générer bulletins
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
