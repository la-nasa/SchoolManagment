@extends('layouts.app')

@section('title', 'Erreur')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Une erreur est survenue
                    </h4>
                    <p>{{ $exception->getMessage() ?? 'Une erreur inattendue s\'est produite.' }}</p>

                    @if (config('app.debug'))
                        <hr>
                        <small class="text-muted">
                            <strong>Fichier:</strong> {{ $exception->getFile() }}<br>
                            <strong>Ligne:</strong> {{ $exception->getLine() }}
                        </small>
                    @endif
                </div>

                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-house me-2"></i>Retour au tableau de bord
                </a>
            </div>
        </div>
    </div>
@endsection
