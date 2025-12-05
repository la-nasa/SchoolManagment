<!-- filepath: resources/views/errors/500.blade.php -->
@extends('layouts.app')

@section('title', 'Erreur Serveur')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="error-template">
                    <h1 class="display-1 fw-bold text-danger">500</h1>
                    <h2 class="h3 mb-3">Erreur Serveur</h2>
                    <div class="error-details mb-4">
                        <p class="text-muted fs-5">
                            Une erreur interne s'est produite. Notre équipe technique a été notifiée.
                        </p>
                    </div>

                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Erreur:</strong> Veuillez réessayer plus tard.
                    </div>

                    <div class="btn-group gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                            <i class="bi bi-house-door me-2"></i>Tableau de Bord
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-house me-2"></i>Accueil
                        </a>
                    </div>
                </div>

                <div class="mt-5">
                    <i class="bi bi-exclamation-circle display-1 text-danger opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
@endsection
