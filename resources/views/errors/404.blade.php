<!-- filepath: resources/views/errors/404.blade.php -->
@extends('layouts.app')

@section('title', 'Page Non Trouvée')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-template">
                <h1 class="display-1 fw-bold text-danger">404</h1>
                <h2 class="h3 mb-3">Page Non Trouvée</h2>
                <div class="error-details mb-4">
                    <p class="text-muted fs-5">
                        La page que vous recherchez n'existe pas ou a été supprimée.
                    </p>
                </div>

                <div class="btn-group gap-2">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                        <i class="bi bi-house-door me-2"></i>Tableau de Bord
                    </a>
                    <button onclick="history.back()" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Retour
                    </button>
                </div>
            </div>

            <div class="mt-5">
                <svg width="200" height="200" viewBox="0 0 200 200" class="text-muted">
                    <circle cx="100" cy="100" r="90" fill="none" stroke="currentColor" stroke-width="2" opacity="0.1"/>
                    <text x="100" y="110" text-anchor="middle" font-size="80" fill="currentColor" opacity="0.2">?</text>
                </svg>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .error-template {
        padding: 40px 15px;
        text-align: center;
    }
</style>
@endpush