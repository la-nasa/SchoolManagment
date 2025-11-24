@extends('layouts.app')

@section('title', 'Mes Élèves')
@section('page-title', 'Mes Élèves - ' . auth()->user()->class->name)

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('titular.dashboard') }}">Tableau de Bord</a>
</li>
<li class="breadcrumb-item active">Mes Élèves</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Liste des élèves de la classe</h5>
    </div>
    <div class="card-body">
        @if($students->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Date de naissance</th>
                        <th>Moyenne Générale</th>
                        <th>Rang</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    @php
                        $generalAverage = $student->generalAverages->first();
                    @endphp
                    <tr>
                        <td>{{ $student->matricule }}</td>
                        <td>{{ $student->last_name }}</td>
                        <td>{{ $student->first_name }}</td>
                        <td>{{ $student->birth_date->format('d/m/Y') }}</td>
                        <td>
                            @if($generalAverage && $generalAverage->average)
                                <span class="badge {{ $generalAverage->average >= 10 ? 'bg-success' : 'bg-danger' }}">
                                    {{ number_format($generalAverage->average, 2) }}/20
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($generalAverage && $generalAverage->rank)
                                <span class="badge bg-info">{{ $generalAverage->rank }}/{{ $students->count() }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('titular.students.show', $student->id) }}" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-eye"></i> Détails
                                </a>
                                <a href="{{ route('reports.bulletin', $student->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-text"></i> Bulletin
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-3">Aucun élève dans votre classe.</p>
        </div>
        @endif
    </div>
</div>
@endsection
