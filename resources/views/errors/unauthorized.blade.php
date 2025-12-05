<!-- filepath: resources/views/errors/unauthorized.blade.php -->
@extends('layouts.app')

@section('title', 'Accès Refusé')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
            <div class="col-md-6 text-center">
                <div class="mb-4">
                    <i class="bi bi-shield-exclamation display-1 text-danger"></i>
                </div>
                <h1 class="display-3 fw-bold mb-3">403</h1>
                <h2 class="mb-3 text-muted">Accès Refusé</h2>
                <p class="lead mb-4">
                    Vous n'avez pas la permission d'accéder à cette ressource.
                </p>
                <p class="text-muted mb-4">
                    @if ($exception && $exception->getMessage())
                        {{ $exception->getMessage() }}
                    @else
                        Votre compte n'a pas les permissions nécessaires pour effectuer cette action.
                    @endif
                </p>
                <div class="btn-group">
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="bi bi-house me-2"></i>Accueil
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
