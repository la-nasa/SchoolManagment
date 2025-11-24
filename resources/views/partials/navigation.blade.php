@php
    use Illuminate\Support\Facades\Auth;
@endphp

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <i class="bi bi-mortarboard-fill me-2"></i>
            <strong>{{ config('app.name', 'School Management') }}</strong>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <!-- Navigation principale -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2 me-1"></i>Tableau de bord
                    </a>
                </li>
            </ul>

            <!-- Navigation utilisateur -->
            <ul class="navbar-nav ms-auto">
                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span id="notification-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
                            0
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                        <h6 class="dropdown-header">Notifications</h6>
                        <div id="notifications-list">
                            <div class="dropdown-item-text text-center py-3">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="{{ route('notifications') }}">
                            Voir toutes les notifications
                        </a>
                    </div>
                </li>

                <!-- Utilisateur -->

                @auth

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        @php
                            $user = auth()->user();
                            $photoUrl = $user->photo_url ?? '/images/avatar.png';
                            $userName =  $user->name ?? 'Utilisateur';
                            $roleName = $user->role_name ?? 'Utilisateur';
                        @endphp
                        <img src="{{$photoUrl}}" alt="{{ $userName }}" class="rounded-circle me-2" width="32" height="32">
                        <span class="d-none d-md-inline">{{ $userName }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <div class="dropdown-header">
                            <div class="d-flex align-items-center">
                                <img src="{{ $photoUrl }}" alt="{{ $userName }}" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <div class="fw-bold">{{ $userName }}</div>
                                    <small class="text-muted">{{ $roleName }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                            <i class="bi bi-person me-2"></i>Mon profil
                        </a>
                        <a class="dropdown-item" href="{{ route('password.change') }}">
                            <i class="bi bi-key me-2"></i>Changer mot de passe
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                            </button>
                        </form>
                        @endauth
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

