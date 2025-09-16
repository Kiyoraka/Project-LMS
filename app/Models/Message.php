<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'teacher_id',
        'student_id',
        'course_id',
        'subject',
        'message',
        'is_important',
        'is_broadcast',
        'sent_by_student',
        'attachments',
        'read_at',
        'archived_at',
        'parent_message_id',
        'message_type',
        'reply_chain',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_important' => 'boolean',
        'is_broadcast' => 'boolean',
        'sent_by_student' => 'boolean',
        'read_at' => 'datetime',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'reply_chain' => 'array',
    ];

    /**
     * Get the teacher who sent the message
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the student who received the message
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the sender of the message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the recipient of the message
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the course this message is related to
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the parent message (if this is a reply)
     */
    public function parentMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_message_id');
    }

    /**
     * Get all replies to this message
     */
    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_message_id')->orderBy('created_at', 'asc');
    }

    /**
     * Get all replies with sender information
     */
    public function repliesWithSender()
    {
        return $this->hasMany(Message::class, 'parent_message_id')
            ->with(['teacher', 'student'])
            ->orderBy('created_at', 'asc');
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read messages
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for important messages
     */
    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    /**
     * Scope for non-archived messages
     */
    public function scopeNotArchived($query)
    {
        return $query->whereNull('archived_at');
    }

    /**
     * Scope for archived messages
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Scope for messages for a specific student
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where(function ($q) use ($studentId) {
            $q->where('student_id', $studentId)
              ->orWhere('is_broadcast', true);
        });
    }

    /**
     * Scope for messages for a specific teacher (received)
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope for messages sent by a specific user
     */
    public function scopeSentBy($query, $userId)
    {
        return $query->where('student_id', $userId);
    }

    /**
     * Scope for messages received by a specific user
     */
    public function scopeReceivedBy($query, $userId)
    {
        return $query->where('teacher_id', $userId);
    }

    /**
     * Scope for searching messages
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('subject', 'like', "%{$search}%")
              ->orWhere('message', 'like', "%{$search}%");
        });
    }

    /**
     * Scope for filtering by teacher
     */
    public function scopeByTeacher($query, $teacherName)
    {
        return $query->whereHas('teacher', function ($q) use ($teacherName) {
            $q->where('name', 'like', "%{$teacherName}%");
        });
    }

    /**
     * Scope for original messages only (not replies)
     */
    public function scopeOriginalMessages($query)
    {
        return $query->where('message_type', 'original');
    }

    /**
     * Scope for replies only
     */
    public function scopeReplies($query)
    {
        return $query->where('message_type', 'reply');
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Mark message as unread
     */
    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Archive the message
     */
    public function archive()
    {
        $this->update(['archived_at' => now()]);
    }

    /**
     * Unarchive the message
     */
    public function unarchive()
    {
        $this->update(['archived_at' => null]);
    }

    /**
     * Check if message is read
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Check if message is archived
     */
    public function isArchived(): bool
    {
        return !is_null($this->archived_at);
    }

    /**
     * Get formatted created date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('M d, Y \a\t g:i A');
    }

    /**
     * Get message preview (truncated)
     */
    public function getPreviewAttribute(): string
    {
        return \Str::limit(strip_tags($this->message), 150);
    }

    /**
     * Get teacher name
     */
    public function getTeacherNameAttribute(): string
    {
        return $this->teacher?->name ?? 'Unknown Teacher';
    }

    /**
     * Get course name
     */
    public function getCourseNameAttribute(): string
    {
        return $this->course?->title ?? 'General';
    }

    /**
     * Check if this message is a reply
     */
    public function isReply(): bool
    {
        return $this->message_type === 'reply' && !is_null($this->parent_message_id);
    }

    /**
     * Check if this message has replies
     */
    public function hasReplies(): bool
    {
        return $this->replies()->count() > 0;
    }

    /**
     * Get the original message in the thread
     */
    public function getOriginalMessage()
    {
        if ($this->isReply()) {
            return $this->parentMessage;
        }
        return $this;
    }

    /**
     * Get sender name (teacher or student)
     */
    public function getSenderNameAttribute(): string
    {
        return $this->sender?->name ?? 'Unknown User';
    }

    /**
     * Get sender type
     */
    public function getSenderTypeAttribute(): string
    {
        return $this->sender?->role ?? 'unknown';
    }

    /**
     * Get recipient name
     */
    public function getRecipientNameAttribute(): string
    {
        return $this->recipient?->name ?? 'Unknown User';
    }

    /**
     * Get recipient type
     */
    public function getRecipientTypeAttribute(): string
    {
        return $this->recipient?->role ?? 'unknown';
    }

    /**
     * Create a reply to this message
     */
    public function createReply(array $replyData): Message
    {
        $originalMessage = $this->getOriginalMessage();
        
        $reply = Message::create([
            'parent_message_id' => $originalMessage->id,
            'message_type' => 'reply',
            'sender_id' => $replyData['sender_id'],
            'recipient_id' => $replyData['recipient_id'],
            'teacher_id' => $replyData['teacher_id'] ?? null,
            'student_id' => $replyData['student_id'] ?? null,
            'course_id' => $originalMessage->course_id,
            'subject' => 'Re: ' . $originalMessage->subject,
            'message' => $replyData['message'],
            'is_important' => $replyData['is_important'] ?? false,
            'is_broadcast' => false,
            'reply_chain' => $this->buildReplyChain($originalMessage),
        ]);

        return $reply;
    }

    /**
     * Check if current user can reply to this message
     */
    public function canReply($userId): bool
    {
        // Can reply if you're the recipient or part of the conversation
        return $this->recipient_id === $userId || $this->sender_id === $userId;
    }

    /**
     * Get conversation participants
     */
    public function getConversationParticipants(): array
    {
        $originalMessage = $this->getOriginalMessage();
        $allMessages = collect([$originalMessage])->merge($originalMessage->repliesWithSender);
        
        $participants = $allMessages->flatMap(function ($message) {
            return [$message->sender_id, $message->recipient_id];
        })->unique()->values()->toArray();
        
        return $participants;
    }

    /**
     * Build reply chain for threading
     */
    private function buildReplyChain(Message $originalMessage): array
    {
        $chain = [];
        $current = $originalMessage;
        
        while ($current) {
            $chain[] = [
                'id' => $current->id,
                'sender' => $current->sender_name,
                'sender_type' => $current->sender_type,
                'created_at' => $current->created_at->toISOString(),
            ];
            $current = $current->parentMessage;
        }
        
        return array_reverse($chain);
    }
} 