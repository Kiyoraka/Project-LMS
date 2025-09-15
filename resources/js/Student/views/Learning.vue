<template>
  <div class="learning-container">
    <!-- Toast Notifications -->
    <Toast
      :show="showToast"
      :message="toastMessage"
      :title="toastTitle"
      :type="toastType"
      @hide="hideToast"
    />

    <!-- Progress Requirement Modal -->
    <div v-if="showProgressModal" class="modal-overlay" @click="showProgressModal = false">
      <div class="progress-modal" @click.stop>
        <div class="modal-header">
          <div class="header-icon">
            <i class="fas fa-clock"></i>
          </div>
          <h3>Video Progress Required</h3>
          <button class="close-btn" @click="showProgressModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <p>You need to watch at least <strong>90%</strong> of the video before marking this lesson as complete.</p>
          <div class="progress-info">
            <div class="progress-bar-container">
              <div class="progress-bar-fill" :style="{ width: currentLessonProgress + '%' }"></div>
            </div>
            <div class="progress-text">
              Current progress: <strong>{{ currentLessonProgress }}%</strong>
            </div>
          </div>
          <p class="suggestion">Please continue watching the video and try again once you reach 90% completion.</p>
        </div>
        <div class="modal-footer">
          <button class="btn-close" @click="showProgressModal = false">
            <i class="fas fa-arrow-left"></i>
            Continue Watching
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading course...</span>
      </div>
      <p class="mt-3">Loading course content...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <div class="alert alert-danger">
        <h5><i class="bi bi-exclamation-triangle me-2"></i>Error Loading Course</h5>
        <p>{{ error }}</p>
        <button class="btn btn-outline-danger" @click="fetchCourseData">Try Again</button>
      </div>
    </div>

    <!-- Course Content -->
    <div v-else class="course-content">
      <!-- Course Header -->
      <div class="course-header">
        <div class="course-header-content">
          <div class="course-info">
            <router-link to="/student/courses" class="back-link">
              <i class="fas fa-arrow-left"></i>
              <span>Back to My Courses</span>
            </router-link>
            <h1 class="course-title">{{ course.title }}</h1>
            <div class="course-meta">
              <span class="badge badge-level">{{ course.level }}</span>
              <span class="course-duration">
                <i class="fas fa-clock"></i>
                {{ course.duration_hours || 0 }} hours
              </span>
            </div>
          </div>
          
          <div class="course-stats">
            <div class="stat-card">
              <div class="stat-value">{{ overallProgress }}%</div>
              <div class="stat-label">Overall Progress</div>
              <div class="progress-bar">
                <div class="progress-fill" :style="{ width: overallProgress + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Course Navigation Tabs -->
      <div class="course-tabs">
        <div class="tab-container">
          <button 
            class="tab-button"
            :class="{ active: activeTab === 'content' }"
            @click="activeTab = 'content'"
          >
            <i class="fas fa-play-circle"></i>
            Course Content
          </button>
          <button 
            class="tab-button"
            :class="{ active: activeTab === 'overview' }"
            @click="activeTab = 'overview'"
          >
            <i class="fas fa-info-circle"></i>
            Overview
          </button>
        </div>
      </div>

      <!-- Tab Content -->
      <div class="tab-content">
        <!-- Course Content Tab -->
        <div v-if="activeTab === 'content'" class="content-tab">
          <div class="learning-layout">
            <!-- Sidebar - Course Structure -->
            <div class="course-sidebar">
              <div class="sidebar-header">
                <h3>Course Content</h3>
                <div class="completion-badge">
                  {{ getCompletedLessonsTotal() }}/{{ getTotalLessons() }} Complete
                </div>
              </div>
              
              <div class="modules-list">
                <div v-for="(module, moduleIndex) in course.modules" :key="module.id" class="module-item">
                  <!-- Module Header -->
                  <div 
                    class="module-header"
                    @click="toggleModule(moduleIndex)"
                  >
                    <div class="module-info">
                      <i class="fas fa-folder"></i>
                      <span class="module-title">{{ module.title }}</span>
                    </div>
                    <div class="module-progress">
                      <span class="progress-text">{{ getCompletedLessonsCount(module) }}/{{ module.lessons.length }}</span>
                      <i 
                        class="fas"
                        :class="expandedModules[moduleIndex] ? 'fa-chevron-down' : 'fa-chevron-right'"
                      ></i>
                    </div>
                  </div>
                  
                  <!-- Module Lessons -->
                  <div v-if="expandedModules[moduleIndex]" class="lessons-list">
                    <div 
                      v-for="lesson in module.lessons" 
                      :key="lesson.id"
                      class="lesson-item"
                      :class="{
                        'active': currentLesson && currentLesson.id === lesson.id,
                        'completed': isLessonCompleted(lesson)
                      }"
                      @click="setCurrentLesson(lesson)"
                    >
                      <div class="lesson-info">
                        <i class="lesson-icon" :class="getLessonIcon(lesson)"></i>
                        <div class="lesson-details">
                          <div class="lesson-title">{{ lesson.title }}</div>
                          <div class="lesson-meta">
                            <span class="lesson-type">{{ getLessonTypeLabel(lesson) }}</span>
                            <span class="lesson-duration">{{ lesson.duration_minutes || 15 }} min</span>
                          </div>
                        </div>
                      </div>
                      <div class="lesson-status">
                        <i v-if="isLessonCompleted(lesson)" class="fas fa-check-circle completed-icon"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Main Content - Lesson Player -->
            <div class="lesson-content">
              <div v-if="currentLesson" class="lesson-player">
                <!-- Lesson Header -->
                <div class="lesson-header">
                  <div class="lesson-title-section">
                    <h2>{{ currentLesson.title }}</h2>
                    <div class="lesson-badges">
                      <span class="lesson-type-badge" :class="getLessonTypeBadgeClass(currentLesson)">
                        <i :class="getLessonTypeIcon(currentLesson)"></i>
                        {{ getLessonTypeLabel(currentLesson) }}
                      </span>
                      <span class="lesson-duration-badge">
                        <i class="fas fa-clock"></i>
                        {{ currentLesson.duration_minutes || 15 }} min
                      </span>
                    </div>
                  </div>
                  
                  <div class="lesson-actions">
                    <button 
                      v-if="!currentLessonCompleted" 
                      class="btn-mark-complete"
                      :class="{
                        'enabled': currentLesson.type !== 'video' || currentLessonProgress >= 90,
                        'disabled': currentLesson.type === 'video' && currentLessonProgress < 90
                      }"
                      @click="markLessonComplete"
                    >
                      <i class="fas fa-check-circle"></i>
                      <span>Mark Complete</span>
                      <div v-if="currentLesson.type === 'video'" class="progress-indicator">
                        {{ currentLessonProgress }}% watched
                      </div>
                    </button>
                    <div v-else class="completion-status">
                      <div class="status-content">
                        <i class="fas fa-check-circle"></i>
                        <span>Completed</span>
                      </div>
                      <div class="progress-indicator">
                        100% watched
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Lesson Content -->
                <div class="lesson-body">
                  <!-- Video Lesson -->
                  <div v-if="currentLesson.type === 'video'" class="video-lesson">
                    <secure-video-player
                      ref="videoPlayer"
                      :key="'video-player-' + currentLesson.id"
                      :video-url="currentLesson.video_url || getDemoVideoUrl()"
                      :lesson-id="currentLesson.id"
                      :duration="currentLesson.duration_minutes * 60 || 600"
                      @lesson-completed="onLessonCompleted"
                      @progress-update="onProgressUpdate"
                      @show-toast="showToastMessage"
                    />
                  </div>

                  <!-- Article Lesson -->
                  <div v-else-if="currentLesson.type === 'article'" class="article-lesson">
                    <div class="article-content" v-html="currentLesson.content || demoArticleContent"></div>
                  </div>

                  <!-- Quiz Lesson -->
                  <div v-else-if="currentLesson.type === 'quiz'" class="quiz-lesson">
                    <div v-if="!quizSubmitted" class="quiz-questions">
                      <div v-for="(question, qIndex) in currentLesson.questions || demoQuizQuestions" :key="qIndex" class="question-card">
                        <h4 class="question-title">{{ qIndex + 1 }}. {{ question.text }}</h4>
                        <div class="question-options">
                          <div 
                            v-for="(option, oIndex) in question.options" 
                            :key="oIndex"
                            class="option-item"
                          >
                            <input 
                              type="radio" 
                              :id="'q' + qIndex + 'o' + oIndex"
                              :name="'question' + qIndex"
                              v-model="quizAnswers[qIndex]"
                              :value="oIndex"
                            >
                            <label :for="'q' + qIndex + 'o' + oIndex">{{ option }}</label>
                          </div>
                        </div>
                      </div>
                      
                      <div class="quiz-actions">
                        <button class="btn btn-primary" @click="submitQuiz">
                          <i class="fas fa-paper-plane"></i>
                          Submit Quiz
                        </button>
                      </div>
                    </div>

                    <div v-else class="quiz-results">
                      <div class="results-header">
                        <h3>Quiz Results</h3>
                        <div class="score-badge">
                          {{ quizScore }}/{{ (currentLesson.questions || demoQuizQuestions).length }}
                        </div>
                      </div>
                      <!-- Quiz results content -->
                    </div>
                  </div>

                  <!-- Assignment Lesson -->
                  <div v-else-if="currentLesson.type === 'assignment'" class="assignment-lesson">
                    <div class="assignment-content" v-html="currentLesson.content || demoAssignmentContent"></div>
                    <!-- Assignment submission form -->
                  </div>
                </div>

                <!-- Lesson Navigation -->
                <div class="lesson-navigation">
                  <button 
                    class="nav-button prev"
                    :disabled="!hasPreviousLesson" 
                    @click="navigateToPreviousLesson"
                  >
                    <i class="fas fa-chevron-left"></i>
                    Previous Lesson
                  </button>
                  
                  <button
                    class="nav-button next"
                    :disabled="false"
                    @click="hasNextLesson ? navigateToNextLesson() : markCourseComplete()"
                  >
                    <span v-if="hasNextLesson">
                      Next Lesson
                      <i class="fas fa-chevron-right"></i>
                    </span>
                    <span v-else>
                      <i class="fas fa-check-circle"></i>
                      Complete Course
                    </span>
                  </button>
                </div>
              </div>

              <!-- No Lesson Selected -->
              <div v-else class="no-lesson-selected">
                <div class="empty-state">
                  <i class="fas fa-play-circle"></i>
                  <h3>Select a Lesson to Begin</h3>
                  <p>Choose a lesson from the course content sidebar to start learning.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Overview Tab -->
        <div v-if="activeTab === 'overview'" class="overview-tab">
          <div class="course-overview">
            <div class="overview-section">
              <h3>Course Description</h3>
              <p>{{ course.short_description || 'No description available.' }}</p>
            </div>
            
            <div class="overview-section">
              <h3>Your Progress</h3>
              <div class="progress-overview">
                <div class="progress-stat">
                  <span class="stat-value">{{ overallProgress }}%</span>
                  <span class="stat-label">Overall Progress</span>
                </div>
                <div class="progress-stat">
                  <span class="stat-value">{{ getCompletedLessonsTotal() }}</span>
                  <span class="stat-label">Lessons Completed</span>
                </div>
                <div class="progress-stat">
                  <span class="stat-value">{{ getTotalLessons() }}</span>
                  <span class="stat-label">Total Lessons</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SecureVideoPlayer from '../components/SecureVideoPlayer.vue';
import Toast from '../components/Toast.vue';
import axios from 'axios';

export default {
  components: {
    SecureVideoPlayer,
    Toast
  },
  name: 'StudentLearning',
  data() {
    return {
      // Course data
      courseId: null,
      course: {},
      currentLesson: null,
      loading: true,
      error: null,
      
      // Navigation and UI
      activeTab: 'content',
      expandedModules: {},
      
      // Toast notifications
      showToast: false,
      toastMessage: '',
      toastTitle: '',
      toastType: 'info',
      
      // Progress tracking
      currentLessonProgress: 0,
      currentLessonCompleted: false,
      
      // Custom modal for progress requirement
      showProgressModal: false,
      
      // Quiz functionality
      quizAnswers: {},
      quizSubmitted: false,
      quizScore: 0,
      
      // Assignment functionality
      assignmentAnswer: '',
      
      // Demo content for presentation
      demoArticleContent: `
        <h2>Welcome to This Lesson</h2>
        <p>This is a comprehensive article lesson that covers the fundamental concepts you need to understand.</p>
        
        <h3>Key Learning Objectives</h3>
        <ul>
          <li>Understand the core principles</li>
          <li>Apply practical techniques</li>
          <li>Develop problem-solving skills</li>
        </ul>
        
        <h3>Deep Dive Into the Topic</h3>
        <p>Let's explore this topic in detail. The concepts we'll cover are essential for your understanding of the broader subject matter.</p>
        
        <p>Remember to take notes as you read through this content, as it will be helpful for your future reference.</p>
        
        <h3>Summary</h3>
        <p>By the end of this lesson, you should have a solid understanding of the key concepts and be ready to move on to more advanced topics.</p>
      `,
      
      demoQuizQuestions: [
        {
          text: "What is the most important aspect of this lesson?",
          options: [
            "Understanding the concepts",
            "Memorizing the facts",
            "Completing quickly",
            "Taking notes"
          ],
          correctAnswer: 0,
          explanation: "Understanding concepts is more important than just memorizing facts."
        },
        {
          text: "How should you approach learning new material?",
          options: [
            "Rush through it",
            "Take your time and understand",
            "Skip difficult parts",
            "Only read summaries"
          ],
          correctAnswer: 1,
          explanation: "Taking time to understand material leads to better retention and application."
        }
      ],
      
      demoAssignmentContent: `
        <h3>Assignment Instructions</h3>
        <p>Complete the following tasks to demonstrate your understanding of the lesson material:</p>
        
        <ol>
          <li>Summarize the key concepts covered in this lesson</li>
          <li>Provide an example of how you would apply these concepts</li>
          <li>Identify any areas where you need additional clarification</li>
        </ol>
        
        <p><strong>Requirements:</strong></p>
        <ul>
          <li>Minimum 300 words</li>
          <li>Use specific examples</li>
          <li>Submit by the due date</li>
        </ul>
      `,
    };
  },
  computed: {
    hasPreviousLesson() {
      if (!this.currentLesson || !this.course) return false;
      
      let foundCurrent = false;
      
      // Traverse modules and lessons in reverse to find the previous lesson
      for (let m = this.course.modules.length - 1; m >= 0; m--) {
        const module = this.course.modules[m];
        for (let l = module.lessons.length - 1; l >= 0; l--) {
          if (foundCurrent) {
            return true;
          }
          if (module.lessons[l].id === this.currentLesson.id) {
            foundCurrent = true;
          }
        }
      }
      
      return false;
    },
    hasNextLesson() {
      if (!this.currentLesson || !this.course) return false;
      
      let foundCurrent = false;
      
      // Traverse modules and lessons to find the next lesson
      for (const module of this.course.modules) {
        for (const lesson of module.lessons) {
          if (foundCurrent) {
            return true;
          }
          if (lesson.id === this.currentLesson.id) {
            foundCurrent = true;
          }
        }
      }
      
      return false;
    },
    overallProgress() {
      if (!this.course || !this.course.modules || this.course.modules.length === 0) return 0;
      
      let totalLessons = 0;
      let completedLessons = 0;
      
      this.course.modules.forEach(module => {
        if (module.lessons && module.lessons.length > 0) {
          totalLessons += module.lessons.length;
          completedLessons += module.lessons.filter(lesson => lesson.completed || lesson.progress_percentage === 100).length;
        }
      });
      
      return totalLessons > 0 ? Math.round((completedLessons / totalLessons) * 100) : 0;
    }
  },
  mounted() {
    // Get course ID from route params
    this.courseId = this.$route.params.id;
    
    if (this.courseId) {
      // Fetch real course data from API
      this.fetchCourseData();
    } else {
      this.error = 'No course ID provided in the URL';
    }
  },
  methods: {
    async fetchCourseData() {
      if (!this.courseId) {
        this.error = 'No course ID provided';
        return;
      }

      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get(`/api/student/courses/${this.courseId}/learn`);
        
        if (response.data && response.data.id) {
          this.course = response.data;
          
          // Initialize the first module as expanded
          if (this.course.modules && this.course.modules.length > 0) {
            this.expandedModules = { 0: true };
            
            // Set the first lesson as current if none is selected
            if (!this.currentLesson && this.course.modules[0].lessons && this.course.modules[0].lessons.length > 0) {
              this.currentLesson = this.course.modules[0].lessons[0];
            }
          }
        } else {
          this.error = 'Invalid course data received';
        }
      } catch (error) {
        if (error.response) {
          if (error.response.status === 403) {
            this.error = 'You are not enrolled in this course.';
          } else if (error.response.status === 404) {
            this.error = 'Course not found.';
          } else {
            this.error = error.response.data?.message || 'Failed to load course content. Please try again.';
          }
        } else {
          this.error = 'Network error. Please check your connection and try again.';
        }
      } finally {
        this.loading = false;
      }
    },
    
    // Video Analytics Event Handler
    onLessonCompleted(lessonId) {
      // Find and update the lesson in our course data
      let lessonFound = false;
      for (const module of this.course.modules) {
        for (const lesson of module.lessons) {
          if (lesson.id === lessonId) {
            lesson.completed = true;
            lesson.progress_percentage = 100;
            lessonFound = true;
            break;
          }
        }
      }
      
      // Update current lesson if it matches
      if (this.currentLesson && this.currentLesson.id === lessonId) {
        this.currentLesson.completed = true;
        this.currentLesson.progress_percentage = 100;
        this.currentLessonCompleted = true;
      }
      
      // Auto-navigate to next lesson if available
      if (this.hasNextLesson) {
        setTimeout(() => {
          this.navigateToNextLesson();
        }, 1500); // Give time to see completion state change
      }
    },
    
    // Progress Update Handler
    onProgressUpdate(progressData) {
      if (progressData.lessonId === this.currentLesson?.id) {
        this.currentLessonProgress = progressData.progressPercentage;
        this.currentLessonCompleted = progressData.isCompleted || false;
      }
    },
    
    toggleModule(moduleIndex) {
      this.expandedModules = {
        ...this.expandedModules,
        [moduleIndex]: !this.expandedModules[moduleIndex]
      };
    },
    setCurrentLesson(lesson) {
      this.currentLesson = lesson;
      this.quizSubmitted = false;
      this.quizAnswers = {};
      this.assignmentAnswer = '';
      
      // Reset progress tracking for new lesson
      this.currentLessonProgress = 0;
      this.currentLessonCompleted = lesson.completed || false;
    },
    getCompletedLessonsCount(module) {
      return module.lessons.filter(lesson => lesson.completed).length;
    },
    getCompletedLessonsTotal() {
      if (!this.course || !this.course.modules) return 0;
      
      let total = 0;
      this.course.modules.forEach(module => {
        if (module.lessons) {
          total += module.lessons.filter(lesson => lesson.completed).length;
        }
      });
      return total;
    },
    getTotalLessons() {
      if (!this.course || !this.course.modules) return 0;
      
      let total = 0;
      this.course.modules.forEach(module => {
        if (module.lessons) {
          total += module.lessons.length;
        }
      });
      return total;
    },
    isLessonCompleted(lesson) {
      return lesson.completed;
    },
    getLessonIcon(lesson) {
      if (lesson.completed) {
        return 'bi-check-circle-fill';
      }
      
      switch (lesson.type) {
        case 'video':
          return 'bi-play-circle';
        case 'article':
          return 'bi-file-text';
        case 'quiz':
          return 'bi-question-circle';
        case 'assignment':
          return 'bi-clipboard-check';
        default:
          return 'bi-file-earmark';
      }
    },
    getLessonTypeBadgeClass(lesson) {
      if (!lesson) return '';
      
      switch (lesson.type) {
        case 'video':
          return 'bg-primary';
        case 'article':
          return 'bg-info';
        case 'quiz':
          return 'bg-warning text-dark';
        case 'assignment':
          return 'bg-danger';
        default:
          return 'bg-secondary';
      }
    },
    getLessonTypeIcon(lesson) {
      if (!lesson) return '';
      
      switch (lesson.type) {
        case 'video':
          return 'bi bi-play-circle me-1';
        case 'article':
          return 'bi bi-file-text me-1';
        case 'quiz':
          return 'bi bi-question-circle me-1';
        case 'assignment':
          return 'bi bi-clipboard-check me-1';
        default:
          return 'bi bi-file-earmark me-1';
      }
    },
    getLessonTypeLabel(lesson) {
      if (!lesson) return '';
      
      switch (lesson.type) {
        case 'video':
          return 'Video Lesson';
        case 'article':
          return 'Reading Material';
        case 'quiz':
          return 'Quiz';
        case 'assignment':
          return 'Assignment';
        default:
          return 'Lesson';
      }
    },
    async markLessonComplete() {
      if (!this.currentLesson) return;
      
      // Check if lesson is already completed
      if (this.currentLessonCompleted) {
        this.showToastMessage('This lesson is already completed!', 'Already Complete', 'info');
        return;
      }
      
      // For video lessons, check 90% progress requirement
      if (this.currentLesson.type === 'video' && this.currentLessonProgress < 90) {
        this.showProgressModal = true;
        return;
      }
      
      try {
        // For video lessons, delegate to the video player component
        if (this.currentLesson.type === 'video') {
          const videoPlayer = this.$refs.videoPlayer;
          if (videoPlayer && typeof videoPlayer.markLessonCompleted === 'function') {
            await videoPlayer.markLessonCompleted();
            return;
          }
        }
        
        // For non-video lessons, handle directly
        const timeSpent = this.currentLesson.type === 'video' ? 300 : 1;
        
        await axios.post(`/api/student/lessons/${this.currentLesson.id}/complete`, {
          time_spent_seconds: timeSpent,
          completed: true
        });
        
        this.currentLesson.completed = true;
        this.currentLessonCompleted = true;
        
        // Auto-navigate to next lesson if available
        if (this.hasNextLesson) {
          setTimeout(() => {
            this.navigateToNextLesson();
          }, 1000); // Quick transition for manual completion
        }
        
      } catch (error) {
        if (error.response?.status === 403) {
          this.showToastMessage('You are not enrolled in this course.', 'Access Denied', 'error');
        } else {
          this.showToastMessage('Failed to mark lesson as complete. Please try again.', 'Error', 'error');
        }
      }
    },
    navigateToPreviousLesson() {
      if (!this.hasPreviousLesson) return;
      
      let prevLesson = null;
      let foundCurrent = false;
      
      // Find the previous lesson by traversing modules and lessons in reverse
      outerLoop: for (let m = this.course.modules.length - 1; m >= 0; m--) {
        const module = this.course.modules[m];
        for (let l = module.lessons.length - 1; l >= 0; l--) {
          if (foundCurrent) {
            prevLesson = module.lessons[l];
            break outerLoop;
          }
          if (module.lessons[l].id === this.currentLesson.id) {
            foundCurrent = true;
          }
        }
      }
      
      if (prevLesson) {
        this.setCurrentLesson(prevLesson);
      }
    },
    navigateToNextLesson() {
      if (!this.hasNextLesson) return;

      let nextLesson = null;
      let foundCurrent = false;

      // Find the next lesson by traversing modules and lessons
      outerLoop: for (const module of this.course.modules) {
        for (const lesson of module.lessons) {
          if (foundCurrent) {
            nextLesson = lesson;
            break outerLoop;
          }
          if (lesson.id === this.currentLesson.id) {
            foundCurrent = true;
          }
        }
      }

      if (nextLesson) {
        this.setCurrentLesson(nextLesson);
      }
    },

    markCourseComplete() {
      // Show completion message
      this.showToastMessage('Congratulations! You have completed all lessons in this course.', 'Course Completed', 'success');

      // Redirect to courses page after a delay
      setTimeout(() => {
        this.$router.push('/student/courses');
      }, 2000);
    },
    submitQuiz() {
      if (!this.currentLesson || this.currentLesson.type !== 'quiz') return;
      
      const questions = this.currentLesson.questions || this.demoQuizQuestions;
      let score = 0;
      
      // Calculate score
      questions.forEach((question, index) => {
        if (this.quizAnswers[index] === question.correctAnswer) {
          score++;
        }
      });
      
      this.quizScore = score;
      this.quizSubmitted = true;
      
      // Mark lesson as complete if score is passing (e.g., > 70%)
      if (score / questions.length >= 0.7) {
        this.markLessonComplete();
      }
    },
    submitAssignment() {
      if (!this.currentLesson || this.currentLesson.type !== 'assignment') return;
      
      // Show submission confirmation
      this.showToastMessage('Assignment submitted successfully! Your instructor will review it soon.', 'Assignment Submitted', 'success');
      
      // Mark as completed
      this.markLessonComplete();
    },
    
    /**
     * Provides a demo video URL for testing purposes
     * @returns {string} - Demo video URL 
     */
    getDemoVideoUrl() {
      // For testing purposes, return a direct YouTube URL without encryption
      // This bypasses any potential encryption/decryption issues
      return 'https://www.youtube.com/embed/dQw4w9WgXcQ';
    },
    
    /**
     * Video Security System - URL Encryption
     * Implements JavaScript-based link encryption for video security
     * @param {string} url - The original video URL to encrypt
     * @returns {string} - The encrypted URL
     */
    encryptDemoUrl(url) {
      // In a production environment, this would use a proper encryption algorithm
      // with server-generated keys for security. This is a simplified demo version.
      
      // Simple base64 encoding for demonstration
      const encodedUrl = btoa(url);
      
      // Add prefix to indicate it's encrypted
      return `enc:${encodedUrl}`;
      
      // The SecureVideoPlayer component will handle decryption and domain restriction
    },
    
    // Toast notification methods
    showToastMessage(message, title = '', type = 'info') {
      this.toastMessage = message;
      this.toastTitle = title;
      this.toastType = type;
      this.showToast = true;
    },
    
    hideToast() {
      this.showToast = false;
    }
  }
};
</script>

<style scoped>
.learning-container {
  min-height: 100vh;
  background-color: #f8f9fa;
  color: #212529;
  padding: 0 20px;
}

/* Loading and Error States */
.loading-state, .error-state {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  min-height: 60vh;
  text-align: center;
}

/* Course Header */
.course-header {
  background: linear-gradient(135deg, #001d3d 0%, #0077b6 100%);
  color: white;
  margin: -24px -24px 0 -24px;
  padding: 30px 24px;
}

.course-header-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
}

.back-link {
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  font-size: 14px;
  margin-bottom: 10px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: color 0.2s;
}

.back-link:hover {
  color: white;
}

.course-title {
  font-size: 2rem;
  font-weight: 700;
  margin: 8px 0;
}

.course-meta {
  display: flex;
  align-items: center;
  gap: 15px;
}

.badge-level {
  background-color: rgba(255, 255, 255, 0.2);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.course-duration {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 14px;
  opacity: 0.9;
}

.course-stats {
  flex-shrink: 0;
}

.stat-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  min-width: 180px;
}

.stat-value {
  display: block;
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1;
}

.stat-label {
  display: block;
  font-size: 14px;
  opacity: 0.9;
  margin-top: 5px;
}

.progress-bar {
  height: 6px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 3px;
  margin-top: 10px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: white;
  border-radius: 3px;
  transition: width 0.3s ease;
}

/* Tabs */
.course-tabs {
  background: white;
  border-bottom: 1px solid #e9ecef;
  margin: 0 -24px;
  padding: 0 24px;
}

.tab-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  gap: 2px;
}

.tab-button {
  background: none;
  border: none;
  padding: 15px 24px;
  font-weight: 500;
  color: #6c757d;
  border-bottom: 3px solid transparent;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.tab-button:hover {
  color: #495057;
  background: #f8f9fa;
}

.tab-button.active {
  color: #667eea;
  border-bottom-color: #667eea;
  background: #f8f9fa;
}

/* Tab Content */
.tab-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px 0;
}

/* Learning Layout */
.learning-layout {
  display: grid;
  grid-template-columns: 400px 1fr;
  gap: 24px;
  min-height: 70vh;
}

/* Course Sidebar */
.course-sidebar {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  height: fit-content;
  max-height: calc(100vh - 250px);
  overflow-y: auto;
}

.sidebar-header {
  padding: 20px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.sidebar-header h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
  color: #495057;
}

.completion-badge {
  background: #e7f3ff;
  color: #0066cc;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.modules-list {
  padding: 0;
}

.module-item {
  border-bottom: 1px solid #f1f3f4;
}

.module-header {
  padding: 16px 20px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: background-color 0.2s;
}

.module-header:hover {
  background-color: #f8f9fa;
}

.module-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.module-info i {
  color: #6c757d;
  width: 16px;
}

.module-title {
  font-weight: 600;
  color: #495057;
}

.module-progress {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: #6c757d;
}

.lessons-list {
  background: #f8f9fa;
}

.lesson-item {
  padding: 12px 20px 12px 50px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  transition: all 0.2s;
  border-left: 3px solid transparent;
}

.lesson-item:hover {
  background-color: #e9ecef;
}

.lesson-item.active {
  background-color: #e7f3ff;
  border-left-color: #667eea;
}

.lesson-item.completed {
  background-color: #f0f9ff;
}

.lesson-info {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.lesson-icon {
  width: 16px;
  color: #6c757d;
}

.lesson-details {
  flex: 1;
}

.lesson-title {
  font-weight: 500;
  color: #495057;
  font-size: 14px;
  line-height: 1.3;
}

.lesson-meta {
  display: flex;
  gap: 10px;
  margin-top: 2px;
}

.lesson-type, .lesson-duration {
  font-size: 12px;
  color: #6c757d;
}

.lesson-status {
  flex-shrink: 0;
}

.completed-icon {
  color: #28a745;
  font-size: 16px;
}

.progress-indicator {
  background: #e7f3ff;
  color: #0066cc;
  padding: 2px 6px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 500;
}

/* Lesson Content */
.lesson-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.lesson-player {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.lesson-header {
  padding: 24px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
}

.lesson-title-section h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #212529;
  margin: 0 0 12px 0;
}

.lesson-badges {
  display: flex;
  gap: 8px;
}

.lesson-type-badge, .lesson-duration-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 4px;
}

.lesson-type-badge {
  background: #e7f3ff;
  color: #0066cc;
}

.lesson-duration-badge {
  background: #f0f0f0;
  color: #666;
}

.lesson-actions {
  display: flex;
  align-items: center;
  gap: 15px;
}

.btn-mark-complete {
  position: relative;
  padding: 12px 24px;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  min-width: 140px;
}

.btn-mark-complete.enabled {
  background: linear-gradient(135deg, #0077b6 0%, #003d5c 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-mark-complete.enabled:hover {
  background: linear-gradient(135deg, #001d3d 0%, #0077b6 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
}

.btn-mark-complete.disabled {
  background: linear-gradient(135deg, #4a90a4 0%, #001d3d 100%);
  color: #dee2e6;
  cursor: not-allowed;
  opacity: 0.7;
}

.btn-mark-complete i {
  font-size: 16px;
}

.progress-indicator {
  font-size: 11px;
  opacity: 0.9;
  font-weight: 500;
}

.completion-status {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  color: #28a745;
  font-weight: 600;
  padding: 12px 24px;
  background: rgba(40, 167, 69, 0.1);
  border-radius: 12px;
  border: 2px solid rgba(40, 167, 69, 0.2);
  min-width: 140px;
}

.status-content {
  display: flex;
  align-items: center;
  gap: 6px;
}

.status-content i {
  font-size: 16px;
}

.lesson-body {
  flex: 1;
  padding: 20px;
}

.video-lesson {
  margin-bottom: 0;
}

.article-lesson {
  max-width: none;
}

.article-content {
  line-height: 1.7;
  font-size: 16px;
}

.article-content h1, .article-content h2, .article-content h3 {
  color: #495057;
  margin-top: 1.5em;
  margin-bottom: 0.5em;
}

.article-content p {
  margin-bottom: 1em;
}

.quiz-lesson {
  max-width: 800px;
}

.question-card {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  border: 1px solid #e9ecef;
}

.question-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #495057;
  margin-bottom: 15px;
}

.question-options {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.option-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: white;
  border-radius: 6px;
  border: 1px solid #e9ecef;
  transition: all 0.2s;
}

.option-item:hover {
  border-color: #667eea;
  background: #f8f9fa;
}

.option-item input[type="radio"] {
  margin: 0;
}

.quiz-actions {
  margin-top: 20px;
  text-align: right;
}

.quiz-results .results-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.score-badge {
  background: #28a745;
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: 600;
}

.assignment-lesson {
  max-width: 800px;
}

.assignment-content {
  line-height: 1.7;
  font-size: 16px;
}

/* Lesson Navigation */
.lesson-navigation {
  padding: 20px 24px;
  border-top: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.nav-button {
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.nav-button:hover:not(:disabled) {
  background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.nav-button:disabled {
  background: #e9ecef;
  color: #6c757d;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.nav-button.prev {
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
  color: white;
}

.nav-button.prev:hover:not(:disabled) {
  background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
}

/* No Lesson Selected */
.no-lesson-selected {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 400px;
}

.empty-state {
  text-align: center;
  color: #6c757d;
}

.empty-state i {
  font-size: 4rem;
  margin-bottom: 20px;
  opacity: 0.5;
}

.empty-state h3 {
  font-weight: 600;
  margin-bottom: 10px;
}

/* Overview Tab */
.course-overview {
  max-width: 800px;
}

.overview-section {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.overview-section h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #495057;
  margin-bottom: 15px;
}

.progress-overview {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 20px;
}

.progress-stat {
  text-align: center;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
}

.progress-stat .stat-value {
  display: block;
  font-size: 2rem;
  font-weight: 700;
  color: #667eea;
}

.progress-stat .stat-label {
  display: block;
  font-size: 14px;
  color: #6c757d;
  margin-top: 5px;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .learning-container {
    padding: 0 16px;
  }
}

@media (max-width: 1024px) {
  .learning-layout {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .course-sidebar {
    max-height: 300px;
  }
  
  .course-header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .course-stats {
    width: 100%;
  }
  
  .stat-card {
    min-width: auto;
  }
}

@media (max-width: 768px) {
  .learning-container {
    padding: 0 15px;
  }
  
  .course-header {
    margin: -16px -16px 0 -16px;
    padding: 20px 16px;
  }
  
  .course-tabs {
    margin: 0 -16px;
    padding: 0 16px;
  }
  
  .tab-content {
    padding: 16px 0;
  }
  
  .lesson-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .lesson-badges {
    flex-wrap: wrap;
  }
  
  .lesson-navigation {
    flex-direction: column;
    gap: 8px;
  }
  
  .nav-button {
    width: 100%;
    justify-content: center;
  }
  
  .progress-overview {
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 10px;
  }
  
  .sidebar-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .course-title {
    font-size: 1.5rem;
  }
}

/* Progress Requirement Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  animation: fadeIn 0.3s ease;
}

.progress-modal {
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  max-width: 480px;
  width: 90%;
  overflow: hidden;
  animation: slideUp 0.3s ease;
}

.progress-modal .modal-header {
  background: linear-gradient(135deg, #0099cc 0%, #004d66 100%);
  color: white;
  padding: 24px;
  position: relative;
  display: flex;
  align-items: center;
  gap: 16px;
}

.progress-modal .header-icon {
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.progress-modal .modal-header h3 {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 700;
  flex: 1;
}

.progress-modal .close-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.progress-modal .close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.progress-modal .modal-body {
  padding: 24px;
}

.progress-modal .modal-body p {
  margin: 0 0 16px 0;
  color: #495057;
  line-height: 1.6;
}

.progress-info {
  background: #f8f9fa;
  padding: 16px;
  border-radius: 8px;
  margin: 16px 0;
}

.progress-bar-container {
  width: 100%;
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 8px;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #001d3d 0%, #0077b6 100%);
  border-radius: 4px;
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 14px;
  font-weight: 600;
  color: #495057;
  text-align: center;
}

.suggestion {
  color: #6c757d;
  font-style: italic;
}

.progress-modal .modal-footer {
  padding: 20px 24px;
  background: #f8f9fa;
  border-top: 1px solid #e9ecef;
  display: flex;
  justify-content: center;
}

.btn-close {
  background: linear-gradient(135deg, #4a90a4 0%, #001d3d 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}

.btn-close:hover {
  background: linear-gradient(135deg, #001d3d 0%, #0077b6 100%);
  transform: translateY(-1px);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>