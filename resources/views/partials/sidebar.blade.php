<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse shadow-sm">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <!-- Tableau de bord -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Tableau de bord
                </a>
            </li>
@auth


            <!-- Menu Administrateur -->
            @if(auth()->user()->isAdministrator())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people me-2"></i>
                    Enseignants
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}" href="{{ route('admin.classes.index') }}">
                    <i class="bi bi-building me-2"></i>
                    Classes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}" href="{{ route('admin.subjects.index') }}">
                    <i class="bi bi-journal-text me-2"></i>
                    Matières
                </a>
            </li>
            @endif

            <!-- Menu commun -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('*students*') ? 'active' : '' }}" href="{{ route('students.index') }}">
                    <i class="bi bi-person-video3 me-2"></i>
                    Élèves
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('*evaluations*') ? 'active' : '' }}" href="{{ route('evaluations.index') }}">
                    <i class="bi bi-clipboard-check me-2"></i>
                    Évaluations
                </a>
            </li>

            <!-- Menu Enseignant/Titulaire -->
            @if(auth()->user()->isTeacher() || auth()->user()->isTitularTeacher())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('*marks*') ? 'active' : '' }}" href="{{ route('teacher.evaluations') }}">
                    <i class="bi bi-pencil-square me-2"></i>
                    Saisie des notes
                </a>
            </li>
            @endif

            <!-- Menu Titulaire -->
            @if(auth()->user()->isTitularTeacher())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('titular.my-class*') ? 'active' : '' }}" href="{{ route('titular.my-class') }}">
                    <i class="bi bi-house-door me-2"></i>
                    Ma classe
                </a>
            </li>
            @endif

            <!-- Rapports -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {{ request()->routeIs('*reports*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-graph-up me-2"></i>
                    Rapports
                </a>
                <ul class="dropdown-menu">
                    @if(auth()->user()->isAdministrator() || auth()->user()->isDirector())
                    <li><a class="dropdown-item" href="{{ route('admin.reports.school') }}">Rapport établissement</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.reports.performance') }}">Performance</a></li>
                    @endif
                    @if(auth()->user()->isTitularTeacher())
                    <li><a class="dropdown-item" href="{{ route('titular.reports.class') }}">Rapport de classe</a></li>
                    @endif
                    @if(auth()->user()->isSecretary())
                    <li><a class="dropdown-item" href="{{ route('secretary.reports.school') }}">Rapports administratifs</a></li>
                    @endif
                </ul>
            </li>

            <!-- Audit (Admin et Directeur) -->
            @if(auth()->user()->canAccessAudit())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('*audit*') ? 'active' : '' }}" href="{{ route('admin.audit.index') }}">
                    <i class="bi bi-shield-check me-2"></i>
                    Journal d'audit
                </a>
            </li>
            @endif

            <!-- Administration -->
            @if(auth()->user()->isAdministrator())
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.settings*') || request()->routeIs('admin.academic*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-gear me-2"></i>
                    Administration
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.settings') }}">Paramètres</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.academic.school-years') }}">Années scolaires</a></li>
                </ul>
            </li>
            @endif
        </ul>
@endauth
        <!-- Indicateur d'année scolaire -->
        <div class="mt-5 p-3 bg-light rounded">
            <small class="text-muted d-block">Année scolaire</small>
            <strong class="d-block">
                @php
                    $currentYear = \App\Models\SchoolYear::current();
                    $currentTerm = \App\Models\Term::current();
                @endphp
                {{ $currentYear->year ?? 'Non définie' }}
            </strong>
            <small class="text-muted">
                {{ $currentTerm->name ?? 'Trimestre non défini' }}
            </small>
        </div>
    </div>
</nav>
