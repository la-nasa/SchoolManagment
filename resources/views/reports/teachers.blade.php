@extends('layouts.app')

@section('title', 'Rapport des Enseignants')
@section('page-title', 'Rapport des Enseignants')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.school') }}">Rapports</a></li>
<li class="breadcrumb-item active">Enseignants</li>
@endsection

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('admin.reports.teachers', ['export' => true]) }}" class="btn btn-success">
        <i class="bi bi-file-pdf me-1"></i>Exporter PDF
    </a>
    <a href="{{ route('admin.reports.school') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bi bi-people me-2"></i>Performance des Enseignants - {{ $term->name }} {{ $schoolYear->year }}
                </h6>
            </div>
            <div class="card-body">
                @if($teachers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Enseignant</th>
                                <th>Classes</th>
                                <th>Matières</th>
                                <th>Évaluations</th>
                                <th>Taux de Complétion</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teachers as $teacherData)
                            @php
                                $teacher = $teacherData['teacher'];
                                $assignments = $teacherData['assignments'];
                                $completionStats = $teacherData['completion_stats'] ?? [];
                                $performanceStats = $teacherData['performance_stats'] ?? [];
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $teacher->photo_url }}" alt="{{ $teacher->name }}" 
                                             class="rounded-circle me-2" width="32" height="32">
                                        <div>
                                            <div class="fw-semibold">{{ $teacher->name }}</div>
                                            <small class="text-muted">{{ $teacher->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($assignments->count() > 0)
                                        @foreach($assignments->take(2) as $assignment)
                                            <span class="badge bg-light text-dark mb-1">{{ $assignment->classe->name }}</span>
                                        @endforeach
                                        @if($assignments->count() > 2)
                                            <small class="text-muted">+{{ $assignments->count() - 2 }} autres</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Aucune classe</span>
                                    @endif
                                </td>
                                <td>
                                    @if($assignments->count() > 0)
                                        @foreach($assignments->take(2) as $assignment)
                                            <span class="badge bg-info mb-1">{{ $assignment->subject->name }}</span>
                                        @endforeach
                                        @if($assignments->count() > 2)
                                            <small class="text-muted">+{{ $assignments->count() - 2 }} autres</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Aucune matière</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $evaluationsCount = \App\Models\Evaluation::whereIn('class_id', $assignments->pluck('class_id'))
                                            ->whereIn('subject_id', $assignments->pluck('subject_id'))
                                            ->where('school_year_id', $schoolYear->id)
                                            ->where('term_id', $term->id)
                                            ->count();
                                    @endphp
                                    <span class="badge bg-primary">{{ $evaluationsCount }}</span>
                                </td>
                                <td>
                                    @if(isset($completionStats['completion_rate']))
                                    <div class="d-flex align-items-center">
                                        <div class="progress grow me-2" style="height: 8px;">
                                            <div class="progress-bar {{ $completionStats['completion_rate'] >= 80 ? 'bg-success' : ($completionStats['completion_rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                 style="width: {{ $completionStats['completion_rate'] }}%">
                                            </div>
                                        </div>
                                        <span class="text-muted small">{{ $completionStats['completion_rate'] ?? 0 }}%</span>
                                    </div>
                                    @else
                                    <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($performanceStats['average_mark']))
                                        @if($performanceStats['average_mark'] >= 12)
                                        <span class="badge bg-success">Excellente</span>
                                        @elseif($performanceStats['average_mark'] >= 10)
                                        <span class="badge bg-warning">Satisfaisante</span>
                                        @else
                                        <span class="badge bg-danger">À améliorer</span>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-people display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">Aucun enseignant trouvé</h4>
                    <p class="text-muted">Aucune donnée d'enseignant disponible pour cette période.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection