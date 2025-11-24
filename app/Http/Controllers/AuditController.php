<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Vérifier les permissions
        if (!Gate::allows('view-audit-trail')) {
            abort(403, 'Accès non autorisé au journal d\'audit.');
        }

        $query = Audit::with('user')->latest();

        // Filtres
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $audits = $query->paginate(50);
        $users = User::whereIn('id', Audit::distinct()->pluck('user_id'))->get();
        $events = Audit::distinct()->pluck('event');

        return view('audit.index', compact('audits', 'users', 'events'));
    }

    public function show(Audit $audit)
    {
        if (!Gate::allows('view-audit-trail')) {
            abort(403, 'Accès non autorisé au journal d\'audit.');
        }

        return view('audit.show', compact('audit'));
    }

    public function userActivity(User $user)
    {
        if (!Gate::allows('view-audit-trail')) {
            abort(403, 'Accès non autorisé au journal d\'audit.');
        }

        $audits = Audit::where('user_id', $user->id)
            ->with('user')
            ->latest()
            ->paginate(50);

        return view('audit.user-activity', compact('audits', 'user'));
    }

    public function export(Request $request)
    {
        if (!Gate::allows('export-audit-trail')) {
            abort(403, 'Accès non autorisé à l\'exportation du journal d\'audit.');
        }

        $query = Audit::with('user');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $audits = $query->get();

        // Logique d'exportation (CSV ou Excel)
        return response()->streamDownload(function () use ($audits) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Utilisateur', 'Événement', 'Modèle', 'Date', 'IP', 'User Agent']);

            foreach ($audits as $audit) {
                fputcsv($handle, [
                    $audit->id,
                    $audit->user ? $audit->user->name : 'Système',
                    $audit->event,
                    $audit->auditable_type,
                    $audit->created_at->format('d/m/Y H:i'),
                    $audit->ip_address,
                    $audit->user_agent
                ]);
            }
            fclose($handle);
        }, 'audit-trail-' . date('Y-m-d') . '.csv');
    }
}
