<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'data',
        'read_at',
        'action_url',
        'priority',
        'expires_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    // Types de notifications
    const TYPE_SYSTEM = 'system';
    const TYPE_ACADEMIC = 'academic';
    const TYPE_EVALUATION = 'evaluation';
    const TYPE_REPORT = 'report';
    const TYPE_SECURITY = 'security';

    // Priorités
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Méthodes utilitaires
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    public function isRead()
    {
        return !is_null($this->read_at);
    }

    public function isUnread()
    {
        return is_null($this->read_at);
    }

    public function getIconAttribute()
    {
        return match($this->type) {
            self::TYPE_ACADEMIC => 'fas fa-graduation-cap',
            self::TYPE_EVALUATION => 'fas fa-clipboard-check',
            self::TYPE_REPORT => 'fas fa-chart-bar',
            self::TYPE_SECURITY => 'fas fa-shield-alt',
            default => 'fas fa-bell'
        };
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'blue',
            self::PRIORITY_NORMAL => 'gray',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_URGENT => 'red',
            default => 'gray'
        };
    }
}