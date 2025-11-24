@extends('layouts.app')

@section('title', 'Tableau de bord académique')

@section('page-title', 'Tableau de bord académique')

@section('content')
    <div class="space-y-6">
        <!-- Header Stats -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Élèves</dt>
                    <dd class="mt-1 text-3xl font-extrabold text-gray-900">{{ $stats['total_students'] }}</dd>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Taux de réussite</dt>
                    <dd class="mt-1 text-3xl font-extrabold text-green-600">{{ number_format($stats['success_rate'], 1) }}%
                    </dd>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Moyenne générale</dt>
                    <dd class="mt-1 text-3xl font-extrabold text-blue-600">{{ number_format($stats['average_mark'], 2) }}/20
                    </dd>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Évaluations</dt>
                    <dd class="mt-1 text-3xl font-extrabold text-purple-600">{{ $stats['total_evaluations'] }}</dd>
                </div>
            </div>
        </div>

        <!-- Performance des Classes -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Performance par Classe</h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
                @if ($classPerformance->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Classe</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Élèves</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Moyenne</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Taux réussite</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($classPerformance as $class)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $class->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $class->students_count }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ number_format($class->average_mark, 2) }}/20
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-green-600 h-2 rounded-full"
                                                        style="width: {{ $class->success_rate }}%"></div>
                                                </div>
                                                <span
                                                    class="ml-2 text-xs font-medium">{{ number_format($class->success_rate, 1) }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-500">Aucune classe disponible</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Performance par Matière -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Performance par Matière</h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
                @if ($subjectPerformance->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Matière</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Coefficient</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Moyenne</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Évaluations</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($subjectPerformance as $subject)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $subject->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $subject->coefficient }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ number_format($subject->average_mark, 2) }}/20
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $subject->evaluations_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-500">Aucune matière disponible</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Activités récentes -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Activités Récentes</h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
                @if ($recentActivities->count() > 0)
                    <ul role="list" class="divide-y divide-gray-200">
                        @foreach ($recentActivities as $activity)
                            <li class="py-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $activity->user?->name ?? 'Système' }}</p>
                                            <p class="text-sm text-gray-500">{{ $activity->description }}</p>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $activity->created_at->diffForHumans() }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-500">Aucune activité</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
