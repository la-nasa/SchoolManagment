<footer class="footer mt-auto py-3 bg-light border-top">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="text-muted">
                    &copy; {{ date('Y') }} {{ config('app.name', 'School Management') }}.
                    Tous droits réservés.
                </span>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="text-muted small">
                    <i class="bi bi-clock me-1"></i>
                    By People Dev Software: {{ now()->format('d/m/Y H:i') }}
                </span>
            </div>
        </div>
    </div>
</footer>
