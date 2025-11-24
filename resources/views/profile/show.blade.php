@extends('layouts.app')

@section('title', 'Mon Profil')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
                        <li class="breadcrumb-item active">Mon Profil</li>
                    </ol>
                </div>
                <h4 class="page-title">Mon Profil</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Colonne de gauche - Informations du profil -->
        <div class="col-lg-4 col-xl-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="{{ $user->avatar ? Storage::url($user->avatar) : asset('assets/images/users/avatar-default.png') }}" 
                             alt="Avatar" class="rounded-circle avatar-xl img-thumbnail">
                    </div>
                    
                    <h4 class="mb-1">{{ $user->getFullName() }}</h4>
                    <p class="text-muted">
                        @foreach($user->roles as $role)
                            <span class="badge bg-primary">{{ $role->name }}</span>
                        @endforeach
                    </p>
                    
                    <p class="text-muted mb-3">
                        @if($user->hasRole('enseignant') && $user->matricule)
                        <i class="fas fa-id-card me-1"></i>Matricule: {{ $user->matricule }}
                        @endif
                    </p>

                    <div class="mt-3">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary me-2">
                            <i class="fas fa-edit me-1"></i>Modifier
                        </a>
                        <a href="{{ route('password.change') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-lock me-1"></i>Mot de passe
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            @if(!empty($stats))
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Statistiques</h5>
                    <div class="list-group list-group-flush">
                        @foreach($stats as $key => $value)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-capitalize">{{ str_replace('_', ' ', $key) }}</span>
                            <span class="badge bg-primary rounded-pill">{{ $value }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Colonne de droite - Détails du profil -->
        <div class="col-lg-8 col-xl-9">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Informations personnelles</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nom complet</label>
                                <p class="form-control-plaintext">{{ $user->getFullName() }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <p class="form-control-plaintext">{{ $user->email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @if($user->phone)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Téléphone</label>
                                <p class="form-control-plaintext">{{ $user->phone }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($user->date_of_birth)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Date de naissance</label>
                                <p class="form-control-plaintext">{{ $user->date_of_birth->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($user->gender)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Genre</label>
                                <p class="form-control-plaintext">
                                    @if($user->gender === 'male')
                                        Masculin
                                    @elseif($user->gender === 'female')
                                        Féminin
                                    @else
                                        Autre
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($user->address)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Adresse</label>
                        <p class="form-control-plaintext">{{ $user->address }}</p>
                    </div>
                    @endif

                    @if($user->bio)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Biographie</label>
                        <p class="form-control-plaintext">{{ $user->bio }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Affectations (pour les enseignants) -->
            @if($user->hasRole('enseignant') || $user->hasRole('enseignant titulaire'))
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Mes affectations</h5>
                    
                    @if($user->teacherAssignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Classe</th>
                                        <th>Matière</th>
                                        <th>Type</th>
                                        <th>Année scolaire</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->teacherAssignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->classe->full_name }}</td>
                                        <td>{{ $assignment->subject->name }}</td>
                                        <td>
                                            @if($assignment->is_class_teacher)
                                                <span class="badge bg-primary">Titulaire</span>
                                            @else
                                                <span class="badge bg-secondary">Enseignant</span>
                                            @endif
                                        </td>
                                        <td>{{ $assignment->schoolYear->year }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Aucune affectation pour le moment.</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Informations de compte -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Informations du compte</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Date de création</label>
                                <p class="form-control-plaintext">{{ $user->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Dernière connexion</label>
                                <p class="form-control-plaintext">
                                    @if($user->last_login_at)
                                        {{ $user->last_login_at->format('d/m/Y à H:i') }}
                                    @else
                                        Jamais connecté
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Statut du compte</label>
                                <p class="form-control-plaintext">
                                    @if($user->is_active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-danger">Inactif</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email vérifié</label>
                                <p class="form-control-plaintext">
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success">Vérifié</span>
                                    @else
                                        <span class="badge bg-warning">Non vérifié</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection