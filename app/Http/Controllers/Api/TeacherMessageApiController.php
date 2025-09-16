<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TeacherMessageApiController extends Controller
{
    /**
     * Get students for messaging (enrolled in teacher's courses)
     */
    public function getStudents(): JsonResponse
    {
        try {
            $teacherId = Auth::id();
            
            // Get all students enrolled in this teacher's courses
            $students = User::whereHas('enrollments.course', function ($query) use ($teacherId) {
                $query->where('user_id', $teacherId);
            })
            ->where('role', 'student')
            ->select('id', 'name', 'email')
            ->distinct()
            ->orderBy('name')
            ->get();

            return response()->json($students);

        } catch (\Exception $e) {
            \Log::error('Error fetching students for teacher: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch students'
            ], 500);
        }
    }

    /**
     * Get teacher's courses for messaging
     */
    public function getCourses(): JsonResponse
    {
        try {
            $teacherId = Auth::id();
            
            $courses = Course::where('user_id', $teacherId)
                ->select('id', 'title')
                ->orderBy('title')
                ->get();

            return response()->json($courses);

        } catch (\Exception $e) {
            \Log::error('Error fetching courses for teacher: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch courses'
            ], 500);
        }
    }

    /**
     * Send message to student(s)
     */
    public function sendMessage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'recipients' => 'required|array|min:1',
                'recipients.*' => 'exists:users,id',
                'course_id' => 'nullable|exists:courses,id',
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
                'is_important' => 'boolean',
                'is_broadcast' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $teacherId = Auth::id();
            $recipients = $request->input('recipients');
            $courseId = $request->input('course_id');
            $subject = $request->input('subject');
            $messageContent = $request->input('message');
            $isImportant = $request->input('is_important', false);
            $isBroadcast = $request->input('is_broadcast', false);

            // Verify course belongs to teacher if specified
            if ($courseId) {
                $course = Course::where('id', $courseId)
                    ->where('user_id', $teacherId)
                    ->first();
                
                if (!$course) {
                    return response()->json([
                        'error' => 'Invalid course selected'
                    ], 403);
                }
            }

            // Create messages for each recipient
            $messagesCreated = 0;
            foreach ($recipients as $recipientId) {
                // Verify recipient is a student
                $student = User::where('id', $recipientId)
                    ->where('role', 'student')
                    ->first();

                if (!$student) {
                    continue; // Skip invalid recipients
                }

                Message::create([
                    'sender_id' => $teacherId,         // Teacher is the sender
                    'recipient_id' => $recipientId,   // Student is the recipient
                    'teacher_id' => $teacherId,
                    'student_id' => $recipientId,
                    'course_id' => $courseId,
                    'subject' => $subject,
                    'message' => $messageContent,
                    'is_important' => $isImportant,
                    'is_broadcast' => $isBroadcast,
                    'sent_by_student' => false,       // Mark as sent by teacher
                ]);

                $messagesCreated++;
            }

            return response()->json([
                'success' => true,
                'message' => "Message sent successfully to {$messagesCreated} student(s)",
                'messages_created' => $messagesCreated
            ]);

        } catch (\Exception $e) {
            \Log::error('Error sending message: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to send message'
            ], 500);
        }
    }

    /**
     * Send broadcast message to all students in a course
     */
    public function sendBroadcastMessage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:courses,id',
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
                'is_important' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $teacherId = Auth::id();
            $courseId = $request->input('course_id');
            
            // Verify course belongs to teacher
            $course = Course::where('id', $courseId)
                ->where('user_id', $teacherId)
                ->first();
            
            if (!$course) {
                return response()->json([
                    'error' => 'Course not found or access denied'
                ], 403);
            }

            // Get all enrolled students
            $enrolledStudents = CourseEnrollment::where('course_id', $courseId)
                ->with('user')
                ->get()
                ->pluck('user');

            if ($enrolledStudents->isEmpty()) {
                return response()->json([
                    'error' => 'No students enrolled in this course'
                ], 400);
            }

            $subject = $request->input('subject');
            $messageContent = $request->input('message');
            $isImportant = $request->input('is_important', false);

            // Create messages for all enrolled students
            $messagesCreated = 0;
            foreach ($enrolledStudents as $student) {
                Message::create([
                    'sender_id' => $teacherId,         // Teacher is the sender
                    'recipient_id' => $student->id,    // Student is the recipient
                    'teacher_id' => $teacherId,
                    'student_id' => $student->id,
                    'course_id' => $courseId,
                    'subject' => $subject,
                    'message' => $messageContent,
                    'is_important' => $isImportant,
                    'is_broadcast' => true,
                    'sent_by_student' => false,        // Mark as sent by teacher
                ]);

                $messagesCreated++;
            }

            return response()->json([
                'success' => true,
                'message' => "Broadcast message sent to {$messagesCreated} student(s) in {$course->title}",
                'messages_created' => $messagesCreated,
                'course_title' => $course->title
            ]);

        } catch (\Exception $e) {
            \Log::error('Error sending broadcast message: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to send broadcast message'
            ], 500);
        }
    }

    /**
     * Get sent messages for teacher (messages FROM teacher TO students)
     */
    public function getSentMessages(Request $request): JsonResponse
    {
        try {
            $teacherId = Auth::id();
            $perPage = $request->get('per_page', 10);
            
            // Get messages where teacher sent to students
            $messages = Message::with(['student', 'course'])
                ->where('teacher_id', $teacherId)
                ->where(function ($query) {
                    // Ensure these are messages sent BY teacher (not received by teacher)
                    $query->where('sent_by_student', false)
                          ->orWhereNull('sent_by_student');
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Transform the data
            $messages->getCollection()->transform(function ($message) {
                return [
                    'id' => $message->id,
                    'student_name' => $message->student?->name ?? 'Unknown Student',
                    'course_name' => $message->course?->title ?? 'General',
                    'subject' => $message->subject,
                    'message' => $message->message,
                    'preview' => $message->preview,
                    'is_read' => $message->isRead(),
                    'is_important' => $message->is_important,
                    'is_broadcast' => $message->is_broadcast,
                    'created_at' => $message->created_at->toISOString(),
                    'formatted_date' => $message->formatted_date,
                ];
            });

            return response()->json($messages);

        } catch (\Exception $e) {
            \Log::error('Error fetching sent messages: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch sent messages'
            ], 500);
        }
    }

    /**
     * Get students enrolled in a specific course
     */
    public function getCourseStudents(Request $request, $courseId): JsonResponse
    {
        try {
            $teacherId = Auth::id();
            
            // Verify course belongs to teacher
            $course = Course::where('id', $courseId)
                ->where('user_id', $teacherId)
                ->first();
            
            if (!$course) {
                return response()->json([
                    'error' => 'Course not found or access denied'
                ], 403);
            }

            $students = User::whereHas('enrollments', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->where('role', 'student')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

            return response()->json([
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title
                ],
                'students' => $students
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching course students: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch course students'
            ], 500);
        }
    }

    /**
     * Get teacher message statistics
     */
    public function getMessageStats(): JsonResponse
    {
        try {
            $teacherId = Auth::id();
            
            // Get current date ranges
            $startOfWeek = \Carbon\Carbon::now()->startOfWeek();
            $endOfWeek = \Carbon\Carbon::now()->endOfWeek();
            
            // Build queries for different message types
            $receivedQuery = Message::where('teacher_id', $teacherId)
                ->where('sent_by_student', true)
                ->notArchived();
                
            $sentQuery = Message::where('teacher_id', $teacherId)
                ->where(function ($query) {
                    $query->where('sent_by_student', false)
                          ->orWhereNull('sent_by_student');
                });
            
            $stats = [
                'received_messages' => (clone $receivedQuery)->count(),
                'unread_messages' => (clone $receivedQuery)->unread()->count(),
                'sent_messages' => (clone $sentQuery)->count(),
                'messages_this_week' => (clone $receivedQuery)
                    ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->count(),
                'important_messages' => (clone $receivedQuery)->important()->count(),
            ];
            
            return response()->json($stats);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch message statistics'
            ], 500);
        }
    }

    /**
     * Get received messages for teacher (messages FROM students TO teacher)
     */
    public function getReceivedMessages(Request $request): JsonResponse
    {
        try {
            $teacherId = Auth::id();
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search');
            $status = $request->get('status');
            $student = $request->get('student');
            $sortBy = $request->get('sort_by', 'newest');

            // Get messages FROM students TO this teacher
            $query = Message::with(['student', 'course'])
                ->where('teacher_id', $teacherId)
                ->where('sent_by_student', true)  // Only messages sent BY students
                ->notArchived();

            // Apply search filter
            if ($search) {
                $query->search($search);
            }

            // Apply status filter
            switch ($status) {
                case 'unread':
                    $query->unread();
                    break;
                case 'read':
                    $query->read();
                    break;
                case 'important':
                    $query->important();
                    break;
            }

            // Apply student filter
            if ($student) {
                $query->whereHas('student', function ($q) use ($student) {
                    $q->where('name', 'like', "%{$student}%");
                });
            }

            // Apply sorting
            switch ($sortBy) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'priority':
                    $query->orderBy('is_important', 'desc')
                          ->orderBy('created_at', 'desc');
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
                    'sender_name' => $message->student?->name ?? 'Unknown Student',
                    'sender_type' => 'student',
                    'course_name' => $message->course?->title ?? 'General',
                    'subject' => $message->subject,
                    'message' => $message->message,
                    'preview' => $message->preview,
                    'is_read' => $message->isRead(),
                    'is_important' => $message->is_important,
                    'attachments' => $message->attachments ?? [],
                    'created_at' => $message->created_at->toISOString(),
                    'formatted_date' => $message->formatted_date,
                    'can_reply' => true,
                ];
            });

            return response()->json($messages);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch received messages'
            ], 500);
        }
    }

    /**
     * Get a specific received message
     */
    public function getReceivedMessage(Request $request, $id): JsonResponse
    {
        try {
            $teacherId = Auth::id();
            
            $message = Message::with(['sender', 'course', 'repliesWithSender'])
                ->forTeacher($teacherId)
                ->findOrFail($id);

            // Mark as read if not already read
            if (!$message->isRead()) {
                $message->markAsRead();
            }

            $messageData = [
                'id' => $message->id,
                'sender_name' => $message->sender_name,
                'sender_type' => $message->sender_type,
                'course_name' => $message->course?->title ?? 'General',
                'subject' => $message->subject,
                'message' => $message->message,
                'is_read' => $message->isRead(),
                'is_important' => $message->is_important,
                'attachments' => $message->attachments ?? [],
                'created_at' => $message->created_at->toISOString(),
                'formatted_date' => $message->formatted_date,
                'can_reply' => $message->canReply($teacherId),
                'replies' => $message->repliesWithSender->map(function ($reply) {
                    return [
                        'id' => $reply->id,
                        'sender_name' => $reply->sender_name,
                        'sender_type' => $reply->sender_type,
                        'message' => $reply->message,
                        'created_at' => $reply->created_at->toISOString(),
                        'formatted_date' => $reply->formatted_date,
                    ];
                })
            ];

            return response()->json($messageData);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Message not found'
            ], 404);
        }
    }

    /**
     * Reply to a student message
     */
    public function replyToMessage(Request $request, $messageId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string',
                'is_important' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $teacherId = Auth::id();
            
            // Find the original message and verify teacher can reply to it
            $originalMessage = Message::with(['sender', 'recipient'])
                ->where('id', $messageId)
                ->first();
            
            if (!$originalMessage) {
                return response()->json([
                    'error' => 'Message not found'
                ], 404);
            }

            if (!$originalMessage->canReply($teacherId)) {
                return response()->json([
                    'error' => 'You cannot reply to this message'
                ], 403);
            }

            // Determine the recipient (if teacher sent original, reply goes to student and vice versa)
            $recipientId = $originalMessage->sender_id === $teacherId 
                ? $originalMessage->recipient_id 
                : $originalMessage->sender_id;

            // Create the reply
            $reply = $originalMessage->createReply([
                'sender_id' => $teacherId,
                'recipient_id' => $recipientId,
                'teacher_id' => $originalMessage->teacher_id,
                'student_id' => $originalMessage->student_id,
                'message' => $request->input('message'),
                'is_important' => $request->input('is_important', false)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reply sent successfully',
                'reply_id' => $reply->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Error sending teacher reply: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to send reply'
            ], 500);
        }
    }

    /**
     * Mark received message as read
     */
    public function markReceivedAsRead(Request $request, $id): JsonResponse
    {
        try {
            $teacherId = Auth::id();
            
            $message = Message::forTeacher($teacherId)->findOrFail($id);
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
     * Archive a received message
     */
    public function archiveReceived(Request $request, $id): JsonResponse
    {
        try {
            $teacherId = Auth::id();
            
            $message = Message::forTeacher($teacherId)->findOrFail($id);
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
     * Delete a message (for both sent and received)
     */
    public function deleteMessage(Request $request, $id): JsonResponse
    {
        try {
            $teacherId = Auth::id();
            
            // Find the message and verify teacher can delete it
            $message = Message::where('id', $id)
                ->where('teacher_id', $teacherId)  // Teacher owns this message thread
                ->first();

            if (!$message) {
                return response()->json([
                    'error' => 'Message not found or you do not have permission to delete it'
                ], 404);
            }

            // Delete the message
            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting teacher message: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete message'
            ], 500);
        }
    }
} 