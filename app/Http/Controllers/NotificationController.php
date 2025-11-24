<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Afficher la liste des notifications
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification, Request $request): JsonResponse
    {
        $this->authorize('update', $notification);

        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => NotificationService::getUnreadCount($request->user()->id)
            ]);
        }

        return back()->with('success', 'Notification marquée comme lue.');
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        NotificationService::markAllAsRead($user->id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0
            ]);
        }

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Supprimer une notification
     */
    public function destroy(Notification $notification, Request $request): JsonResponse
    {
        $this->authorize('delete', $notification);

        $notification->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification supprimée avec succès.'
            ]);
        }

        return back()->with('success', 'Notification supprimée avec succès.');
    }

    /**
     * Obtenir le nombre de notifications non lues (API)
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        $count = NotificationService::getUnreadCount($request->user()->id);

        return response()->json([
            'unread_count' => $count
        ]);
    }

    /**
     * Obtenir les notifications récentes (API)
     */
    public function getRecentNotifications(Request $request): JsonResponse
    {
        $notifications = NotificationService::getUnreadNotifications($request->user()->id, 5);

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'icon' => $notification->icon,
                    'priority' => $notification->priority,
                    'priority_color' => $notification->priority_color,
                    'action_url' => $notification->action_url,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'is_read' => $notification->isRead()
                ];
            })
        ]);
    }

    /**
     * Afficher les paramètres de notifications
     */
    public function settings(Request $request)
    {
        $user = $request->user();
        
        return view('notifications.settings', compact('user'));
    }

    /**
     * Mettre à jour les paramètres de notifications
     */
    public function updateSettings(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'academic_notifications' => 'boolean',
            'evaluation_notifications' => 'boolean',
            'report_notifications' => 'boolean',
            'security_notifications' => 'boolean'
        ]);

        $user->notification_settings = array_merge(
            (array) $user->notification_settings,
            $validated
        );
        
        $user->save();

        return back()->with('success', 'Paramètres de notifications mis à jour avec succès.');
    }
}