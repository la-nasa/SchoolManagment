<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Créer une notification
     */
    public static function create($userId, $title, $message, $type = Notification::TYPE_SYSTEM, $data = [], $actionUrl = null, $priority = Notification::PRIORITY_NORMAL)
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'action_url' => $actionUrl,
            'priority' => $priority,
        ]);
    }

    /**
     * Notifier un utilisateur
     */
    public static function notifyUser(User $user, $title, $message, $type = Notification::TYPE_SYSTEM, $data = [], $actionUrl = null, $priority = Notification::PRIORITY_NORMAL)
    {
        return self::create($user->id, $title, $message, $type, $data, $actionUrl, $priority);
    }

    /**
     * Notifier plusieurs utilisateurs
     */
    public static function notifyUsers($users, $title, $message, $type = Notification::TYPE_SYSTEM, $data = [], $actionUrl = null, $priority = Notification::PRIORITY_NORMAL)
    {
        $notifications = [];
        
        foreach ($users as $user) {
            $notifications[] = self::create($user->id, $title, $message, $type, $data, $actionUrl, $priority);
        }
        
        return $notifications;
    }

    /**
     * Notifier par rôle
     */
    public static function notifyByRole($role, $title, $message, $type = Notification::TYPE_SYSTEM, $data = [], $actionUrl = null, $priority = Notification::PRIORITY_NORMAL)
    {
        $users = User::role($role)->get();
        return self::notifyUsers($users, $title, $message, $type, $data, $actionUrl, $priority);
    }

    /**
     * Notification d'évaluation manquante
     */
    public static function notifyMissingEvaluation($teacher, $evaluation)
    {
        return self::notifyUser(
            $teacher,
            'Évaluation en attente',
            "L'évaluation '{$evaluation->title}' pour la classe {$evaluation->classe->full_name} n'a pas encore de notes saisies.",
            Notification::TYPE_EVALUATION,
            ['evaluation_id' => $evaluation->id, 'class_id' => $evaluation->class_id],
            route('teacher.evaluations.show', $evaluation),
            Notification::PRIORITY_HIGH
        );
    }

    /**
     * Notification de nouveau bulletin généré
     */
    public static function notifyBulletinGenerated($student, $term, $schoolYear)
    {
        $teachers = $student->classe->teacherAssignments()->with('teacher')->get()->pluck('teacher');
        
        return self::notifyUsers(
            $teachers,
            'Bulletin généré',
            "Le bulletin du {$term->name} trimestre pour {$student->getFullName()} a été généré.",
            Notification::TYPE_REPORT,
            ['student_id' => $student->id, 'term_id' => $term->id],
            route('reports.bulletin', $student),
            Notification::PRIORITY_NORMAL
        );
    }

    /**
     * Notification de nouvelle affectation
     */
    public static function notifyTeacherAssignment($teacher, $assignment)
    {
        return self::notifyUser(
            $teacher,
            'Nouvelle affectation',
            "Vous avez été assigné à enseigner {$assignment->subject->name} en {$assignment->classe->full_name}.",
            Notification::TYPE_ACADEMIC,
            ['assignment_id' => $assignment->id],
            route('teacher.my-subjects'),
            Notification::PRIORITY_NORMAL
        );
    }

    /**
     * Notification de sécurité (connexion suspecte)
     */
    public static function notifySuspiciousLogin($user, $ipAddress, $location)
    {
        return self::notifyUser(
            $user,
            'Connexion suspecte détectée',
            "Une connexion depuis {$ipAddress} ({$location}) a été détectée sur votre compte.",
            Notification::TYPE_SECURITY,
            ['ip_address' => $ipAddress, 'location' => $location],
            route('profile'),
            Notification::PRIORITY_URGENT
        );
    }

    /**
     * Obtenir les notifications non lues d'un utilisateur
     */
    public static function getUnreadNotifications($userId, $limit = 10)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Compter les notifications non lues
     */
    public static function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->count();
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public static function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Supprimer les notifications expirées
     */
    public static function cleanupExpired()
    {
        return Notification::where('expires_at', '<', now())->delete();
    }
}