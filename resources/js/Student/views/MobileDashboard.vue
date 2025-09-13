<template>
  <div class="mobile-student-dashboard">
    <!-- Mobile Navigation Header -->
    <div class="mobile-nav-header">
      <button class="mobile-menu-btn" @click="showMobileMenu = true">
        <i class="bi bi-list"></i>
      </button>
      <h1>Dashboard</h1>
      <div class="mobile-user-info">
        <i class="bi bi-person-circle"></i>
      </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div v-if="showMobileMenu" class="mobile-menu-overlay" @click="showMobileMenu = false">
      <div class="mobile-menu" @click.stop>
        <div class="mobile-menu-header">
          <h2>Student Dashboard</h2>
          <button class="close-btn" @click="showMobileMenu = false">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <nav class="mobile-menu-nav">
          <router-link to="/student/dashboard" class="mobile-nav-item" @click="showMobileMenu = false">
            <i class="bi bi-speedometer2"></i>
            Dashboard
          </router-link>
          <router-link to="/student/courses" class="mobile-nav-item" @click="showMobileMenu = false">
            <i class="bi bi-journal-text"></i>
            My Courses
          </router-link>
          <router-link to="/student/browse" class="mobile-nav-item" @click="showMobileMenu = false">
            <i class="bi bi-grid-3x3-gap"></i>
            Browse Courses
          </router-link>
          <router-link to="/student/certificates" class="mobile-nav-item" @click="showMobileMenu = false">
            <i class="bi bi-award"></i>
            Certificates
          </router-link>
          <router-link to="/student/messages" class="mobile-nav-item" @click="showMobileMenu = false">
            <i class="bi bi-chat-dots"></i>
            Messages
          </router-link>
        </nav>
        <div class="mobile-menu-footer">
          <button @click="logout" class="logout-btn">
            <i class="bi bi-box-arrow-right"></i>
            Logout
          </button>
        </div>
      </div>
    </div>

    <!-- Achievement Modal -->
    <div v-if="showAllAchievements" class="achievement-modal-overlay" @click="showAllAchievements = false">
      <div class="achievement-modal" @click.stop>
        <div class="modal-header">
          <h3>All Achievements</h3>
          <button class="close-btn" @click="showAllAchievements = false">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="achievement-stats">
            <span class="achievement-progress">{{ achievedCount }}/{{ achievements.length }} Unlocked</span>
          </div>
          <div class="achievements-list">
            <div 
              v-for="achievement in achievements" 
              :key="achievement.id || achievement.title"
              class="achievement-item"
              :class="{ 'achieved': achievement.achieved }"
            >
              <div class="achievement-icon">
                <i class="bi bi-trophy-fill"></i>
              </div>
              <div class="achievement-content">
                <div class="achievement-title">{{ achievement.title }}</div>
                <div class="achievement-description">{{ achievement.description }}</div>
                <div v-if="!achievement.achieved" class="achievement-requirement">
                  {{ achievement.howToAchieve }}
                </div>
              </div>
              <div v-if="achievement.achieved" class="achievement-status">
                <i class="bi bi-check-circle-fill"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Stats Cards - Mobile Layout -->
    <div class="mobile-stats-grid">
      <div class="mobile-stat-card bg-primary">
        <div class="stat-icon">
          <i class="bi bi-graph-up"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ overallProgress }}%</div>
          <div class="stat-label">Overall Progress</div>
        </div>
      </div>
      
      <div class="mobile-stat-card bg-success">
        <div class="stat-icon">
          <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ completedCourses }}</div>
          <div class="stat-label">Completed</div>
          <div class="stat-info">{{ inProgressCourses }} in progress</div>
        </div>
      </div>
      
      <div class="mobile-stat-card bg-warning">
        <div class="stat-icon">
          <i class="bi bi-lightning"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ streak }}</div>
          <div class="stat-label">Day Streak</div>
          <div class="stat-info">Keep going!</div>
        </div>
      </div>
      
      <div class="mobile-stat-card bg-info">
        <div class="stat-icon">
          <i class="bi bi-trophy"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ achievedCount }}</div>
          <div class="stat-label">Achievements</div>
          <div class="stat-info">Great job!</div>
        </div>
      </div>
    </div>

    <!-- My Courses Section -->
    <div class="mobile-courses-section">
      <div class="section-header">
        <h3>My Courses</h3>
        <router-link to="/student/courses" class="view-all-link">View All</router-link>
      </div>
      
      <div v-if="enrollments.length > 0" class="mobile-courses-list">
        <div 
          v-for="enrollment in enrolledCoursesPreview" 
          :key="enrollment.id"
          class="mobile-course-card"
        >
          <div class="course-thumbnail" :style="{ backgroundImage: enrollment.course.thumbnail ? `url(${enrollment.course.thumbnail})` : 'none' }">
            <i v-if="!enrollment.course.thumbnail" class="bi bi-book"></i>
          </div>
          <div class="course-content">
            <div class="course-title">{{ enrollment.course.title }}</div>
            <div class="course-meta">
              <span class="course-level">{{ enrollment.course.level }}</span>
              <span class="course-status">
                {{ enrollment.status === 'completed' ? 'Completed' : `${enrollment.progress_percentage}% Complete` }}
              </span>
            </div>
            <div class="progress-container">
              <div class="progress-bar">
                <div class="progress-fill" :class="enrollment.status === 'completed' ? 'bg-success' : 'bg-primary'" :style="{ width: enrollment.progress_percentage + '%' }"></div>
              </div>
              <span class="progress-text">{{ enrollment.progress_percentage }}%</span>
            </div>
            <router-link 
              :to="`/student/learning/${enrollment.course.id}`" 
              class="mobile-course-btn"
            >
              {{ enrollment.status === 'completed' ? 'Review Course' : 'Continue Learning' }}
            </router-link>
          </div>
        </div>
      </div>
      
      <div v-else class="empty-courses">
        <i class="bi bi-book"></i>
        <p>You haven't enrolled in any courses yet.</p>
        <router-link to="/student/browse" class="browse-btn">
          Browse Courses
        </router-link>
      </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="mobile-activity-section">
      <div class="section-header">
        <h3>Recent Activity</h3>
      </div>
      
      <div v-if="recentActivity.length > 0" class="mobile-activity-list">
        <div v-for="activity in recentActivity.slice(0, 3)" :key="activity.id" class="mobile-activity-item">
          <div class="activity-icon" :class="getActivityIconClass(activity)">
            <i :class="getActivityIcon(activity)"></i>
          </div>
          <div class="activity-content">
            <div class="activity-title">{{ getActivityTitle(activity) }}</div>
            <div class="activity-course">{{ activity.lesson.module.course.title }}</div>
            <div class="activity-lesson">{{ activity.lesson.title }}</div>
            <div class="activity-time">{{ formatDate(activity.updated_at) }}</div>
          </div>
        </div>
      </div>
      
      <div v-else class="empty-activity">
        <i class="bi bi-activity"></i>
        <p>No recent activity to show.</p>
      </div>
    </div>

    <!-- Achievements Section -->
    <div class="mobile-achievements-section">
      <div class="section-header">
        <h3>Recent Achievements</h3>
        <button class="view-all-btn" @click="showAllAchievements = true">View All</button>
      </div>
      
      <div v-if="achievedAchievements.length > 0" class="mobile-achievements-list">
        <div v-for="achievement in achievedAchievements.slice(0, 3)" :key="achievement.id || achievement.title" class="mobile-achievement-item">
          <div class="achievement-icon bg-warning">
            <i class="bi bi-trophy-fill"></i>
          </div>
          <div class="achievement-content">
            <div class="achievement-title">{{ achievement.title }}</div>
            <div class="achievement-description">{{ achievement.description }}</div>
          </div>
        </div>
      </div>
      
      <div v-else class="empty-achievements">
        <i class="bi bi-award"></i>
        <p>No achievements yet.</p>
      </div>
    </div>



    <!-- Loading State -->
    <div v-if="loading" class="mobile-loading">
      <div class="loading-spinner"></div>
      <p>Loading dashboard...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="mobile-error">
      <i class="bi bi-exclamation-triangle"></i>
      <p>{{ error }}</p>
      <button @click="fetchDashboardData" class="retry-btn">Retry</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'MobileStudentDashboard',
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
      // Mobile UI state
      showMobileMenu: false,
      showAllAchievements: false
    };
  },
  computed: {
    enrolledCoursesPreview() {
      // Show only first 2 courses in mobile preview
      return this.enrollments.slice(0, 2);
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
    formatDate(dateString) {
      const date = new Date(dateString);
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
        // Add more achievements as needed - keeping mobile version lighter
        {
          id: 'week_warrior',
          title: "Week Warrior",
          description: "Maintain a 7-day learning streak.",
          howToAchieve: "Learn something every day for 7 consecutive days.",
          category: 'learning_streak',
          requirement: 7
        },
        {
          id: 'speed_demon',
          title: "Speed Demon",
          description: "Complete 5 lessons in a single day.",
          howToAchieve: "Complete 5 or more lessons within 24 hours.",
          category: 'daily_performance',
          requirement: 5
        },
        {
          id: 'early_bird',
          title: "Early Bird",
          description: "Complete your first lesson before 6 AM.",
          howToAchieve: "Complete any lesson before 6:00 AM to unlock this.",
          category: 'timing',
          requirement: 1
        }
      ];
    },

    async logout() {
      try {
        await axios.post('/logout');
        window.location.href = '/';
      } catch (error) {
        // Handle logout error silently in mobile
        window.location.href = '/';
      }
    }
  },
  mounted() {
    // Initialize achievements before API call for fallback
    this.achievements = this.processAchievements([]);
    this.fetchDashboardData();
  }
};
</script>

<style scoped>
/* Mobile Student Dashboard Layout */
.mobile-student-dashboard {
  padding: 0;
  background-color: #f8fafc;
  min-height: 100vh;
}

/* Mobile Navigation Header */
.mobile-nav-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  background: linear-gradient(135deg, #6a5acd, #8a6ccf);
  color: white;
  position: sticky;
  top: 0;
  z-index: 100;
}

.mobile-menu-btn {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  padding: 8px;
  cursor: pointer;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.mobile-menu-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.mobile-nav-header h1 {
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0;
  flex: 1;
  text-align: center;
}

.mobile-user-info {
  font-size: 1.5rem;
  padding: 8px;
}

/* Mobile Menu Overlay */
.mobile-menu-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  display: flex;
  align-items: flex-start;
  justify-content: flex-start;
}

.mobile-menu {
  width: 280px;
  height: 100vh;
  background: white;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  animation: slideInLeft 0.3s ease-out;
}

@keyframes slideInLeft {
  from {
    transform: translateX(-100%);
  }
  to {
    transform: translateX(0);
  }
}

.mobile-menu-header {
  padding: 20px;
  background: linear-gradient(135deg, #6a5acd, #8a6ccf);
  color: white;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.mobile-menu-header h2 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  padding: 4px;
  cursor: pointer;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.close-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.mobile-menu-nav {
  flex: 1;
  padding: 20px 0;
}

.mobile-nav-item {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  color: #4a5568;
  text-decoration: none;
  transition: all 0.2s ease;
  border-left: 3px solid transparent;
}

.mobile-nav-item:hover,
.mobile-nav-item.router-link-active {
  background-color: #f3f4f6;
  border-left-color: #6a5acd;
  color: #6a5acd;
}

.mobile-nav-item i {
  width: 24px;
  margin-right: 12px;
  font-size: 1.1rem;
  text-align: center;
}

.mobile-menu-footer {
  padding: 20px;
  border-top: 1px solid #e2e8f0;
}

.logout-btn {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 12px 16px;
  background: none;
  border: 1px solid #dc2626;
  color: #dc2626;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.logout-btn:hover {
  background-color: #dc2626;
  color: white;
}

.logout-btn i {
  margin-right: 8px;
}

/* Main Content Padding */
.mobile-stats-grid,
.mobile-courses-section,
.mobile-activity-section,
.mobile-achievements-section {
  margin: 0 16px;
}

.mobile-stats-grid {
  margin-top: 16px;
}

/* Mobile Stats Grid */
.mobile-stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 24px;
}

.mobile-stat-card {
  display: flex;
  align-items: center;
  padding: 16px;
  border-radius: 12px;
  color: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease;
}

.mobile-stat-card:active {
  transform: scale(0.98);
}

.bg-primary {
  background: linear-gradient(135deg, #6a5acd, #8a6ccf);
}

.bg-success {
  background: linear-gradient(135deg, #2cc069, #40d787);
}

.bg-warning {
  background: linear-gradient(135deg, #f59f0b, #ffbc3e);
}

.bg-info {
  background: linear-gradient(135deg, #8a70d6, #9e86e8);
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  color: white;
  font-size: 1.5rem;
  background-color: rgba(255, 255, 255, 0.2);
  margin-right: 12px;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.8rem;
  opacity: 0.9;
  margin-bottom: 2px;
}

.stat-info {
  font-size: 0.7rem;
  opacity: 0.8;
}

/* Section Headers */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.section-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.view-all-link {
  color: #6a5acd;
  font-size: 0.875rem;
  font-weight: 500;
  text-decoration: none;
}

.view-all-link:hover {
  text-decoration: underline;
}

.view-all-btn {
  background: #6a5acd;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.view-all-btn:hover {
  background-color: #5a4abd;
}

/* My Courses Section */
.mobile-courses-section {
  margin-bottom: 24px;
}

.mobile-courses-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.mobile-course-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transition: transform 0.2s ease;
}

.mobile-course-card:active {
  transform: scale(0.98);
}

.course-thumbnail {
  height: 120px;
  background-size: cover;
  background-position: center;
  background-color: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #6a5acd;
}

.course-content {
  padding: 16px;
}

.course-title {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 8px;
  color: #1f2937;
}

.course-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.course-level {
  background-color: #6a5acd;
  color: white;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
}

.course-status {
  font-size: 0.75rem;
  color: #6b7280;
}

.progress-container {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
}

.progress-bar {
  flex: 1;
  height: 6px;
  background-color: #f3f4f6;
  border-radius: 3px;
  overflow: hidden;
  margin-right: 8px;
}

.progress-fill {
  height: 100%;
  transition: width 0.3s ease;
}

.progress-fill.bg-primary {
  background-color: #6a5acd;
}

.progress-fill.bg-success {
  background-color: #2cc069;
}

.progress-fill.bg-danger {
  background-color: #dc2626;
}

.progress-text {
  font-size: 0.75rem;
  color: #6b7280;
  min-width: 32px;
}

.mobile-course-btn {
  display: block;
  width: 100%;
  padding: 8px 16px;
  background-color: #6a5acd;
  color: white;
  text-decoration: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  text-align: center;
}

.mobile-course-btn:hover {
  background-color: #5a4abd;
}

.empty-courses {
  background: white;
  border-radius: 12px;
  padding: 40px 20px;
  text-align: center;
  color: #6b7280;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.empty-courses i {
  font-size: 2.5rem;
  margin-bottom: 12px;
}

.browse-btn {
  display: inline-block;
  margin-top: 12px;
  padding: 8px 16px;
  background-color: #6a5acd;
  color: white;
  text-decoration: none;
  border-radius: 6px;
  font-size: 0.875rem;
}

/* Recent Activity Section */
.mobile-activity-section {
  margin-bottom: 24px;
}

.mobile-activity-list {
  background-color: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.mobile-activity-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #f3f4f6;
}

.mobile-activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  color: white;
  font-size: 0.875rem;
}

.activity-icon.bg-success {
  background-color: #2cc069;
}

.activity-icon.bg-primary {
  background-color: #6a5acd;
}

.activity-icon.bg-secondary {
  background-color: #6b7280;
}

.activity-content {
  flex: 1;
}

.activity-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 2px;
}

.activity-course {
  font-size: 0.75rem;
  color: #6a5acd;
  font-weight: 500;
  margin-bottom: 2px;
}

.activity-lesson {
  font-size: 0.75rem;
  color: #6b7280;
  margin-bottom: 2px;
}

.activity-time {
  font-size: 0.7rem;
  color: #9ca3af;
}

.empty-activity {
  background: white;
  border-radius: 12px;
  padding: 40px 20px;
  text-align: center;
  color: #6b7280;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.empty-activity i {
  font-size: 2rem;
  margin-bottom: 12px;
}

/* Achievements Section */
.mobile-achievements-section {
  margin-bottom: 24px;
}

.mobile-achievements-list {
  background-color: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.mobile-achievement-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #f3f4f6;
}

.mobile-achievement-item:last-child {
  border-bottom: none;
}

.achievement-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  color: white;
  font-size: 0.875rem;
}

.achievement-content {
  flex: 1;
}

.achievement-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 2px;
}

.achievement-description {
  font-size: 0.75rem;
  color: #6b7280;
}

.empty-achievements {
  background: white;
  border-radius: 12px;
  padding: 40px 20px;
  text-align: center;
  color: #6b7280;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.empty-achievements i {
  font-size: 2rem;
  margin-bottom: 12px;
}



/* Loading and Error States */
.mobile-loading, .mobile-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  text-align: center;
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f3f4f6;
  border-top: 3px solid #6a5acd;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.mobile-error i {
  font-size: 2rem;
  color: #dc2626;
  margin-bottom: 16px;
}

.retry-btn {
  background-color: #6a5acd;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  margin-top: 12px;
  cursor: pointer;
}

.retry-btn:active {
  background-color: #5a4abd;
}

/* Achievement Modal */
.achievement-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.achievement-modal {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  max-height: 80vh;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  animation: modalAppear 0.3s ease-out;
}

@keyframes modalAppear {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px;
  background: linear-gradient(135deg, #f59f0b, #ff8c00);
  color: white;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-body {
  padding: 20px;
  max-height: calc(80vh - 140px);
  overflow-y: auto;
}

.achievement-stats {
  text-align: center;
  margin-bottom: 20px;
  padding: 12px;
  background-color: #f8f9fa;
  border-radius: 8px;
}

.achievement-progress {
  font-size: 1.1rem;
  font-weight: 600;
  color: #6a5acd;
}

.achievements-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.achievement-item {
  display: flex;
  align-items: center;
  padding: 16px;
  border-radius: 8px;
  border: 2px solid transparent;
  transition: all 0.2s ease;
}

.achievement-item:not(.achieved) {
  background-color: #f8f9fa;
  opacity: 0.6;
}

.achievement-item.achieved {
  background: linear-gradient(135deg, #fff3cd, #fff8e1);
  border-color: #f59f0b;
}

.achievement-item .achievement-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 16px;
  font-size: 1.2rem;
}

.achievement-item:not(.achieved) .achievement-icon {
  background-color: #e9ecef;
  color: #6c757d;
}

.achievement-item.achieved .achievement-icon {
  background: linear-gradient(135deg, #f59f0b, #ff8c00);
  color: white;
}

.achievement-item .achievement-content {
  flex: 1;
}

.achievement-item .achievement-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 4px;
}

.achievement-item .achievement-description {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 4px;
}

.achievement-item .achievement-requirement {
  font-size: 0.75rem;
  color: #9ca3af;
  font-style: italic;
}

.achievement-status {
  color: #22c55e;
  font-size: 1.5rem;
}

/* Responsive Design */
@media (max-width: 480px) {
  .mobile-stats-grid {
    grid-template-columns: 1fr;
  }
  
  .mobile-course-card {
    margin-bottom: 8px;
  }
  
  .course-thumbnail {
    height: 100px;
  }

  .achievement-modal {
    margin: 10px;
    max-height: 90vh;
  }

  .modal-header {
    padding: 16px;
  }

  .modal-body {
    padding: 16px;
    max-height: calc(90vh - 120px);
  }

  .achievement-item {
    padding: 12px;
  }

  .achievement-item .achievement-icon {
    width: 32px;
    height: 32px;
    margin-right: 12px;
    font-size: 1rem;
  }
}
</style> 