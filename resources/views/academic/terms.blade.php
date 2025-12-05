@extends('layouts.app')

@section('title', 'Trimestres - ' . $schoolYear->year)
@section('page-title', 'Trimestres - ' . $schoolYear->year)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.academic.school-years') }}">Années Scolaires</a></li>
<li class="breadcrumb-item active">{{ $schoolYear->year }}</li>
@endsection

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('admin.academic.school-years') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
    <a href="{{ route('admin.academic.terms.create', $schoolYear) }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nouveau trimestre
    </a>
</div>
@endsection

@section('content')
@if ($terms->count() > 0)
<div class="row">
    @foreach ($terms as $term)
    <div class="col-lg-4 mb-4">
        <div class="card h-100 {{ $term->is_current ? 'border-success' : '' }}">
            <div class="card-header bg-white d-flex justify-content-between align-items-center {{ $term->is_current ? 'bg-success text-white' : '' }}">
                <h6 class="mb-0">{{ $term->name }}</h6>
                @if ($term->is_current)
                <span class="badge bg-light text-success">
                    <i class="bi bi-play-circle me-1"></i>En cours
                </span>
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            {{ $term->start_date->format('d/m/Y') }} - {{ $term->end_date->format('d/m/Y') }}
                        </small>
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-6">
                        <div class="h5 text-primary mb-1">{{ $term->order }}</div>
                        <small class="text-muted">Ordre</small>
                    </div>
                    <div class="col-6">
                        <div class="h5 text-success mb-1">
                            @if($term->is_current)
                            <span class="badge bg-success">Actif</span>
                            @else
                            <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </div>
                        <small class="text-muted">Statut</small>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.academic.terms.edit', $term) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-pencil me-1"></i>Modifier
                    </a>
                    @if (!$term->is_current)
                    <form action="{{ route('admin.academic.terms.destroy', $term) }}" method="POST"
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce trimestre ?')">
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
        <h4 class="text-muted mt-3">Aucun trimestre</h4>
        <p class="text-muted">Créez un nouveau trimestre pour cette année scolaire.</p>
        <a href="{{ route('admin.academic.terms.create', $schoolYear) }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Créer un trimestre
        </a>
    </div>
</div>
@endif
@endsection
