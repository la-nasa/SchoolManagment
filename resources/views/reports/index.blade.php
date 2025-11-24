@extends('layouts.app')

@section('title', 'Rapports et Documents')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Rapports et Documents</h1>
    </div>

    <!-- Report Types -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Bulletins -->
        <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Bulletins de Notes</dt>
                            <dd class="text-lg font-medium text-gray-900">Génération des bulletins</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="#bulletins" class="settings-nav-item w-full justify-center" data-tab="bulletins">
                        Générer des bulletins
                    </a>
                </div>
            </div>
        </div>

        <!-- PV -->
        <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Procès-verbaux</dt>
                            <dd class="text-lg font-medium text-gray-900">PV d'examens</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="#pv" class="settings-nav-item w-full justify-center" data-tab="pv">
                        Générer des PV
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Statistiques</dt>
                            <dd class="text-lg font-medium text-gray-900">Rapports statistiques</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="#statistics" class="settings-nav-item w-full justify-center" data-tab="statistics">
                        Voir les statistiques
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Content -->
    <div class="mt-8">
        <!-- Bulletins Tab -->
        <div id="bulletins-tab" class="settings-tab active">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Génération des Bulletins</h3>
                    <p class="mt-1 text-sm text-gray-500">Générez les bulletins de notes par classe et trimestre</p>
                </div>
                <form action="{{ route('admin.reports.performance') }}" method="POST" class="px-4 py-5 sm:p-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div>
                            <label for="bulletin_class_id" class="block text-sm font-medium text-gray-700">Classe *</label>
                            <select name="class_id" id="bulletin_class_id" required class="mt-1 form-select">
                                <option value="">Sélectionnez une classe</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="bulletin_trimester" class="block text-sm font-medium text-gray-700">Trimestre *</label>
                            <select name="trimester" id="bulletin_trimester" required class="mt-1 form-select">
                                <option value="1">Premier Trimestre</option>
                                <option value="2">Deuxième Trimestre</option>
                                <option value="3">Troisième Trimestre</option>
                                <option value="all">Tous les trimestres</option>
                            </select>
                        </div>
                        <div>
                            <label for="bulletin_format" class="block text-sm font-medium text-gray-700">Format *</label>
                            <select name="format" id="bulletin_format" required class="mt-1 form-select">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Options</label>
                        <div class="mt-2 space-y-2">
                            <div class="flex items-center">
                                <input type="checkbox" name="include_ranking" id="include_ranking" value="1" checked
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <label for="include_ranking" class="ml-2 text-sm text-gray-700">Inclure le classement</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="include_appreciations" id="include_appreciations" value="1" checked
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <label for="include_appreciations" class="ml-2 text-sm text-gray-700">Inclure les appréciations</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="include_comments" id="include_comments" value="1"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <label for="include_comments" class="ml-2 text-sm text-gray-700">Inclure les commentaires</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Générer les bulletins
                        </button>
                    </div>
                </form>
            </div>

            <!-- Recent Bulletins -->
            <div class="mt-6 bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Bulletins Générés Récemment</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    @if($recentBulletins->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classe</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trimestre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Généré le</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentBulletins as $bulletin)
                                <tr class="table-row-hover">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bulletin->class->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($bulletin->trimester == 'all')
                                            Tous les trimestres
                                        @else
                                            {{ $bulletin->trimester }}ème trimestre
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 uppercase">{{ $bulletin->format }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $bulletin->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('reports.download', $bulletin) }}" class="text-blue-600 hover:text-blue-900 transition-colors">
                                            Télécharger
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-sm text-gray-500 text-center py-4">Aucun bulletin généré récemment</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- PV Tab -->
        <div id="pv-tab" class="settings-tab hidden">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Génération des Procès-verbaux</h3>
                    <p class="mt-1 text-sm text-gray-500">Générez les PV d'examens et de conseils de classe</p>
                </div>
                <form action="{{ route('reports.generate.pv') }}" method="POST" class="px-4 py-5 sm:p-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="pv_class_id" class="block text-sm font-medium text-gray-700">Classe *</label>
                            <select name="class_id" id="pv_class_id" required class="mt-1 form-select">
                                <option value="">Sélectionnez une classe</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="pv_evaluation_id" class="block text-sm font-medium text-gray-700">Évaluation</label>
                            <select name="evaluation_id" id="pv_evaluation_id" class="mt-1 form-select">
                                <option value="">Toutes les évaluations</option>
                                <!-- Les évaluations seront chargées dynamiquement -->
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="pv_type" class="block text-sm font-medium text-gray-700">Type de PV *</label>
                            <select name="type" id="pv_type" required class="mt-1 form-select">
                                <option value="exam">PV d'Examen</option>
                                <option value="council">PV de Conseil de Classe</option>
                                <option value="deliberation">PV de Délibération</option>
                            </select>
                        </div>
                        <div>
                            <label for="pv_date" class="block text-sm font-medium text-gray-700">Date *</label>
                            <input type="date" name="date" id="pv_date" value="{{ date('Y-m-d') }}" required
                                   class="mt-1 form-input">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="pv_remarks" class="block text-sm font-medium text-gray-700">Remarques</label>
                        <textarea name="remarks" id="pv_remarks" rows="3" class="mt-1 form-textarea" placeholder="Remarques additionnelles..."></textarea>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Générer le PV
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Tab -->
        <div id="statistics-tab" class="settings-tab hidden">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Rapports Statistiques</h3>
                    <p class="mt-1 text-sm text-gray-500">Statistiques détaillées sur les performances</p>
                </div>
                <form action="{{ route('reports.generate.statistics') }}" method="POST" class="px-4 py-5 sm:p-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="stats_class_id" class="block text-sm font-medium text-gray-700">Classe</label>
                            <select name="class_id" id="stats_class_id" class="mt-1 form-select">
                                <option value="">Toutes les classes</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="stats_trimester" class="block text-sm font-medium text-gray-700">Trimestre</label>
                            <select name="trimester" id="stats_trimester" class="mt-1 form-select">
                                <option value="all">Tous les trimestres</option>
                                <option value="1">Premier Trimestre</option>
                                <option value="2">Deuxième Trimestre</option>
                                <option value="3">Troisième Trimestre</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Type de statistiques *</label>
                        <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex items-center">
                                <input type="radio" id="stats_type_performance" name="stats_type" value="performance" checked
                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                <label for="stats_type_performance" class="ml-3 block text-sm font-medium text-gray-700">Performances générales</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="stats_type_comparison" name="stats_type" value="comparison"
                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                <label for="stats_type_comparison" class="ml-3 block text-sm font-medium text-gray-700">Comparaison trimestrielle</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="stats_type_subject" name="stats_type" value="subject"
                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                <label for="stats_type_subject" class="ml-3 block text-sm font-medium text-gray-700">Par matière</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="stats_type_teacher" name="stats_type" value="teacher"
                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                <label for="stats_type_teacher" class="ml-3 block text-sm font-medium text-gray-700">Par enseignant</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Inclure les graphiques</label>
                        <div class="mt-2 flex items-center">
                            <input type="checkbox" name="include_charts" id="include_charts" value="1" checked
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <label for="include_charts" class="ml-2 text-sm text-gray-700">Inclure les graphiques dans le rapport</label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Générer le rapport
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Stats -->
            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-4">
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
                                    <dt class="text-sm font-medium text-gray-500 truncate">Taux de Réussite</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($quickStats['success_rate'], 1) }}%</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Moyenne Générale</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($quickStats['average_mark'], 2) }}/20</dd>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Meilleure Moyenne</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($quickStats['top_mark'], 2) }}/20</dd>
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
                                    <dt class="text-sm font-medium text-gray-500 truncate">Plus Basse Moyenne</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($quickStats['bottom_mark'], 2) }}/20</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab navigation
    const navItems = document.querySelectorAll('.settings-nav-item');
    const tabs = document.querySelectorAll('.settings-tab');

    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active class from all items and tabs
            navItems.forEach(nav => nav.classList.remove('active'));
            tabs.forEach(tab => tab.classList.add('hidden'));

            // Add active class to clicked item
            this.classList.add('active');

            // Show corresponding tab
            const tabId = this.getAttribute('data-tab');
            const tab = document.getElementById(tabId + '-tab');
            if (tab) {
                tab.classList.remove('hidden');
                tab.classList.add('active');
            }
        });
    });

    // Load evaluations when class is selected for PV
    const pvClassSelect = document.getElementById('pv_class_id');
    const pvEvaluationSelect = document.getElementById('pv_evaluation_id');

    if (pvClassSelect && pvEvaluationSelect) {
        pvClassSelect.addEventListener('change', function() {
            const classId = this.value;
            pvEvaluationSelect.innerHTML = '<option value="">Toutes les évaluations</option>';

            if (classId) {
                // Load evaluations for selected class
                fetch(`/api/classes/${classId}/evaluations`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(evaluation => {
                            const option = document.createElement('option');
                            option.value = evaluation.id;
                            option.textContent = `${evaluation.subject.name} - ${evaluation.type}`;
                            pvEvaluationSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading evaluations:', error));
            }
        });
    }
});
</script>
@endpush
