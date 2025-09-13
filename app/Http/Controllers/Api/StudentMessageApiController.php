<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentMessageApiController extends Controller
{
    /**
     * Test endpoint to debug authentication and database connection
     */
    public function testEndpoint(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'User not authenticated',
                    'auth_check' => Auth::check(),
                    'guard' => Auth::getDefaultDriver()
                ], 401);
            }
            
            // Test basic database query
            $messageCount = Message::count();
            
            return response()->json([
                'success' => true,
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_name' => $user->name,
                'total_messages_in_db' => $messageCount,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Test endpoint failed',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Get student message statistics
     */
    public function getMessageStats(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'User not authenticated'
                ], 401);
            }
            
            $studentId = $user->id;
            
            // For now, return simple counts to avoid any query issues
            $totalMessages = 0;
            $unreadMessages = 0; 
            $messagesThisWeek = 0;
            $importantMessages = 0;
            
            try {
                // Count only RECEIVED messages (sent by teachers TO this student)
                $totalMessages = Message::where('student_id', $studentId)
                    ->where('sent_by_student', false)
                    ->count();
                    
                $unreadMessages = Message::where('student_id', $studentId)
                    ->where('sent_by_student', false)
                    ->whereNull('read_at')
                    ->count();
                
                // Get current week dates
                $startOfWeek = Carbon::now()->startOfWeek();
                $endOfWeek = Carbon::now()->endOfWeek();
                
                $messagesThisWeek = Message::where('student_id', $studentId)
                    ->where('sent_by_student', false)
                    ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->count();
                    
                $importantMessages = Message::where('student_id', $studentId)
                    ->where('sent_by_student', false)
                    ->where('is_important', true)
                    ->count();
                    
            } catch (\Exception $queryError) {
                \Log::error('Query error in message stats: ' . $queryError->getMessage());
                // Return zeros if queries fail
            }
            
            $stats = [
                'total_messages' => $totalMessages,
                'unread_messages' => $unreadMessages,
                'messages_this_week' => $messagesThisWeek,
                'important_messages' => $importantMessages,
            ];
            
            return response()->json($stats);
            
        } catch (\Exception $e) {
            \Log::error('Student message stats error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch message statistics',
                'debug' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Get paginated messages for student
     */
    public function getMessages(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'User not authenticated'
                ], 401);
            }
            
            $studentId = $user->id;
            
            try {
                // Get RECEIVED messages only (sent by teachers TO this student)
                $messages = Message::where('student_id', $studentId)
                    ->where('sent_by_student', false) // Only messages sent by teachers
                    ->with(['teacher', 'course'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();

                // Transform the data
                $transformedMessages = $messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'teacher_id' => $message->teacher_id,
                        'teacher_name' => $message->teacher?->name ?? 'Unknown Teacher',
                        'course_name' => $message->course?->title ?? 'General',
                        'course_id' => $message->course_id,
                        'subject' => $message->subject ?? 'No Subject',
                        'message' => $message->message ?? '',
                        'preview' => substr(strip_tags($message->message ?? ''), 0, 100) . '...',
                        'is_read' => !is_null($message->read_at),
                        'is_important' => $message->is_important ?? false,
                        'attachments' => $message->attachments ?? [],
                        'created_at' => $message->created_at->toISOString(),
                        'formatted_date' => $message->created_at->format('M j, Y g:i A'),
                    ];
                });

                return response()->json([
                    'data' => $transformedMessages,
                    'total' => $transformedMessages->count(),
                    'per_page' => 10,
                    'current_page' => 1
                ]);
                
            } catch (\Exception $queryError) {
                \Log::error('Query error in messages: ' . $queryError->getMessage());
                
                // Return empty data if query fails
                return response()->json([
                    'data' => [],
                    'total' => 0,
                    'per_page' => 10,
                    'current_page' => 1
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Student messages error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch messages',
                'debug' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Get a specific message
     */
    public function getMessage(Request $request, $id): JsonResponse
    {
        try {
            $studentId = Auth::id();
            
            $message = Message::with(['teacher', 'course'])
                ->forStudent($studentId)
                ->findOrFail($id);

            // Mark as read if not already read
            if (!$message->isRead()) {
                $message->markAsRead();
            }

            $messageData = [
                'id' => $message->id,
                'teacher_name' => $message->teacher_name,
                'course_name' => $message->course_name,
                'subject' => $message->subject,
                'message' => $message->message,
                'is_read' => $message->isRead(),
                'is_important' => $message->is_important,
                'attachments' => $message->attachments ?? [],
                'created_at' => $message->created_at->toISOString(),
                'formatted_date' => $message->formatted_date,
            ];

            return response()->json($messageData);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Message not found'
            ], 404);
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        try {
            $studentId = Auth::id();
            
            $message = Message::forStudent($studentId)->findOrFail($id);
            $message->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Message marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to mark message as read'
            ], 500);
        }
    }

    /**
     * Mark message as unread
     */
    public function markAsUnread(Request $request, $id): JsonResponse
    {
        try {
            $studentId = Auth::id();
            
            $message = Message::forStudent($studentId)->findOrFail($id);
            $message->markAsUnread();

            return response()->json([
                'success' => true,
                'message' => 'Message marked as unread'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to mark message as unread'
            ], 500);
        }
    }

    /**
     * Archive a message
     */
    public function archive(Request $request, $id): JsonResponse
    {
        try {
            $studentId = Auth::id();
            
            $message = Message::forStudent($studentId)->findOrFail($id);
            $message->archive();

            return response()->json([
                'success' => true,
                'message' => 'Message archived successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to archive message'
            ], 500);
        }
    }

    /**
     * Get archived messages
     */
    public function getArchivedMessages(Request $request): JsonResponse
    {
        try {
            $studentId = Auth::id();
            $perPage = $request->get('per_page', 10);

            $messages = Message::with(['teacher', 'course'])
                ->forStudent($studentId)
                ->archived()
                ->orderBy('archived_at', 'desc')
                ->paginate($perPage);

            // Transform the data
            $messages->getCollection()->transform(function ($message) {
                return [
                    'id' => $message->id,
                    'teacher_name' => $message->teacher_name,
                    'course_name' => $message->course_name,
                    'subject' => $message->subject,
                    'preview' => $message->preview,
                    'is_important' => $message->is_important,
                    'created_at' => $message->created_at->toISOString(),
                    'archived_at' => $message->archived_at->toISOString(),
                ];
            });

            return response()->json($messages);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch archived messages'
            ], 500);
        }
    }

    /**
     * Unarchive a message
     */
    public function unarchive(Request $request, $id): JsonResponse
    {
        try {
            $studentId = Auth::id();
            
            $message = Message::forStudent($studentId)
                ->archived()
                ->findOrFail($id);
            
            $message->unarchive();

            return response()->json([
                'success' => true,
                'message' => 'Message restored from archive'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to restore message'
            ], 500);
        }
    }

    /**
     * Get message view URL (for pop-up window)
     */
    public function getMessageViewUrl(Request $request, $id): JsonResponse
    {
        try {
            $studentId = Auth::id();
            
            $message = Message::forStudent($studentId)->findOrFail($id);
            
            // Mark as read if not already read
            if (!$message->isRead()) {
                $message->markAsRead();
            }

            return response()->json([
                'message_url' => route('student.message.view', $id),
                'success' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Message not found'
            ], 404);
        }
    }

    /**
     * Delete a message
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $studentId = Auth::id();
            
            // Find message that belongs to this student (either received or sent)
            $message = Message::where('student_id', $studentId)
                ->where('id', $id)
                ->first(); // Can delete both received and sent messages
            
            if (!$message) {
                return response()->json([
                    'error' => 'Message not found'
                ], 404);
            }
            
            \Log::info('Message deleted', [
                'message_id' => $id,
                'student_id' => $studentId,
                'subject' => $message->subject
            ]);
            
            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting message', [
                'message_id' => $id,
                'student_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Failed to delete message'
            ], 500);
        }
    }

    /**
     * Get teachers that student can message (from enrolled courses)
     */
    public function getAvailableTeachers(): JsonResponse
    {
        try {
            $studentId = Auth::id();
            
            // Get teachers from courses the student is enrolled in
            $teachers = \App\Models\User::whereHas('courses.enrollments', function ($query) use ($studentId) {
                $query->where('user_id', $studentId);
            })
            ->where('role', 'teacher')
            ->select('id', 'name', 'email')
            ->distinct()
            ->orderBy('name')
            ->get();

            return response()->json($teachers);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch teachers'
            ], 500);
        }
    }

    /**
     * Get courses student is enrolled in (for context when messaging teachers)
     */
    public function getEnrolledCourses(): JsonResponse
    {
        try {
            $studentId = Auth::id();
            
            $courses = \App\Models\Course::whereHas('enrollments', function ($query) use ($studentId) {
                $query->where('user_id', $studentId);
            })
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

            return response()->json($courses);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch courses'
            ], 500);
        }
    }

    /**
     * Get sent messages for student
     */
    public function getSentMessages(Request $request): JsonResponse
    {
        try {
            $studentId = Auth::id();
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search');
            $teacher = $request->get('teacher');
            $sortBy = $request->get('sort_by', 'newest');

            // Get messages SENT by this student only
            $query = Message::with(['teacher', 'course'])
                ->where('student_id', $studentId)
                ->where('sent_by_student', true); // Only messages sent by student

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%")
                      ->orWhere('message', 'like', "%{$search}%");
                });
            }

            // Apply teacher filter
            if ($teacher) {
                $query->whereHas('teacher', function ($q) use ($teacher) {
                    $q->where('name', 'like', "%{$teacher}%");
                });
            }

            // Apply sorting
            switch ($sortBy) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            // Get paginated results
            $messages = $query->paginate($perPage);

            // Transform the data
            $messages->getCollection()->transform(function ($message) {
                return [
                    'id' => $message->id,
                    'teacher_name' => $message->teacher?->name ?? 'Unknown Teacher',
                    'course_name' => $message->course?->title ?? 'General',
                    'subject' => $message->subject,
                    'message' => $message->message,
                    'preview' => substr($message->message, 0, 150),
                    'is_important' => $message->is_important,
                    'created_at' => $message->created_at->toISOString(),
                    'formatted_date' => $message->created_at->format('M j, Y g:i A'),
                ];
            });

            return response()->json($messages);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch sent messages'
            ], 500);
        }
    }

    /**
     * Send message to teacher
     */
    public function sendMessage(Request $request): JsonResponse
    {
        try {
            \Log::info('🚀 Student sending message', [
                'student_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'teacher_id' => 'required|exists:users,id',
                'course_id' => 'nullable|exists:courses,id',
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
                'is_important' => 'boolean'
            ]);

            if ($validator->fails()) {
                \Log::error('❌ Validation failed', [
                    'errors' => $validator->errors()
                ]);
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $studentId = Auth::id();
            $teacherId = $request->input('teacher_id');
            $courseId = $request->input('course_id');
            
            // Verify teacher is valid and student is enrolled in teacher's course
            $teacher = \App\Models\User::where('id', $teacherId)
                ->where('role', 'teacher')
                ->first();
            
            if (!$teacher) {
                return response()->json([
                    'error' => 'Invalid teacher selected'
                ], 403);
            }

            // If course is specified, verify student is enrolled and teacher teaches it
            if ($courseId) {
                $enrollment = \App\Models\CourseEnrollment::where('user_id', $studentId)
                    ->where('course_id', $courseId)
                    ->first();
                
                $course = \App\Models\Course::where('id', $courseId)
                    ->where('user_id', $teacherId)
                    ->first();
                
                if (!$enrollment || !$course) {
                    return response()->json([
                        'error' => 'Invalid course selection - you must be enrolled in the selected course'
                    ], 403);
                }
            }
            // Note: Removed the strict enrollment check to allow students to message any teacher

            // Create the message using the existing database structure
            $message = Message::create([
                'teacher_id' => $teacherId,
                'student_id' => $studentId,
                'course_id' => $courseId,
                'subject' => $request->input('subject'),
                'message' => $request->input('message'),
                'is_important' => $request->input('is_important', false),
                'is_broadcast' => false,
                'sent_by_student' => true, // Mark as sent by student
            ]);

            \Log::info('✅ Message created successfully', [
                'message_id' => $message->id,
                'student_id' => $studentId,
                'teacher_id' => $teacherId,
                'subject' => $request->input('subject')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully to ' . $teacher->name,
                'message_id' => $message->id
            ]);

        } catch (\Exception $e) {
            \Log::error('🚨 Error sending student message', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reply to a message
     */
    public function replyToMessage(Request $request, $messageId): JsonResponse
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'message' => 'required|string',
                'is_important' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $studentId = Auth::id();
            
            // Find the original message (should be a message received by the student)
            $originalMessage = Message::where('id', $messageId)
                ->where('student_id', $studentId)
                ->where('sent_by_student', false) // Only allow replies to received messages
                ->first();
            
            if (!$originalMessage) {
                return response()->json([
                    'error' => 'Message not found'
                ], 404);
            }

            // Create the reply using the existing database structure
            $reply = Message::create([
                'teacher_id' => $originalMessage->teacher_id,
                'student_id' => $studentId,
                'course_id' => $originalMessage->course_id,
                'subject' => 'Re: ' . $originalMessage->subject,
                'message' => $request->input('message'),
                'is_important' => $request->input('is_important', false),
                'is_broadcast' => false,
                'sent_by_student' => true, // Mark reply as sent by student
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reply sent successfully',
                'reply_id' => $reply->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Error sending reply: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to send reply'
            ], 500);
        }
    }
} 