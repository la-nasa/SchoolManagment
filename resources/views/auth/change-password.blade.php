@extends('layouts.app')

@section('title', 'Changer le mot de passe')
@section('page-title', 'Changer le mot de passe')

@section('breadcrumbs')
<li class="breadcrumb-item active">Changer mot de passe</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-key me-2"></i>Changer votre mot de passe
                </h5>
            </div>
            <div class="card-body">
                @if(session('warning'))
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.change') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mot de passe actuel</label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password" name="current_password" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nouveau mot de passe</label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                                   id="new_password" name="new_password" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            Le mot de passe doit contenir au moins 8 caractères avec des lettres et des chiffres.
                        </div>
                        @error('new_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="new_password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                        <div class="input-group">
                            <input type="password" class="form-control"
                                   id="new_password_confirmation" name="new_password_confirmation" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Changer le mot de passe
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Retour
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
        });
    });

    // Password strength indicator
    const passwordInput = document.getElementById('new_password');
    const requirements = {
        length: document.createElement('div'),
        letter: document.createElement('div'),
        number: document.createElement('div')
    };

    Object.keys(requirements).forEach(key => {
        requirements[key].className = 'form-text';
        passwordInput.parentNode.appendChild(requirements[key]);
    });

    passwordInput.addEventListener('input', function() {
        const password = this.value;

        requirements.length.innerHTML = password.length >= 8 ?
            '<i class="bi bi-check-circle text-success me-1"></i>8 caractères minimum' :
            '<i class="bi bi-x-circle text-danger me-1"></i>8 caractères minimum';

        requirements.letter.innerHTML = /[a-zA-Z]/.test(password) ?
            '<i class="bi bi-check-circle text-success me-1"></i>Lettre requis' :
            '<i class="bi bi-x-circle text-danger me-1"></i>Lettre requis';

        requirements.number.innerHTML = /[0-9]/.test(password) ?
            '<i class="bi bi-check-circle text-success me-1"></i>Chiffre requis' :
            '<i class="bi bi-x-circle text-danger me-1"></i>Chiffre requis';
    });
</script>
@endpush
