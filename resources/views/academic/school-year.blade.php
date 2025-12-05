@extends('layouts.app')

@section('title', 'Gestion des Années Scolaires')
@section('page-title', 'Années Scolaires')

@section('breadcrumbs')
<li class="breadcrumb-item active">Années Scolaires</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.academic.school-years.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i>Nouvelle année scolaire
</a>
@endsection

@section('content')
@if($schoolYears->count() > 0)
<div class="row">
    @foreach($schoolYears as $schoolYear)
    <div class="col-lg-6 mb-4">
        <div class="card h-100 {{ $schoolYear->is_current ? 'border-primary' : '' }}">
            <div class="card-header bg-white d-flex justify-content-between align-items-center {{ $schoolYear->is_current ? 'bg-primary text-white' : '' }}">
                <h6 class="mb-0">{{ $schoolYear->year }}</h6>
                @if($schoolYear->is_current)
                <span class="badge bg-light text-primary">
                    <i class="bi bi-star-fill me-1"></i>Année actuelle
                </span>
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            {{ $schoolYear->start_date->format('d/m/Y') }} - {{ $schoolYear->end_date->format('d/m/Y') }}
                        </small>
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-6">
                        <div class="h5 text-primary mb-1">{{ $schoolYear->terms->count() }}</div>
                        <small class="text-muted">Trimestres</small>
                    </div>
                    <div class="col-6">
                        <div class="h5 text-success mb-1">
                            @if($schoolYear->is_current)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                        <small class="text-muted">Statut</small>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{{ route('admin.academic.terms', $schoolYear) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-calendar me-1"></i>Trimestres
                    </a>
                    <a href="{{ route('admin.academic.school-years.edit', $schoolYear) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-pencil me-1"></i>Modifier
                    </a>
                    @if(!$schoolYear->is_current)
                    <form action="{{ route('admin.academic.school-years.destroy', $schoolYear) }}" method="POST"
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette année scolaire ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash me-1"></i>Supprimer
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-calendar-x display-1 text-muted"></i>
        <h4 class="text-muted mt-3">Aucune année scolaire</h4>
        <p class="text-muted">Créez une nouvelle année scolaire pour commencer.</p>
        <a href="{{ route('admin.academic.school-years.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Créer une année scolaire
        </a>
    </div>
</div>
@endif
@endsection
