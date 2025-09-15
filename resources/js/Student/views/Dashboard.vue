<template>
  <div class="dashboard-container">
    <!-- Stats Cards -->
    <div class="stats-grid">
      <!-- Overall Progress Card -->
      <div class="stat-card bg-primary">
        <div class="stat-icon">
          <i class="bi bi-graph-up"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ overallProgress }}%</h3>
          <p class="stat-label">Overall Progress</p>
        </div>
      </div>
      
      <!-- Completed Courses Card -->
      <div class="stat-card bg-success">
        <div class="stat-icon">
          <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ completedCourses }}</h3>
          <p class="stat-label">Completed Courses</p>
          <small class="stat-info">{{ inProgressCourses }} courses in progress</small>
        </div>
      </div>
      
      <!-- Learning Streak Card -->
      <div class="stat-card bg-warning">
        <div class="stat-icon">
          <i class="bi bi-lightning"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ streak }} days</h3>
          <p class="stat-label">Learning Streak</p>
          <small class="stat-info">Keep your momentum going!</small>
        </div>
      </div>
      
      <!-- Achievements Card -->
      <div class="stat-card bg-info">
        <div class="stat-icon">
          <i class="bi bi-trophy"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ achievedCount }}</h3>
          <p class="stat-label">Achievements</p>
          <small class="stat-info">You're doing great!</small>
        </div>
      </div>
    </div>

    <div class="dashboard-content">
      <div class="main-content">
        <!-- My Courses -->
        <div class="card courses-card">
          <div class="card-header">
            <h5>My Courses</h5>
            <router-link to="/student/courses" class="btn btn-outline-primary btn-sm">
              View All
            </router-link>
          </div>
          <div class="card-body">
            <div v-if="enrollments.length > 0">
              <div class="course-grid">
                <div 
                  v-for="enrollment in enrolledCoursesPreview" 
                  :key="enrollment.id"
                  class="course-card"
                >
                  <div class="course-thumbnail" :style="{ backgroundImage: getCourseImageUrl(enrollment.course) }">
                    <i v-if="!enrollment.course.thumbnail" class="bi bi-book"></i>
                  </div>
                  <div class="course-content">
                    <h6 class="course-title">{{ enrollment.course.title }}</h6>
                    <div class="course-meta">
                      <span class="badge bg-primary">{{ enrollment.course.level }}</span>
                      <small class="text-muted">
                        {{ enrollment.status === 'completed' ? 'Completed' : `${enrollment.progress_percentage}% Complete` }}
                      </small>
                    </div>
                    <div class="progress-wrapper">
                      <div class="progress-bar">
                        <div class="progress-fill" :class="enrollment.status === 'completed' ? 'bg-success' : 'bg-primary'" :style="{ width: enrollment.progress_percentage + '%' }"></div>
                      </div>
                      <span class="progress-text">{{ enrollment.progress_percentage }}%</span>
                    </div>
                    <div class="course-actions">
                      <router-link 
                        :to="`/student/learning/${enrollment.course.id}`" 
                        class="btn btn-primary btn-sm flex-fill"
                      >
                        {{ enrollment.status === 'completed' ? 'Review Course' : 'Continue Learning' }}
                      </router-link>
                      <button 
                        v-if="enrollment.status === 'completed'"
                        @click="viewCertificate(enrollment.id)"
                        class="btn btn-outline-warning btn-sm ms-2"
                        title="View Certificate"
                      >
                        <i class="bi bi-award"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="empty-data">
              <i class="bi bi-book display-1 text-muted"></i>
              <p class="mt-3">You haven't enrolled in any courses yet.</p>
              <router-link to="/student/browse" class="btn btn-primary">
                Browse Courses
              </router-link>
            </div>
          </div>
          <div class="card-footer" v-if="enrollments.length > 0">
            <router-link to="/student/courses" class="btn btn-outline-primary btn-sm">
              View All Courses
            </router-link>
          </div>
        </div>
      </div>

      <div class="side-content">
        <!-- Recent Activity -->
        <div class="card activity-card">
          <div class="card-header">
            <h5>Recent Activity</h5>
          </div>
          <div class="card-body">
            <div v-if="recentActivity.length > 0">
              <div class="activity-list">
                <div v-for="activity in recentActivityPreview" :key="activity.id" class="activity-item">
                  <div class="activity-icon" :class="getActivityIconClass(activity)">
                    <i :class="getActivityIcon(activity)"></i>
                  </div>
                  <div class="activity-content">
                    <h6 class="activity-title">{{ getActivityTitle(activity) }}</h6>
                    <div class="activity-meta">
                      <small class="text-muted">
                        {{ activity.lesson.module.course.title }} - {{ activity.lesson.title }}
                      </small>
                      <br>
                      <small class="text-muted">
                        {{ formatDate(activity.updated_at) }}
                      </small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="empty-data">
              <i class="bi bi-activity display-1 text-muted"></i>
              <p class="mt-3">No recent activity to show.</p>
            </div>
          </div>
        </div>



        <!-- Achievements -->
        <div class="card achievements-card">
          <div class="card-header">
            <h5>Recent Achievements</h5>
            <button @click="openAchievementsModal" class="btn btn-outline-primary btn-sm">
              View All
            </button>
          </div>
          <div class="card-body">
            <div v-if="achievedAchievements.length > 0">
              <div class="achievement-list">
                <div v-for="achievement in achievedAchievements.slice(0, 3)" :key="achievement.title" class="achievement-item">
                  <div class="achievement-icon bg-warning">
                    <i class="bi bi-trophy-fill"></i>
                  </div>
                  <div class="achievement-content">
                    <h6 class="achievement-title">{{ achievement.title }}</h6>
                    <small class="text-muted">{{ achievement.description }}</small>
                      </div>
                    </div>
                  </div>
                </div>
            <div v-else class="empty-data">
              <i class="bi bi-award display-1 text-muted"></i>
              <p class="mt-3">No achievements yet.</p>
              </div>
            </div>
            </div>
          </div>
        </div>

    <!-- Achievements Modal -->
    <div v-if="showAchievementsModal" class="modal-overlay" @click="closeAchievementsModal">
      <div class="modal-content achievements-modal" @click.stop>
        <div class="achievements-header">
          <div class="achievements-icon">
            <i class="bi bi-trophy-fill"></i>
          </div>
          <h3>All Achievements</h3>
          <button @click="closeAchievementsModal" class="close-btn">
            <i class="bi bi-x-lg"></i>
          </button>
                  </div>
        
        <div class="achievements-body">
          <div v-if="achievements && achievements.length > 0" class="achievements-list">
            <div v-for="achievement in achievements" :key="achievement.id || achievement.title" class="achievement-item" :class="{ 'achievement-locked': !achievement.achieved }">
              <div class="achievement-icon" :class="achievement.achieved ? 'bg-warning' : 'bg-secondary'">
                <i class="bi bi-trophy-fill"></i>
                  </div>
              <div class="achievement-info">
                <h6 class="achievement-name" :class="{ 'text-muted': !achievement.achieved }">{{ achievement.title }}</h6>
                <p class="achievement-desc" :class="{ 'text-muted': !achievement.achieved }">{{ achievement.description }}</p>
                <small v-if="achievement.achieved && formatDate(achievement.earned_at)" class="achievement-date text-success">
                  <i class="bi bi-check-circle-fill me-1"></i>Earned on {{ formatDate(achievement.earned_at) }}
                </small>
                <small v-if="!achievement.achieved" class="achievement-how-to text-muted">
                  <i class="bi bi-info-circle me-1"></i>{{ achievement.howToAchieve }}
                </small>
                </div>
              <div class="achievement-status">
                <i v-if="achievement.achieved" class="bi bi-check-circle-fill text-success"></i>
                <i v-else class="bi bi-lock-fill text-muted"></i>
              </div>
            </div>
            </div>
          <div v-else class="empty-achievements">
            <i class="bi bi-trophy display-1 text-muted"></i>
            <h5>Achievements System</h5>
            <p class="text-muted">Achievement system is being set up. Check back soon!</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'StudentDashboard',
  data() {
    return {
      // Real data from API
      overallProgress: 0,
      completedCourses: 0,
      inProgressCourses: 0,
      streak: 0,
      achievements: [],
      enrollments: [],
      recentActivity: [],
      loading: true,
      error: null,
      showAchievementsModal: false
    };
  },
  computed: {
    enrolledCoursesPreview() {
      // Show only first 4 courses in preview
      return this.enrollments.slice(0, 4);
    },
    recentActivityPreview() {
      // Show only first 3 recent activities
      return this.recentActivity.slice(0, 3);
    },
    achievedAchievements() {
      // Only show achieved achievements
      return this.achievements.filter(achievement => achievement.achieved);
    },
    achievedCount() {
      // Count of achieved achievements for stats card
      return this.achievedAchievements.length;
    }
  },
  methods: {
    getCourseImageUrl(course) {
      if (!course.thumbnail) {
        return 'url(/assets/images/course-placeholder.svg)';
      }
      // If thumbnail doesn't start with http or /, prepend /storage/
      if (!course.thumbnail.startsWith('http') && !course.thumbnail.startsWith('/')) {
        return `url(/storage/${course.thumbnail})`;
      }
      return `url(${course.thumbnail})`;
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      if (isNaN(date.getTime())) return '';
      return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      }).format(date);
    },
    getActivityIcon(activity) {
      if (activity.status === 'completed') {
        return 'bi-check-circle';
      } else if (activity.status === 'in_progress') {
        return 'bi-play-circle';
      }
      return 'bi-circle';
    },
    getActivityIconClass(activity) {
      if (activity.status === 'completed') {
        return 'bg-success';
      } else if (activity.status === 'in_progress') {
        return 'bg-primary';
      }
      return 'bg-secondary';
    },
    getActivityTitle(activity) {
      if (activity.status === 'completed') {
        return 'Completed Lesson';
      } else if (activity.status === 'in_progress') {
        return 'Started Lesson';
      }
      return 'Viewed Lesson';
    },
    async fetchDashboardData() {
        this.loading = true;
        this.error = null;
        
      try {
        const response = await axios.get('/api/student/dashboard');
        
        if (response.data) {
          // Assign data with fallbacks
          this.overallProgress = response.data.overallProgress || 0;
          this.completedCourses = response.data.completedCourses || 0;
          this.inProgressCourses = response.data.inProgressCourses || 0;
          this.streak = response.data.streak || 0;
          // Process achievements from API data
          this.achievements = this.processAchievements(response.data.achievements || []);
          this.enrollments = response.data.enrollments || [];
          this.recentActivity = response.data.recentActivity || [];
        }
      } catch (error) {
        if (error.response?.status === 401) {
          this.error = 'Authentication required. Please log in again.';
        } else {
          this.error = 'Failed to load dashboard data. Please try again later.';
        }
        
        // Initialize achievements even if API fails
        this.achievements = this.processAchievements([]);
      } finally {
        this.loading = false;
      }
    },

    viewCertificate(enrollmentId) {
      // Open certificate in new window
      const certificateUrl = `/api/student/certificates/${enrollmentId}/view`;
      const certificateWindow = window.open(certificateUrl, '_blank');
      
      if (!certificateWindow) {
        alert('Please allow pop-ups to view certificates.');
      }
    },

    openAchievementsModal() {
      this.showAchievementsModal = true;
    },

    closeAchievementsModal() {
      this.showAchievementsModal = false;
    },

    /**
     * Get the base achievement definitions
     * This defines all available achievements with their requirements
     * @returns {Array} Array of base achievement objects
     */
    getBaseAchievements() {
      return [
        // Course Completion Milestones
        {
          id: 'first_steps',
          title: "First Steps",
          description: "Complete your very first course!",
          howToAchieve: "Finish any course to unlock this achievement.",
          category: 'course_completion',
          requirement: 1
        },
        {
          id: 'getting_started',
          title: "Getting Started",
          description: "Complete 5 courses.",
          howToAchieve: "Complete 5 different courses to unlock this achievement.",
          category: 'course_completion',
          requirement: 5
        },
        {
          id: 'dedicated_learner',
          title: "Dedicated Learner",
          description: "Complete 10 courses.",
          howToAchieve: "Complete 10 different courses to unlock this achievement.",
          category: 'course_completion',
          requirement: 10
        },
        {
          id: 'knowledge_collector',
          title: "Knowledge Collector",
          description: "Complete 25 courses.",
          howToAchieve: "Complete 25 different courses to unlock this achievement.",
          category: 'course_completion',
          requirement: 25
        },
        {
          id: 'learning_enthusiast',
          title: "Learning Enthusiast",
          description: "Complete 50 courses.",
          howToAchieve: "Complete 50 different courses to unlock this achievement.",
          category: 'course_completion',
          requirement: 50
        },
        {
          id: 'century_scholar',
          title: "Century Scholar",
          description: "Complete 100 courses! A major milestone.",
          howToAchieve: "Complete 100 different courses to unlock this achievement.",
          category: 'course_completion',
          requirement: 100
        },
        {
          id: 'knowledge_master',
          title: "Knowledge Master",
          description: "Complete 500 courses.",
          howToAchieve: "Complete 500 different courses to unlock this achievement.",
          category: 'course_completion',
          requirement: 500
        },
        {
          id: 'learning_legend',
          title: "Learning Legend",
          description: "Complete 1,000 courses!",
          howToAchieve: "Complete 1,000 different courses to unlock this achievement.",
          category: 'course_completion',
          requirement: 1000
        },
        {
          id: 'ultimate_scholar',
          title: "Ultimate Scholar",
          description: "Complete 10,000 courses! An incredible achievement.",
          howToAchieve: "Complete 10,000 different courses to unlock this achievement.",
          category: 'course_completion',
          requirement: 10000
        },
        
        // Learning Streak Achievements
        {
          id: 'week_warrior',
          title: "Week Warrior",
          description: "Maintain a 7-day learning streak.",
          howToAchieve: "Learn something every day for 7 consecutive days.",
          category: 'learning_streak',
          requirement: 7
        },
        {
          id: 'monthly_champion',
          title: "Monthly Champion",
          description: "Maintain a 30-day learning streak.",
          howToAchieve: "Learn something every day for 30 consecutive days.",
          category: 'learning_streak',
          requirement: 30
        },
        {
          id: 'hundred_days_hero',
          title: "Hundred Days Hero",
          description: "Maintain a 100-day learning streak.",
          howToAchieve: "Learn something every day for 100 consecutive days.",
          category: 'learning_streak',
          requirement: 100
        },
        {
          id: 'year_long_learner',
          title: "Year-Long Learner",
          description: "Maintain a 365-day learning streak.",
          howToAchieve: "Learn something every day for 365 consecutive days.",
          category: 'learning_streak',
          requirement: 365
        },
        
        // Speed & Efficiency Achievements
        {
          id: 'speed_demon',
          title: "Speed Demon",
          description: "Complete 5 lessons in a single day.",
          howToAchieve: "Complete 5 or more lessons within 24 hours.",
          category: 'daily_performance',
          requirement: 5
        },
        {
          id: 'marathon_learner',
          title: "Marathon Learner",
          description: "Spend 10 hours learning in a single day.",
          howToAchieve: "Accumulate 10 hours of learning time in one day.",
          category: 'daily_performance',
          requirement: 10
        },
        {
          id: 'quick_finisher',
          title: "Quick Finisher",
          description: "Complete a course in under 2 hours.",
          howToAchieve: "Finish any course from start to finish in under 2 hours.",
          category: 'efficiency',
          requirement: 2
        },
        
        // Perfect Performance Achievements
        {
          id: 'perfectionist',
          title: "Perfectionist",
          description: "Achieve 100% completion rate on 10 consecutive courses.",
          howToAchieve: "Complete 10 courses in a row with 100% completion rate.",
          category: 'performance',
          requirement: 10
        },
        {
          id: 'flawless_execution',
          title: "Flawless Execution",
          description: "Complete 25 courses with perfect attendance.",
          howToAchieve: "Complete 25 courses without skipping any lessons.",
          category: 'performance',
          requirement: 25
        },
        {
          id: 'excellence_pursuer',
          title: "Excellence Pursuer",
          description: "Maintain 95%+ average progress across all courses.",
          howToAchieve: "Keep your average course progress above 95%.",
          category: 'performance',
          requirement: 95
        },
        
        // Special Timing Achievements
        {
          id: 'early_bird',
          title: "Early Bird",
          description: "Complete your first lesson before 6 AM.",
          howToAchieve: "Complete any lesson before 6:00 AM to unlock this.",
          category: 'timing',
          requirement: 1
        },
        {
          id: 'night_owl',
          title: "Night Owl",
          description: "Complete lessons after 11 PM for 7 consecutive days.",
          howToAchieve: "Complete lessons after 11:00 PM for 7 days straight.",
          category: 'timing',
          requirement: 7
        },
        {
          id: 'weekend_warrior',
          title: "Weekend Warrior",
          description: "Complete 20 lessons during weekends.",
          howToAchieve: "Complete 20 lessons on Saturdays and Sundays.",
          category: 'timing',
          requirement: 20
        },
        
        // Community & Engagement
        {
          id: 'certificate_collector',
          title: "Certificate Collector",
          description: "Earn 10 course completion certificates.",
          howToAchieve: "Successfully complete 10 courses to earn their certificates.",
          category: 'engagement',
          requirement: 10
        },
        {
          id: 'curious_explorer',
          title: "Curious Explorer",
          description: "Enroll in courses from 5 different categories.",
          howToAchieve: "Enroll in courses from at least 5 different subject categories.",
          category: 'engagement',
          requirement: 5
        },
        {
          id: 'learning_addict',
          title: "Learning Addict",
          description: "Enroll in 20 courses simultaneously.",
          howToAchieve: "Have 20 or more active course enrollments at the same time.",
          category: 'engagement',
          requirement: 20
        }
      ];
    },

    /**
     * Process achievements by merging API data with base achievement definitions
     * Expected API format: 
     * [{ id: 'achievement_id', achieved: true, earned_at: '2024-01-15T10:30:00Z', progress: 100 }]
     * @param {Array} apiAchievements - Achievements data from API
     * @returns {Array} Processed achievements with achievement status
     */
    processAchievements(apiAchievements) {
      const baseAchievements = this.getBaseAchievements();
      
      return baseAchievements.map(baseAchievement => {
        // Find matching achievement from API (match by ID or title)
        const apiAchievement = apiAchievements.find(
          achievement => achievement.id === baseAchievement.id || achievement.title === baseAchievement.title
        );
        
        return {
          ...baseAchievement,
          achieved: apiAchievement ? (apiAchievement.achieved || apiAchievement.unlocked || false) : false,
          earned_at: apiAchievement ? (apiAchievement.earned_at || apiAchievement.unlocked_at || null) : null,
          progress: apiAchievement ? (apiAchievement.progress || 0) : 0
        };
      });
    }
  },
  mounted() {
    this.fetchDashboardData();
  }
};
</script>

<style scoped>
.dashboard-container {
  width: 100%;
  padding: 0 20px;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 24px;
}

.stat-card {
  display: flex;
  align-items: center;
  padding: 20px;
  border-radius: 12px;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.bg-primary {
  background: linear-gradient(135deg, #001d3d, #0077b6);
}

.bg-success {
  background: linear-gradient(135deg, #0077b6, #003d5c);
}

.bg-warning {
  background: linear-gradient(135deg, #0099cc, #004d66);
}

.bg-info {
  background: linear-gradient(135deg, #4a90a4, #001d3d);
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  color: white;
  font-size: 1.8rem;
  background-color: rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  margin-right: 16px;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.9rem;
  margin: 5px 0 0 0;
  opacity: 0.9;
}

.stat-info {
  display: block;
  font-size: 0.8rem;
  margin-top: 5px;
  opacity: 0.8;
}

/* Dashboard Content Layout */
.dashboard-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
}

/* Card Styling */
.card {
  border-radius: 12px;
  border: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  margin-bottom: 24px;
  overflow: hidden;
  background: white;
}

.card-header {
  background-color: white;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.card-header h5 {
  font-weight: 600;
  color: #333;
  margin: 0;
}

.card-body {
  padding: 20px;
}

.card-footer {
  background-color: #f8f9fa;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  padding: 12px 20px;
}

/* Course Grid */
.course-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}

.course-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease;
}

.course-card:hover {
  transform: translateY(-2px);
}

.course-thumbnail {
  height: 180px;
  background-size: cover;
  background-position: center;
  background-color: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #0077b6;
}

.course-content {
  padding: 16px;
}

.course-title {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 8px;
  color: #333;
}

.course-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.progress-wrapper {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
}

.progress-bar {
  flex: 1;
  height: 6px;
  background-color: #e9ecef;
  border-radius: 3px;
  overflow: hidden;
  margin-right: 8px;
}

.progress-fill {
  height: 100%;
  border-radius: 3px;
  transition: width 0.3s ease;
}

.progress-fill.bg-primary {
  background-color: #0077b6;
}

.progress-fill.bg-success {
  background-color: #2cc069;
}

.progress-fill.bg-danger {
  background-color: #dc3545;
}

.progress-text {
  font-size: 12px;
  color: #666;
  min-width: 35px;
}

.course-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.course-actions .btn {
  font-size: 0.8rem;
  padding: 6px 12px;
  border-radius: 6px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-outline-warning {
  background: transparent;
  color: #f59e0b;
  border: 1px solid #f59e0b;
}

.btn-outline-warning:hover {
  background: #f59e0b;
  color: white;
}

/* Activity & Achievement Lists */
.activity-list,
.achievement-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.activity-item,
.achievement-item {
  display: flex;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child,
.achievement-item:last-child {
  border-bottom: none;
}

.activity-icon,
.achievement-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  color: white;
  font-size: 1.2rem;
  margin-right: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.activity-icon.bg-success {
  background-color: #2cc069;
}

.activity-icon.bg-primary {
  background-color: #0077b6;
}

.activity-icon.bg-secondary {
  background-color: #6c757d;
}

.achievement-icon.bg-warning {
  background-color: #f59f0b;
}

.activity-content,
.achievement-content {
  flex: 1;
}

.activity-title,
.achievement-title {
  font-size: 14px;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: #333;
}

.activity-meta {
  line-height: 1.4;
}

/* Empty States */
.empty-data {
  text-align: center;
  padding: 40px 20px;
}

.empty-data i {
  font-size: 3rem;
  color: #6c757d;
  margin-bottom: 16px;
}

.empty-data p {
  color: #6c757d;
  margin-bottom: 16px;
}

/* Button Styling */
.btn-primary {
  background-color: #0077b6;
  border-color: #0077b6;
  border-radius: 8px;
  padding: 8px 16px;
  font-weight: 500;
  color: white;
}

.btn-primary:hover {
  background-color: #001d3d;
  border-color: #001d3d;
}

.btn-outline-primary {
  color: #0077b6;
  border-color: #0077b6;
  border-radius: 8px;
  padding: 8px 16px;
  font-weight: 500;
}

.btn-outline-primary:hover {
  background-color: #0077b6;
  color: white;
}

.btn-block {
  width: 100%;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 0.875rem;
}

.badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.badge.bg-primary {
  background-color: #0077b6 !important;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .dashboard-container {
    max-width: 100%;
    padding: 0 16px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .dashboard-content {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .dashboard-container {
    padding: 0 15px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .course-grid {
    grid-template-columns: 1fr;
  }
}

/* Achievements Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
  padding: 20px;
  backdrop-filter: blur(4px);
}

.achievements-modal {
  background: white;
  border-radius: 20px;
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
  animation: slideUp 0.3s ease-out;
  margin: auto;
}

@keyframes slideUp {
  from { 
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  to { 
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.achievements-header {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 24px;
  background: linear-gradient(135deg, #001d3d 0%, #0077b6 100%);
  color: white;
  position: relative;
}

.achievements-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  flex-shrink: 0;
}

.achievements-icon i {
  font-size: 1.5rem;
  color: white;
}

.achievements-header h3 {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 600;
  flex: 1;
}

.close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.1);
}

.achievements-body {
  padding: 0;
  max-height: 70vh;
  overflow-y: auto;
}

.achievements-list {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.achievement-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px 24px;
  border-bottom: 1px solid #e9ecef;
  transition: all 0.2s ease;
}

.achievement-item:last-child {
  border-bottom: none;
}

.achievement-item:hover {
  background: #f8f9fa;
}

.achievement-item:first-child {
  padding-top: 24px;
}

.achievement-item:last-child {
  padding-bottom: 24px;
}

.achievement-item .achievement-icon {
  width: 50px;
  height: 50px;
  font-size: 1.3rem;
  flex-shrink: 0;
}

.achievement-locked {
  opacity: 0.7;
}

.achievement-icon.bg-secondary {
  background-color: #adb5bd !important;
}

.achievement-locked .achievement-name,
.achievement-locked .achievement-desc {
  color: #6c757d !important;
}

.achievement-how-to {
  font-size: 0.8rem;
  font-style: italic;
  margin-top: 4px;
  display: block;
}

.achievement-info {
  flex: 1;
}

.achievement-name {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: #333;
}

.achievement-desc {
  font-size: 0.9rem;
  color: #666;
  margin: 0 0 8px 0;
  line-height: 1.4;
}

.achievement-date {
  font-size: 0.8rem;
  color: #2cc069 !important;
  font-weight: 600;
}

.achievement-status {
  display: flex;
  align-items: center;
  margin-left: auto;
  flex-shrink: 0;
}

.achievement-status i {
  font-size: 1.2rem;
  color: #2cc069;
}

.empty-achievements {
  text-align: center;
  padding: 40px 20px;
}

.empty-achievements i {
  font-size: 3rem;
  color: #dee2e6;
  margin-bottom: 16px;
}

.empty-achievements h5 {
  color: #6c757d;
  margin-bottom: 8px;
}

.empty-achievements p {
  color: #6c757d;
  margin: 0;
}

/* Mobile modal adjustments */
@media (max-width: 768px) {
  .achievements-modal {
    max-width: 95%;
    margin: 20px;
  }
  
  .achievements-header {
    padding: 20px;
  }
  
  .achievements-header h3 {
    font-size: 1.2rem;
  }
  
  .achievements-icon {
    width: 40px;
    height: 40px;
  }
  
  .achievements-icon i {
    font-size: 1.3rem;
  }
  
  .achievements-body {
    padding: 0;
    max-height: 80vh;
  }
  
  .achievement-item {
    padding: 16px 20px;
    flex-direction: row;
    align-items: center;
    gap: 12px;
  }
  
  .achievement-item:first-child {
    padding-top: 20px;
  }
  
  .achievement-item:last-child {
    padding-bottom: 20px;
  }
  
  .achievement-item .achievement-icon {
    width: 45px;
    height: 45px;
    font-size: 1.2rem;
  }
  
  .achievement-info {
    flex: 1;
  }
  
  .achievement-status {
    margin-left: auto;
  }
}
</style>