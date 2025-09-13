<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get teachers and students
        $teachers = User::where('role', 'teacher')->get();
        $students = User::where('role', 'student')->get();
        $courses = Course::all();

        if ($teachers->isEmpty() || $students->isEmpty()) {
            $this->command->info('No teachers or students found. Please seed users first.');
            return;
        }

        $this->command->info('Creating sample messages...');

        $sampleMessages = [
            [
                'subject' => 'Assignment Due Reminder',
                'message' => '<p>Dear students,</p><p>This is a friendly reminder that your calculus assignment is due next Monday. Please make sure to:</p><ul><li>Show all your work clearly</li><li>Double-check your calculations</li><li>Submit before 11:59 PM</li></ul><p>If you have any questions, please don\'t hesitate to reach out.</p><p>Best regards,<br>Prof. Smith</p>',
                'is_important' => true,
                'is_broadcast' => true,
            ],
            [
                'subject' => 'Lab Session Rescheduled',
                'message' => '<p>Hello everyone,</p><p>Due to maintenance in the physics lab, this week\'s lab session has been <strong>rescheduled</strong>:</p><p><strong>New Time:</strong> Friday at 2:00 PM<br><strong>Location:</strong> Physics Lab B</p><p>Please update your calendars accordingly.</p><p>Thank you for your understanding.</p>',
                'is_important' => false,
                'is_broadcast' => true,
            ],
            [
                'subject' => 'New Study Materials Available',
                'message' => '<p>Hi students,</p><p>I\'ve uploaded new study materials for our upcoming exam:</p><ul><li>Algorithm complexity study guide</li><li>Practice problems with solutions</li><li>Video tutorials on sorting algorithms</li></ul><p>These materials are available in the course portal. Please review them before our next class.</p><p>Good luck with your studies!</p>',
                'is_important' => false,
                'is_broadcast' => true,
                'attachments' => [
                    ['id' => 1, 'name' => 'algorithm-study-guide.pdf', 'url' => '/downloads/algorithm-study-guide.pdf'],
                    ['id' => 2, 'name' => 'practice-problems.pdf', 'url' => '/downloads/practice-problems.pdf']
                ]
            ],
            [
                'subject' => 'Excellent Work on Project',
                'message' => '<p>Dear [Student Name],</p><p>I wanted to personally congratulate you on your excellent work on the recent project. Your approach to solving the problem was innovative and well-executed.</p><p>Keep up the great work!</p><p>Best regards,<br>Dr. Johnson</p>',
                'is_important' => false,
                'is_broadcast' => false,
            ],
            [
                'subject' => 'Office Hours Update',
                'message' => '<p>Students,</p><p>Please note that my office hours for this week have been updated:</p><p><strong>Tuesday:</strong> 10:00 AM - 12:00 PM<br><strong>Thursday:</strong> 2:00 PM - 4:00 PM</p><p>Location remains the same: Office 301, Science Building.</p>',
                'is_important' => true,
                'is_broadcast' => true,
            ],
            [
                'subject' => 'Midterm Exam Information',
                'message' => '<p>Dear Class,</p><p>The midterm exam is scheduled for next Friday. Here are the important details:</p><ul><li><strong>Date:</strong> Friday, [Date]</li><li><strong>Time:</strong> 9:00 AM - 11:00 AM</li><li><strong>Location:</strong> Main Auditorium</li><li><strong>Material:</strong> Chapters 1-8</li></ul><p>Please bring:</p><ul><li>Student ID</li><li>Calculator (non-programmable)</li><li>Pencils and erasers</li></ul><p>Good luck!</p>',
                'is_important' => true,
                'is_broadcast' => true,
            ],
            [
                'subject' => 'Guest Lecture Announcement',
                'message' => '<p>Exciting news!</p><p>We have a special guest lecturer joining us next week:</p><p><strong>Dr. Maria Rodriguez</strong><br>Senior Data Scientist at TechCorp<br><strong>Topic:</strong> "Machine Learning in Real-World Applications"</p><p><strong>When:</strong> Wednesday at 2:00 PM<br><strong>Where:</strong> Lecture Hall C</p><p>This is an excellent opportunity to learn from industry experts. Attendance is highly encouraged!</p>',
                'is_important' => false,
                'is_broadcast' => true,
            ]
        ];

        foreach ($sampleMessages as $index => $messageData) {
            $teacher = $teachers->random();
            $course = $courses->isNotEmpty() ? $courses->random() : null;

            if ($messageData['is_broadcast']) {
                // Create broadcast message for all students in the course
                if ($course) {
                    // Get students enrolled in this course
                    $enrolledStudents = $course->enrollments()->with('user')->get()->pluck('user');
                    
                    if ($enrolledStudents->isNotEmpty()) {
                        foreach ($enrolledStudents as $student) {
                            Message::create([
                                'teacher_id' => $teacher->id,
                                'student_id' => $student->id,
                                'course_id' => $course->id,
                                'subject' => $messageData['subject'],
                                'message' => $messageData['message'],
                                'is_important' => $messageData['is_important'],
                                'is_broadcast' => $messageData['is_broadcast'],
                                'attachments' => $messageData['attachments'] ?? null,
                                'read_at' => $index % 3 === 0 ? now() : null, // Mark some as read
                                'created_at' => Carbon::now()->subDays(rand(1, 7)),
                            ]);
                        }
                    }
                } else {
                    // Broadcast to all students
                    foreach ($students as $student) {
                        Message::create([
                            'teacher_id' => $teacher->id,
                            'student_id' => $student->id,
                            'course_id' => null,
                            'subject' => $messageData['subject'],
                            'message' => $messageData['message'],
                            'is_important' => $messageData['is_important'],
                            'is_broadcast' => $messageData['is_broadcast'],
                            'attachments' => $messageData['attachments'] ?? null,
                            'read_at' => $index % 3 === 0 ? now() : null,
                            'created_at' => Carbon::now()->subDays(rand(1, 7)),
                        ]);
                    }
                }
            } else {
                // Create individual message to a random student
                $student = $students->random();
                Message::create([
                    'teacher_id' => $teacher->id,
                    'student_id' => $student->id,
                    'course_id' => $course ? $course->id : null,
                    'subject' => $messageData['subject'],
                    'message' => str_replace('[Student Name]', $student->name, $messageData['message']),
                    'is_important' => $messageData['is_important'],
                    'is_broadcast' => $messageData['is_broadcast'],
                    'attachments' => $messageData['attachments'] ?? null,
                    'read_at' => $index % 4 === 0 ? now() : null,
                    'created_at' => Carbon::now()->subDays(rand(1, 7)),
                ]);
            }
        }

        $totalMessages = Message::count();
        $this->command->info("✅ Created {$totalMessages} sample messages successfully!");
    }
} 