@extends('layouts.app')

@section('title', 'Gestion des Notes')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Gestion des Notes</h1>
        @can('create', App\Models\Mark::class)
        <a href="{{ route('marks.create') }}" class="btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Saisir des Notes
        </a>
        @endcan
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6">
        <form method="GET" action="{{ route('marks.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <label for="class_id" class="block text-sm font-medium text-gray-700">Classe</label>
                <select name="class_id" id="class_id" class="mt-1 form-select">
                    <option value="">Toutes les classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="subject_id" class="block text-sm font-medium text-gray-700">Matière</label>
                <select name="subject_id" id="subject_id" class="mt-1 form-select">
                    <option value="">Toutes les matières</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="trimester" class="block text-sm font-medium text-gray-700">Trimestre</label>
                <select name="trimester" id="trimester" class="mt-1 form-select">
                    <option value="">Tous</option>
                    <option value="1" {{ request('trimester') == 1 ? 'selected' : '' }}>1er Trimestre</option>
                    <option value="2" {{ request('trimester') == 2 ? 'selected' : '' }}>2ème Trimestre</option>
                    <option value="3" {{ request('trimester') == 3 ? 'selected' : '' }}>3ème Trimestre</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary w-full">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-4">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Notes Saisies</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total_marks'] }}</dd>
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Moyenne Générale</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['average_mark'], 2) }}/20</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Meilleure Note</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['max_mark'], 2) }}/20</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Plus Basse Note</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['min_mark'], 2) }}/20</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Marks Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Notes des Élèves</h3>
            <p class="mt-1 text-sm text-gray-500">Liste de toutes les notes saisies</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Élève</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matière</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Évaluation</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Appréciation</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($marks as $mark)
                    <tr class="table-row-hover">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium">
                                        {{ substr($mark->student->first_name, 0, 1) }}{{ substr($mark->student->last_name, 0, 1) }}
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $mark->student->first_name }} {{ $mark->student->last_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $mark->student->class->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $mark->evaluation->subject->name }}</div>
                            <div class="text-sm text-gray-500">Coeff. {{ $mark->evaluation->subject->coefficient }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $mark->evaluation->type }}</div>
                            <div class="text-sm text-gray-500">{{ $mark->evaluation->sequence_type }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ number_format($mark->mark, 2) }}/{{ $mark->evaluation->max_mark }}
                            </div>
                            <div class="text-sm text-gray-500">
                                @php
                                    $percentage = ($mark->mark / $mark->evaluation->max_mark) * 20;
                                @endphp
                                {{ number_format($percentage, 1) }}/20
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $mark->appreciation == 'Excellent' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $mark->appreciation == 'Très bien' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $mark->appreciation == 'Bien' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $mark->appreciation == 'Assez bien' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $mark->appreciation == 'Passable' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $mark->appreciation == 'Insuffisant' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $mark->appreciation }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $mark->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                @can('update', $mark)
                                <a href="{{ route('marks.edit', $mark) }}" class="text-green-600 hover:text-green-900 transition-colors" title="Modifier">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endcan
                                @can('delete', $mark)
                                <form action="{{ route('marks.destroy', $mark) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirmAction('Êtes-vous sûr de vouloir supprimer cette note ?')" class="text-red-600 hover:text-red-900 transition-colors" title="Supprimer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4 sm:px-6 border-t border-gray-200">
            {{ $marks->links() }}
        </div>
    </div>
</div>
@endsection
