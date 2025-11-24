@extends('layouts.app')

@section('title', 'Gestion des Classes')

@section('page-title', 'Classes')

@section('page-actions')
    <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Nouvelle classe
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white shadow rounded-lg px-4 py-5 sm:p-6">
            <form method="GET" action="{{ route('admin.classes.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Rechercher</label>
                    <input type="text" name="search" id="search"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Nom de la classe..." value="{{ request('search') }}">
                </div>

                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700">Niveau</label>
                    <select name="level" id="level"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Tous les niveaux --</option>
                        <option value="6ème" {{ request('level') == '6ème' ? 'selected' : '' }}>6ème</option>
                        <option value="5ème" {{ request('level') == '5ème' ? 'selected' : '' }}>5ème</option>
                        <option value="4ème" {{ request('level') == '4ème' ? 'selected' : '' }}>4ème</option>
                        <option value="3ème" {{ request('level') == '3ème' ? 'selected' : '' }}>3ème</option>
                        <option value="2nde" {{ request('level') == '2nde' ? 'selected' : '' }}>2nde</option>
                        <option value="1ère" {{ request('level') == '1ère' ? 'selected' : '' }}>1ère</option>
                        <option value="Tle" {{ request('level') == 'Tle' ? 'selected' : '' }}>Tle</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="btn btn-secondary w-full">
                        <i class="bi bi-search me-2"></i>Rechercher
                    </button>
                </div>
            </form>
        </div>

        <!-- Classes Table -->
        @if ($classes->count() > 0)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Classe</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Niveau</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Année</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Élèves</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Titulaire</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut</th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($classes as $class)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <a href="{{ route('admin.classes.show', $class) }}"
                                            class="text-blue-600 hover:text-blue-800">
                                            {{ $class->name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $class->level }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $class->schoolYear?->year ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $class->students?->count() ?? 0 }}/{{ $class->capacity }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $class->teacher?->name ?? 'Aucun' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $class->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $class->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex space-x-2 justify-end">
                                            <a href="{{ route('admin.classes.show', $class) }}"
                                                class="btn btn-sm btn-outline-secondary" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.classes.edit', $class) }}"
                                                class="btn btn-sm btn-outline-secondary" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.classes.destroy', $class) }}" method="POST"
                                                onsubmit="return confirm('Êtes-vous sûr?')" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $classes->links() }}
            </div>
        @else
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune classe</h3>
                    <p class="mt-1 text-sm text-gray-500">Créez une nouvelle classe pour commencer.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Créer une classe
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
