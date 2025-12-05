<!-- filepath: resources/views/errors/403.blade.php -->
@extends('layouts.app')

@section('title', 'Accès Refusé')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="error-template">
                    <h1 class="display-1 fw-bold text-warning">403</h1>
                    <h2 class="h3 mb-3">Accès Refusé</h2>
                    <div class="error-details mb-4">
                        <p class="text-muted fs-5">
                            Vous n'avez pas les permissions nécessaires pour accéder à cette ressource.
                        </p>
                    </div>

                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-shield-exclamation me-2"></i>
                        <strong>Attention:</strong> Cette action est réservée à certains rôles.
                    </div>

                    <div class="btn-group gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                            <i class="bi bi-house-door me-2"></i>Retour au Tableau de Bord
                        </a>
                        <button onclick="history.back()" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Retour
                        </button>
                    </div>
                </div>

                <div class="mt-5">
                    <i class="bi bi-shield-lock display-1 text-warning opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
@endsection
