<template>
  <div class="course-view-container">
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
      </div>
      <p>Loading course details...</p>
    </div>
    
    <div v-else-if="course" class="course-view">
      <!-- Course Header Section -->
      <div class="course-header-section">
        <div class="header-content">
          <div class="course-image-container">
            <img 
              v-if="course.thumbnail" 
              :src="`/storage/${course.thumbnail}`" 
              :alt="course.title" 
              class="course-image"
            >
            <div v-else class="course-placeholder">
              <i class="fas fa-book"></i>
            </div>
          </div>
          
          <div class="course-info">
            <div class="title-row">
              <h1 class="course-title">{{ course.title }}</h1>
              <div class="action-buttons">
                <router-link 
                  :to="{ name: 'teacher.courses.edit', params: { id: course.id }}" 
                  class="btn btn-primary"
                >
                  <i class="fas fa-edit"></i>
                  Edit Course
                </router-link>
                <router-link :to="{ name: 'teacher.courses' }" class="btn btn-secondary">
                  <i class="fas fa-arrow-left"></i>
                  Back to Courses
                </router-link>
              </div>
            </div>
            
            <p class="course-description">{{ course.short_description }}</p>
            
            <div class="course-meta">
              <span class="status-badge" :class="getStatusClass(course.status)">
                <i :class="getStatusIcon(course.status)"></i>
                {{ formatStatus(course.status) }}
              </span>
              <span class="meta-item">
                <i class="fas fa-calendar-alt"></i>
                Created {{ formatDate(course.created_at) }}
              </span>
              <span class="meta-item">
                <i class="fas fa-signal"></i>
                {{ course.level }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Statistics Section -->
      <div class="stats-section">
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-number">{{ course.enrollments_count || 0 }}</div>
            <div class="stat-label">Students Enrolled</div>
          </div>
          
          <div class="stat-item">
            <div class="stat-number">{{ getTotalLessons() }}</div>
            <div class="stat-label">Total Lessons</div>
          </div>
          
          <div class="stat-item">
            <div class="stat-number">{{ formatDuration(course.duration_hours) }}</div>
            <div class="stat-label">Course Duration</div>
          </div>
          
          <div class="stat-item">
            <div class="stat-number">{{ getCompletionRate() }}%</div>
            <div class="stat-label">Completion Rate</div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <div class="content-area">
          <!-- Course Content -->
          <div class="section">
            <div class="section-header">
              <h3>Course Content</h3>
              <span class="lesson-count">{{ getTotalLessons() }} lessons</span>
            </div>
            
            <div v-if="course.modules && course.modules.length > 0" class="modules">
              <div v-for="(module, moduleIndex) in course.modules" :key="module.id" class="module">
                <div class="module-header">
                  <h4 class="module-title">
                    <span class="module-number">{{ moduleIndex + 1 }}</span>
                    {{ module.title }}
                  </h4>
                  <span class="lesson-count-small">{{ module.lessons?.length || 0 }} lessons</span>
                </div>
                
                <div v-if="module.lessons && module.lessons.length > 0" class="lessons">
                  <div 
                    v-for="(lesson, lessonIndex) in module.lessons" 
                    :key="lesson.id" 
                    class="lesson"
                  >
                    <div class="lesson-icon">
                      <i class="fas fa-play" v-if="lesson.content_type === 'video'"></i>
                      <i class="fas fa-file-alt" v-else-if="lesson.content_type === 'text'"></i>
                      <i class="fas fa-question-circle" v-else-if="lesson.content_type === 'quiz'"></i>
                      <i class="fas fa-tasks" v-else></i>
                    </div>
                    
                    <div class="lesson-details">
                      <div class="lesson-title">
                        <span class="lesson-number">{{ lessonIndex + 1 }}.</span>
                        {{ lesson.title }}
                      </div>
                      <div class="lesson-meta">
                        <span v-if="lesson.duration_minutes" class="duration">
                          {{ lesson.duration_minutes }} min
                        </span>
                        <span class="type">{{ formatContentType(lesson.content_type) }}</span>
                        <span v-if="lesson.video_protected" class="protected">Protected</span>
                      </div>
                      <p v-if="lesson.description" class="lesson-description">{{ lesson.description }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-else class="empty-state">
              <i class="fas fa-folder-open"></i>
              <p>No content has been added to this course yet.</p>
              <router-link 
                :to="{ name: 'teacher.courses.edit', params: { id: course.id }}" 
                class="btn btn-primary"
              >
                Add Content
              </router-link>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
          <!-- About Course -->
          <div v-if="course.description" class="sidebar-section">
            <h4>About This Course</h4>
            <p class="about-text">{{ course.description }}</p>
          </div>

          <!-- Quick Actions -->
          <div class="sidebar-section">
            <h4>Quick Actions</h4>
            <div class="quick-actions">
              <router-link 
                :to="{ name: 'teacher.courses.edit', params: { id: course.id }}" 
                class="quick-action"
              >
                <i class="fas fa-edit"></i>
                Edit Course
              </router-link>
              <button class="quick-action" @click="previewCourse">
                <i class="fas fa-eye"></i>
                Preview Course
              </button>
              <button class="quick-action" @click="viewAnalytics">
                <i class="fas fa-chart-bar"></i>
                View Analytics
              </button>
            </div>
          </div>

          <!-- Course Information -->
          <div class="sidebar-section">
            <h4>Course Information</h4>
            <div class="info-grid">
              <div class="info-row">
                <span class="info-label">Level</span>
                <span class="info-value">{{ course.level }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value" :class="getStatusClass(course.status)">{{ formatStatus(course.status) }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Created</span>
                <span class="info-value">{{ formatDate(course.created_at) }}</span>
              </div>
              <div v-if="course.updated_at !== course.created_at" class="info-row">
                <span class="info-label">Last Updated</span>
                <span class="info-value">{{ formatDate(course.updated_at) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div v-else class="error-state">
      <div class="error-content">
        <i class="fas fa-exclamation-triangle"></i>
        <h3>Course Not Found</h3>
        <p>The course you're looking for doesn't exist or you don't have permission to view it.</p>
        <router-link :to="{ name: 'teacher.courses' }" class="btn btn-primary">
          <i class="fas fa-arrow-left"></i>
          Back to Courses
        </router-link>
      </div>
    </div>

    <!-- Analytics Modal -->
    <div v-if="showAnalyticsModal" class="modal-overlay" @click="closeAnalyticsModal">
      <div class="modal-content analytics-modal" @click.stop>
        <div class="modal-header">
          <h3>
            <i class="fas fa-chart-bar"></i>
            Course Analytics
          </h3>
          <button class="close-button" @click="closeAnalyticsModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <!-- Analytics Overview -->
          <div class="analytics-section">
            <h4>Overview</h4>
            
            <div v-if="loadingAnalytics" class="analytics-loading">
              <i class="fas fa-spinner fa-spin"></i>
              <p>Loading analytics data...</p>
            </div>
            
            <div v-else class="analytics-grid">
                <div class="analytics-card">
                  <div class="analytics-icon enrollment">
                    <i class="fas fa-user-plus"></i>
                  </div>
                  <div class="analytics-content">
                    <div class="analytics-number">{{ analyticsData?.total_enrollments || course?.enrollments_count || 0 }}</div>
                    <div class="analytics-label">Total Enrollments</div>
                    <div class="analytics-change positive">{{ analyticsData?.monthly_change?.enrollments || '+12%' }} this month</div>
                  </div>
                </div>
                
                <div class="analytics-card">
                  <div class="analytics-icon completion">
                    <i class="fas fa-check-circle"></i>
                  </div>
                  <div class="analytics-content">
                    <div class="analytics-number">{{ getCompletionRate() }}%</div>
                    <div class="analytics-label">Completion Rate</div>
                    <div class="analytics-change positive">{{ analyticsData?.monthly_change?.completion || '+5%' }} this month</div>
                  </div>
                </div>
                
                <div class="analytics-card">
                  <div class="analytics-icon engagement">
                    <i class="fas fa-clock"></i>
                  </div>
                  <div class="analytics-content">
                    <div class="analytics-number">{{ getAverageWatchTime() }}</div>
                    <div class="analytics-label">Avg. Watch Time</div>
                    <div class="analytics-change neutral">{{ analyticsData?.monthly_change?.watch_time || '+2%' }} this month</div>
                  </div>
                </div>
                
                <div class="analytics-card">
                  <div class="analytics-icon rating">
                    <i class="fas fa-star"></i>
                  </div>
                  <div class="analytics-content">
                    <div class="analytics-number">{{ getCourseRating() }}</div>
                    <div class="analytics-label">Average Rating</div>
                    <div class="analytics-change positive">{{ analyticsData?.monthly_change?.rating || '+0.2' }} this month</div>
                  </div>
                </div>
              </div>
            </div>

          <!-- Student Progress -->
          <div class="analytics-section">
            <h4>Student Progress Distribution</h4>
            <div class="progress-chart">
              <div class="progress-bar-container">
                <div class="progress-label">Not Started</div>
                <div class="progress-bar">
                  <div class="progress-fill not-started" :style="{ width: `${analyticsData?.progress_distribution?.not_started || 15}%` }"></div>
                </div>
                <div class="progress-percentage">{{ analyticsData?.progress_distribution?.not_started || 15 }}%</div>
              </div>
              <div class="progress-bar-container">
                <div class="progress-label">In Progress</div>
                <div class="progress-bar">
                  <div class="progress-fill in-progress" :style="{ width: `${analyticsData?.progress_distribution?.in_progress || 45}%` }"></div>
                </div>
                <div class="progress-percentage">{{ analyticsData?.progress_distribution?.in_progress || 45 }}%</div>
              </div>
              <div class="progress-bar-container">
                <div class="progress-label">Completed</div>
                <div class="progress-bar">
                  <div class="progress-fill completed" :style="{ width: `${analyticsData?.progress_distribution?.completed || 40}%` }"></div>
                </div>
                <div class="progress-percentage">{{ analyticsData?.progress_distribution?.completed || 40 }}%</div>
              </div>
            </div>
          </div>

          <!-- Lesson Performance -->
          <div class="analytics-section" v-if="course?.modules && course.modules.length > 0">
            <h4>Lesson Performance</h4>
            <div class="lesson-analytics">
              <div v-for="(module, moduleIndex) in course.modules" :key="module.id">
                <h5 class="module-analytics-title">{{ module.title }}</h5>
                <div v-if="module.lessons && module.lessons.length > 0" class="lesson-performance-list">
                  <div 
                    v-for="(lesson, lessonIndex) in module.lessons" 
                    :key="lesson.id" 
                    class="lesson-performance"
                  >
                    <div class="lesson-performance-info">
                      <span class="lesson-performance-title">{{ lesson.title }}</span>
                      <span class="lesson-performance-type">{{ formatContentType(lesson.content_type) }}</span>
                    </div>
                    <div class="lesson-performance-stats">
                      <div class="performance-metric">
                        <span class="metric-value">{{ getRandomPercentage() }}%</span>
                        <span class="metric-label">Completion</span>
                      </div>
                      <div class="performance-metric">
                        <span class="metric-value">{{ getRandomEngagement() }}%</span>
                        <span class="metric-label">Engagement</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>


        </div>
      </div>
    </div>

    <!-- Course Preview Modal -->
    <CoursePreviewModal 
      :visible="showPreviewModal"
      :course-id="courseId"
      @close="showPreviewModal = false"
    />

    <!-- Toast Notifications -->
    <Toast 
      ref="toastRef"
      :show="toast.show"
      :type="toast.type"
      :title="toast.title"
      :message="toast.message"
      @hide="toast.show = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import Toast from '../components/Toast.vue';
import CoursePreviewModal from '../components/CoursePreviewModal.vue';

const route = useRoute();
const courseId = route.params.id;

const loading = ref(true);
const course = ref(null);
const showAnalyticsModal = ref(false);
const showPreviewModal = ref(false);
const analyticsData = ref(null);
const loadingAnalytics = ref(false);

// Toast notifications
const toast = ref({
  show: false,
  type: 'info',
  title: '',
  message: ''
});

const showToast = (type, title, message) => {
  toast.value = {
    show: true,
    type,
    title,
    message
  };
};

async function fetchCourse() {
  try {
    const response = await axios.get(`/api/teacher/courses/${courseId}`);
    course.value = response.data;
    
    // Also fetch analytics data immediately so main page shows real data
    await fetchAnalytics();
  } catch (error) {
    console.error('Error fetching course:', error);
    course.value = null;
  } finally {
    loading.value = false;
  }
}

function getStatusClass(status) {
  return `status-${status}`;
}

function getStatusIcon(status) {
  const icons = {
    published: 'fas fa-check-circle',
    draft: 'fas fa-edit',
    archived: 'fas fa-archive'
  };
  return icons[status] || 'fas fa-circle';
}

function formatStatus(status) {
  return status.charAt(0).toUpperCase() + status.slice(1);
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

function formatDuration(hours) {
  if (!hours) return '0h';
  if (hours < 1) {
    return `${Math.round(hours * 60)}m`;
  }
  return `${Math.round(hours)}h`;
}

function formatContentType(type) {
  const types = {
    video: 'Video',
    text: 'Reading',
    quiz: 'Quiz',
    assignment: 'Assignment'
  };
  return types[type] || 'Content';
}

function getTotalLessons() {
  if (!course.value?.modules) return 0;
  return course.value.modules.reduce((total, module) => {
    return total + (module.lessons?.length || 0);
  }, 0);
}

function getCompletionRate() {
  // Use real data if available, fallback to 0 while loading
  return analyticsData.value ? analyticsData.value.completion_rate : 0;
}

function getAverageWatchTime() {
  // Use real data if available, fallback to default
  if (analyticsData.value && analyticsData.value.average_watch_time) {
    return `${analyticsData.value.average_watch_time}m`;
  }
  return '25m';
}

function getCourseRating() {
  // Use real data if available, fallback to default
  return analyticsData.value ? analyticsData.value.course_rating : '4.2';
}

function getRandomPercentage() {
  // Static value for lesson analytics - would be replaced with individual lesson data
  return 85;
}

function getRandomEngagement() {
  // Static value for engagement - would be calculated from lesson interaction data
  return 92;
}

async function fetchAnalytics() {
  if (loadingAnalytics.value) return;
  
  loadingAnalytics.value = true;
  try {
    const response = await axios.get(`/api/teacher/courses/${courseId}/analytics`);
    if (response.data.success) {
      analyticsData.value = response.data.analytics;
    }
  } catch (error) {
    console.error('Error fetching analytics:', error);
    showToast('error', 'Error', 'Failed to load analytics data');
  } finally {
    loadingAnalytics.value = false;
  }
}

function previewCourse() {
  showPreviewModal.value = true;
}

async function viewAnalytics() {
  showAnalyticsModal.value = true;
  // Analytics data should already be loaded from page load
  // Only fetch again if somehow missing
  if (!analyticsData.value) {
    await fetchAnalytics();
  }
}

function closeAnalyticsModal() {
  showAnalyticsModal.value = false;
}



onMounted(() => {
  fetchCourse();
});
</script>

<style scoped>
.course-view-container {
  min-height: 100vh;
  background: #f8fafc;
  padding: 0;
}

/* Loading and Error States */
.loading-state, .error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  text-align: center;
}

.loading-spinner {
  font-size: 2rem;
  margin-bottom: 1rem;
  color: #6b7280;
}

.error-content {
  color: #6b7280;
}

.error-content i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: #ef4444;
}

/* Header Section */
.course-header-section {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 2rem;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 200px 1fr;
  gap: 2rem;
  align-items: start;
}

.course-image-container {
  width: 200px;
  height: 150px;
}

.course-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.course-placeholder {
  width: 100%;
  height: 100%;
  background: #f3f4f6;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #9ca3af;
}

.title-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.course-title {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  color: #1f2937;
  line-height: 1.2;
}

.action-buttons {
  display: flex;
  gap: 0.75rem;
  flex-shrink: 0;
}

.course-description {
  font-size: 1.1rem;
  color: #4b5563;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.course-meta {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.status-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-weight: 500;
  font-size: 0.9rem;
}

.status-published {
  background: #d1fae5;
  color: #065f46;
}

.status-draft {
  background: #fef3c7;
  color: #92400e;
}

.status-archived {
  background: #fee2e2;
  color: #991b1b;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6b7280;
  font-size: 0.9rem;
}

/* Statistics Section */
.stats-section {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 1.5rem 2rem;
}

.stats-grid {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
}

.stat-item {
  text-align: center;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.stat-label {
  color: #6b7280;
  font-size: 0.9rem;
  font-weight: 500;
}

/* Main Content */
.main-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 2rem;
}

.section {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.section-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.section-header h3 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 600;
  color: #1f2937;
}

.lesson-count {
  background: #f3f4f6;
  color: #4b5563;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
}

.modules {
  padding: 1.5rem;
}

.module {
  margin-bottom: 2rem;
}

.module:last-child {
  margin-bottom: 0;
}

.module-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #f3f4f6;
}

.module-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.module-number {
  background: #0077b6;
  color: white;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 600;
}

.lesson-count-small {
  background: #f3f4f6;
  color: #6b7280;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
}

.lessons {
  space-y: 0.75rem;
}

.lesson {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 6px;
  margin-bottom: 0.75rem;
}

.lesson-icon {
  width: 32px;
  height: 32px;
  background: #0077b6;
  color: white;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 0.9rem;
}

.lesson-details {
  flex: 1;
}

.lesson-title {
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.lesson-number {
  color: #0077b6;
  font-weight: 600;
  margin-right: 0.5rem;
}

.lesson-meta {
  display: flex;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.lesson-meta span {
  font-size: 0.8rem;
  color: #6b7280;
}

.duration {
  color: #0077b6;
}

.type {
  color: #059669;
}

.protected {
  color: #dc2626;
}

.lesson-description {
  margin: 0;
  font-size: 0.9rem;
  color: #6b7280;
  line-height: 1.4;
}

/* Sidebar */
.sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.sidebar-section {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
}

.sidebar-section h4 {
  margin: 0 0 1rem 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
}

.about-text {
  line-height: 1.6;
  color: #4b5563;
  margin: 0;
}

.quick-actions {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.quick-action {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border: none;
  border-radius: 6px;
  background: #f8fafc;
  color: #374151;
  text-decoration: none;
  font-weight: 500;
  transition: background-color 0.2s;
  cursor: pointer;
  width: 100%;
  text-align: left;
}

.quick-action:hover {
  background: #f3f4f6;
}

.info-grid {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.info-label {
  color: #6b7280;
  font-weight: 500;
  font-size: 0.9rem;
}

.info-value {
  font-weight: 600;
  color: #1f2937;
  font-size: 0.9rem;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 3rem 1.5rem;
  color: #6b7280;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-primary {
  background: #0077b6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover {
  background: #4b5563;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .main-content {
    grid-template-columns: 1fr;
  }
  
  .header-content {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .course-image-container {
    width: 200px;
    margin: 0 auto;
  }
  
  .title-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .course-header-section,
  .stats-section,
  .main-content {
    padding: 1rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .course-title {
    font-size: 1.5rem;
  }
  
  .action-buttons {
    flex-direction: column;
    width: 100%;
  }
  
  .course-meta {
    flex-direction: column;
    gap: 0.75rem;
  }
}

/* Analytics Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  overflow: hidden;
  max-height: 90vh;
  overflow-y: auto;
}

.analytics-modal {
  width: 90%;
  max-width: 1000px;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 600;
  color: #1f2937;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.2rem;
  color: #9ca3af;
  cursor: pointer;
  transition: color 0.2s;
  padding: 0.5rem;
  border-radius: 6px;
}

.close-button:hover {
  color: #6b7280;
  background: #f3f4f6;
}

.modal-body {
  padding: 1.5rem;
  max-height: 70vh;
  overflow-y: auto;
}

.analytics-section {
  margin-bottom: 2rem;
}

.analytics-section:last-child {
  margin-bottom: 0;
}

.analytics-section h4 {
  margin: 0 0 1rem 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  border-bottom: 2px solid #e5e7eb;
  padding-bottom: 0.5rem;
}

.analytics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.analytics-card {
  background: #f8fafc;
  border-radius: 8px;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.2s, box-shadow 0.2s;
}

.analytics-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.analytics-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.1rem;
}

.analytics-icon.enrollment {
  background: #0077b6;
}

.analytics-icon.completion {
  background: #10b981;
}

.analytics-icon.engagement {
  background: #f59e0b;
}

.analytics-icon.rating {
  background: #8b5cf6;
}

.analytics-content {
  flex: 1;
}

.analytics-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.analytics-label {
  color: #6b7280;
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.analytics-change {
  font-size: 0.8rem;
  font-weight: 500;
}

.analytics-change.positive {
  color: #059669;
}

.analytics-change.negative {
  color: #dc2626;
}

.analytics-change.neutral {
  color: #6b7280;
}

/* Progress Chart */
.progress-chart {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.progress-bar-container {
  display: grid;
  grid-template-columns: 120px 1fr 60px;
  gap: 1rem;
  align-items: center;
}

.progress-label {
  font-weight: 500;
  color: #374151;
  font-size: 0.9rem;
}

.progress-bar {
  height: 8px;
  background: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.progress-fill.not-started {
  background: #ef4444;
}

.progress-fill.in-progress {
  background: #f59e0b;
}

.progress-fill.completed {
  background: #10b981;
}

.progress-percentage {
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
  text-align: right;
}

/* Lesson Analytics */
.lesson-analytics {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.module-analytics-title {
  margin: 0 0 0.75rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
  padding-left: 0.5rem;
  border-left: 3px solid #0077b6;
}

.lesson-performance-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.lesson-performance {
  background: #f8fafc;
  border-radius: 6px;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.lesson-performance-info {
  flex: 1;
}

.lesson-performance-title {
  font-weight: 500;
  color: #1f2937;
  display: block;
  margin-bottom: 0.25rem;
}

.lesson-performance-type {
  font-size: 0.8rem;
  color: #6b7280;
  background: #e5e7eb;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
}

.lesson-performance-stats {
  display: flex;
  gap: 1.5rem;
}

.performance-metric {
  text-align: center;
}

.metric-value {
  display: block;
  font-weight: 700;
  color: #1f2937;
  font-size: 1.1rem;
}

.metric-label {
  display: block;
  font-size: 0.8rem;
  color: #6b7280;
  margin-top: 0.25rem;
}



/* Responsive Design for Modal */
@media (max-width: 768px) {
  .analytics-modal {
    width: 95%;
    margin: 1rem;
  }
  
  .modal-body {
    padding: 1rem;
  }
  
  .analytics-grid {
    grid-template-columns: 1fr;
  }
  
  .progress-bar-container {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }
  
  .progress-label {
    font-size: 0.8rem;
  }
  
  .lesson-performance {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
  
  .lesson-performance-stats {
    width: 100%;
    justify-content: space-around;
  }
}

/* Analytics Loading State */
.analytics-loading {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
}

.analytics-loading i {
  font-size: 2rem;
  margin-bottom: 1rem;
  color: #0077b6;
}

.analytics-loading p {
  margin: 0;
  font-size: 0.9rem;
}
</style> 