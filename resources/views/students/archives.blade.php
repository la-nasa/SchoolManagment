@extends('layouts.app')

@section('title', 'Archives des Élèves')
@section('page-title', 'Archives des Élèves')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('admin.students.index') }}">Élèves</a>
</li>
<li class="breadcrumb-item active">Archives</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Retour aux élèves
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Élèves archivés</h5>
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
                        <th>Classe</th>
                        <th>Date d'archivage</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr>
                        <td>{{ $student->matricule }}</td>
                        <td>{{ $student->last_name }}</td>
                        <td>{{ $student->first_name }}</td>
                        <td>{{ $student->class->name }}</td>
                        <td>{{ $student->deleted_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="btn-group">
                                <form action="{{ route('admin.archives.students.restore', $student->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-sm" onclick="return confirm('Restaurer cet élève ?')">
                                        <i class="bi bi-arrow-clockwise"></i> Restaurer
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $students->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-archive text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-3">Aucun élève archivé.</p>
        </div>
        @endif
    </div>
</div>
@endsection
