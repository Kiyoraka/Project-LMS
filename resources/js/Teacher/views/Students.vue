<template>
  <div class="students-container">
    <!-- Header Section -->
    <div class="students-page-header">
      <div class="header-content">
        <h1>Student Management</h1>
        <p>Monitor student progress and track enrollment across all your courses</p>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
      <!-- Total Students Card -->
      <div class="stat-card bg-primary">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ loading ? '...' : totalStudents }}</h3>
          <p class="stat-label">Total Students</p>
        </div>
      </div>
      
      <!-- Active Students Card -->
      <div class="stat-card bg-success">
        <div class="stat-icon">
          <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ loading ? '...' : activeStudents }}</h3>
          <p class="stat-label">Active Students</p>
        </div>
      </div>
      
      <!-- Enrolled Courses Card -->
      <div class="stat-card bg-warning">
        <div class="stat-icon">
          <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ loading ? '...' : totalEnrollments }}</h3>
          <p class="stat-label">Total Enrollments</p>
        </div>
      </div>
      
      <!-- Average Progress Card -->
      <div class="stat-card bg-info">
        <div class="stat-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ loading ? '...' : overallAverageProgress + '%' }}</h3>
          <p class="stat-label">Avg Progress</p>
        </div>
      </div>
    </div>
    
    <!-- Student Controls -->
    <div class="student-controls">
      <div class="student-header">
        <h4>My Students</h4>
        <div class="header-actions">
          <div class="search-box">
            <input 
              type="text" 
              class="search-input" 
              placeholder="Search students..." 
              v-model="filters.search"
            >
            <i class="fas fa-search"></i>
          </div>
          <div v-if="isLoading" class="loading-indicator">
            <i class="fas fa-sync fa-spin"></i>
            <span>Updating...</span>
          </div>
        </div>
      </div>
      
      <!-- Filters -->
      <div class="filters-row">
        <div class="filter-item">
          <label for="course-filter">Filter by Course</label>
          <select id="course-filter" v-model="filters.courseId" class="form-select">
            <option value="">All Courses</option>
            <option v-for="course in courses" :key="course.id" :value="course.id">
              {{ course.title }}
            </option>
          </select>
        </div>
        
        <div class="filter-item">
          <label for="progress-filter">Filter by Progress</label>
          <select id="progress-filter" v-model="filters.progress" class="form-select">
            <option value="">All Progress</option>
            <option value="not_started">Not Started (0%)</option>
            <option value="in_progress">In Progress (1-99%)</option>
            <option value="completed">Completed (100%)</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="alert alert-danger" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>
      {{ error }}
      <button @click="fetchStudents" class="btn btn-sm btn-outline-danger ms-3">
        Try Again
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !error" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">Loading your students...</p>
    </div>

    <!-- Students Table -->
    <div v-if="!loading && !error" class="students-table-container">
      <div class="students-table" v-if="filteredStudents.length > 0">
        <!-- Table Header -->
        <div class="table-header">
          <div class="header-cell student-col">
            <i class="fas fa-user me-2"></i>
            Student
          </div>
          <div class="header-cell courses-col">
            <i class="fas fa-book me-2"></i>
            Enrolled Courses
          </div>
          <div class="header-cell activity-col">
            <i class="fas fa-clock me-2"></i>
            Last Activity
          </div>
          <div class="header-cell progress-col">
            <i class="fas fa-chart-bar me-2"></i>
            Progress
          </div>
          <div class="header-cell actions-col">
            <i class="fas fa-cogs me-2"></i>
            Actions
          </div>
        </div>

        <!-- Student Rows -->
        <div class="student-row" v-for="student in paginatedStudents" :key="student.id">
          <!-- Student Info -->
                     <div class="table-cell student-col">
             <div class="student-info">
               <div class="student-avatar" :style="{ backgroundImage: `url(${getAvatarUrl(student)})` }"></div>
               <div class="student-details">
                 <h5 class="student-name">{{ student.name }}</h5>
                 <p class="student-email">{{ student.email }}</p>
               </div>
             </div>
           </div>

          <!-- Course Count -->
          <div class="table-cell courses-col">
            <div class="course-count-badge">
              <span class="count-value">{{ student.enrolled_courses.length }}</span>
              <span class="count-label">{{ student.enrolled_courses.length === 1 ? 'Course' : 'Courses' }}</span>
            </div>
          </div>

          <!-- Activity -->
          <div class="table-cell activity-col">
            <div class="activity-info">
              <span class="activity-time">{{ formatTimeAgo(student.last_activity) }}</span>
              <span class="activity-status" :class="getActivityStatus(student.last_activity)">
                <i class="fas fa-circle"></i>
                {{ getActivityLabel(student.last_activity) }}
              </span>
            </div>
          </div>

          <!-- Progress -->
          <div class="table-cell progress-col">
            <div class="progress-info">
              <div class="progress-bar-container">
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: `${student.average_progress}%` }"></div>
                </div>
                <span class="progress-percentage">{{ student.average_progress }}%</span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="table-cell actions-col">
            <button class="btn btn-primary btn-action" @click="viewStudent(student)">
              <i class="fas fa-eye me-1"></i>
              View
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-users"></i>
        </div>
        <h3>No Students Found</h3>
        <p v-if="hasFilters">No students match your current filters. Try adjusting your search criteria.</p>
        <p v-else>You don't have any students enrolled in your courses yet.</p>
      </div>
    </div>
    
    <!-- Pagination -->
    <div class="pagination-container" v-if="filteredStudents.length > 0">
      <div class="pagination-info">
        Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage, filteredStudents.length) }} of {{ filteredStudents.length }} students
      </div>
      <div class="pagination-controls">
        <button 
          class="pagination-btn" 
          :disabled="currentPage === 1" 
          @click="goToPage(currentPage - 1)"
        >
          <i class="fas fa-chevron-left"></i>
          Previous
        </button>
        
        <div class="page-numbers">
          <template v-for="page in visiblePages" :key="page">
            <button 
              v-if="page !== '...'"
              class="page-btn"
              :class="{ active: page === currentPage }"
              @click="goToPage(page)"
            >
              {{ page }}
            </button>
            <span v-else class="page-dots">{{ page }}</span>
          </template>
        </div>
        
        <button 
          class="pagination-btn" 
          :disabled="currentPage === totalPages" 
          @click="goToPage(currentPage + 1)"
        >
          Next
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>
    
    <!-- Student Details Modal -->
    <div v-if="showStudentModal" class="modal-overlay" @click="closeStudentModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Student Details</h3>
          <button class="close-btn" @click="closeStudentModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body" v-if="selectedStudent">
          <div class="student-details-content">
            <div class="student-profile">
              <div class="profile-avatar" :style="{ backgroundImage: `url(${getAvatarUrl(selectedStudent)})` }"></div>
              <div class="profile-info">
                <h4>{{ selectedStudent.name }}</h4>
                <p class="email">{{ selectedStudent.email }}</p>
                <p class="last-activity">
                  <i class="fas fa-clock me-2"></i>
                  Last active: {{ formatTimeAgo(selectedStudent.last_activity) }}
                </p>
              </div>
            </div>
            
            <div class="enrollment-details">
              <h5>
                <i class="fas fa-graduation-cap me-2"></i>
                Enrolled Courses ({{ selectedStudent.enrolled_courses.length }})
              </h5>
              <div class="course-progress-list">
                <div v-for="course in selectedStudent.enrolled_courses" :key="course.id" class="course-progress-item">
                  <div class="course-info">
                    <h6>{{ course.title }}</h6>
                    <div class="course-meta">
                      <span class="progress-text">{{ course.progress }}% complete</span>
                      <span class="completion-status" :class="getCompletionStatusClass(course.progress)">
                        {{ getCompletionStatusLabel(course.progress) }}
                      </span>
                    </div>
                    <div class="progress-bar">
                      <div class="progress-fill" :style="{ width: `${course.progress}%` }"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="overall-progress">
              <h5>
                <i class="fas fa-chart-line me-2"></i>
                Overall Progress
              </h5>
              <div class="progress-stats">
                <div class="stat-item">
                  <div class="stat-value">{{ selectedStudent.average_progress }}%</div>
                  <div class="stat-label">Average Progress</div>
                </div>
                <div class="stat-item">
                  <div class="stat-value">{{ selectedStudent.enrolled_count }}</div>
                  <div class="stat-label">Courses Enrolled</div>
                </div>
                <div class="stat-item">
                  <div class="stat-value">{{ getCompletedCoursesCount(selectedStudent.enrolled_courses) }}</div>
                  <div class="stat-label">Completed Courses</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeStudentModal">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

// Filters state
const filters = ref({
  courseId: '',
  progress: '',
  search: ''
});

// Pagination
const currentPage = ref(1);
const perPage = 15;
const totalPages = ref(1);

// Loading and error states
const loading = ref(false);
const error = ref(null);

// Data
const students = ref([]);
const courses = ref([]);
const isLoading = ref(false);

// Modal state
const showStudentModal = ref(false);
const selectedStudent = ref(null);

// Fetch real data from API
async function fetchData() {
  try {
    // Fetch teacher's courses for the filter dropdown
    const coursesResponse = await axios.get('/api/teacher/courses');
    courses.value = coursesResponse.data.data || [];
    
    // Fetch students with current filters
    await fetchStudents();
  } catch (error) {
    console.error('Error fetching data:', error);
  }
}

// Fetch students based on current filters
async function fetchStudents() {
  try {
    isLoading.value = true;
    const params = new URLSearchParams();
    
    // Add filters to query params
    if (filters.value.search) {
      params.append('search', filters.value.search);
    }
    if (filters.value.courseId) {
      params.append('course_id', filters.value.courseId);
    }
    if (filters.value.progress) {
      params.append('progress', filters.value.progress);
    }
    
    // Add pagination
    params.append('page', currentPage.value);
    
    const response = await axios.get(`/api/teacher/students?${params}`);
    const data = response.data;
    
      students.value = data.data || [];
      // Update pagination info if available
      if (data.last_page) {
        totalPages.value = data.last_page;
    }
  } catch (error) {
    console.error('Error fetching students:', error);
    students.value = [];
  } finally {
    isLoading.value = false;
  }
}

// Computed properties for stats
const totalStudents = computed(() => {
  return students.value.length;
});

const activeStudents = computed(() => {
  const now = new Date();
  const sevenDaysAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
  return students.value.filter(student => {
    const lastActivity = new Date(student.last_activity);
    return lastActivity >= sevenDaysAgo;
  }).length;
});

const totalEnrollments = computed(() => {
  return students.value.reduce((total, student) => {
    return total + student.enrolled_courses.length;
  }, 0);
});

const overallAverageProgress = computed(() => {
  if (students.value.length === 0) return 0;
  const totalProgress = students.value.reduce((total, student) => {
    return total + student.average_progress;
  }, 0);
  return Math.round(totalProgress / students.value.length);
});

const filteredStudents = computed(() => {
  // Since filtering is now done on the server side, just return the students array
  return students.value;
});

const hasFilters = computed(() => {
  return filters.value.courseId !== '' || filters.value.progress !== '' || filters.value.search !== '';
});

const paginatedStudents = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  const end = start + perPage;
  return filteredStudents.value.slice(start, end);
});

const visiblePages = computed(() => {
  const totalPagesValue = Math.ceil(filteredStudents.value.length / perPage);
  
  if (totalPagesValue <= 1) {
    return totalPagesValue === 1 ? [1] : [];
  }
  
  const delta = 2;
  const range = [];
  const rangeWithDots = [];
  
  // Always show first page
  rangeWithDots.push(1);
  
  // Calculate the range around current page
  const start = Math.max(2, currentPage.value - delta);
  const end = Math.min(totalPagesValue - 1, currentPage.value + delta);
  
  // Add ellipsis if there's a gap after page 1
  if (start > 2) {
    rangeWithDots.push('...');
  }
  
  // Add pages in range (excluding 1 and totalPagesValue to avoid duplicates)
  for (let i = start; i <= end; i++) {
    if (i !== 1 && i !== totalPagesValue) {
      rangeWithDots.push(i);
    }
  }
  
  // Add ellipsis if there's a gap before last page
  if (end < totalPagesValue - 1) {
    rangeWithDots.push('...');
  }
  
  // Always show last page (if different from first)
  if (totalPagesValue > 1) {
    rangeWithDots.push(totalPagesValue);
  }
  
  return rangeWithDots;
});



// Computed for total pages
const totalPagesComputed = computed(() => {
  return Math.ceil(filteredStudents.value.length / perPage);
});

// Methods
function goToPage(page) {
  if (page >= 1 && page <= totalPagesComputed.value) {
    currentPage.value = page;
  }
}

function formatTimeAgo(dateString) {
  if (!dateString) {
    return 'No activity';
  }
  
  const date = new Date(dateString);
  const now = new Date();
  
  // Check if date is valid
  if (isNaN(date.getTime())) {
    return 'No activity';
  }
  
  const diffInMs = now - date;
  const diffInMinutes = Math.floor(diffInMs / (1000 * 60));
  const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));
  const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));
  
  if (diffInMinutes < 1) {
    return 'Just now';
  } else if (diffInMinutes < 60) {
    return `${diffInMinutes} ${diffInMinutes === 1 ? 'minute' : 'minutes'} ago`;
  } else if (diffInHours < 24) {
    return `${diffInHours} ${diffInHours === 1 ? 'hour' : 'hours'} ago`;
  } else if (diffInDays === 0) {
    return 'Today';
  } else if (diffInDays === 1) {
    return 'Yesterday';
  } else if (diffInDays < 7) {
    return `${diffInDays} ${diffInDays === 1 ? 'day' : 'days'} ago`;
  } else if (diffInDays < 30) {
    const weeks = Math.floor(diffInDays / 7);
    return `${weeks} ${weeks === 1 ? 'week' : 'weeks'} ago`;
  } else if (diffInDays < 365) {
    const months = Math.floor(diffInDays / 30);
    return `${months} ${months === 1 ? 'month' : 'months'} ago`;
  } else {
    return date.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric' 
    });
  }
}

function generateAvatar(name) {
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random`;
}

function getAvatarUrl(student) {
  // If student has avatar URL from API, use it with cache busting
  if (student.avatar) {
    return `${student.avatar}?t=${Date.now()}`;
  }
  // If student has profile_picture field but no avatar URL, construct it
  if (student.profile_picture) {
    return `/public-avatar/${student.id}?t=${Date.now()}`;
  }
  // Fallback to generated avatar
  return generateAvatar(student.name);
}

function getActivityStatus(lastActivity) {
  if (!lastActivity) return 'inactive';
  
  const now = new Date();
  const activityDate = new Date(lastActivity);
  const diffInDays = Math.floor((now - activityDate) / (1000 * 60 * 60 * 24));
  
  if (diffInDays <= 1) return 'active';
  if (diffInDays <= 7) return 'recent';
  return 'inactive';
}

function getActivityLabel(lastActivity) {
  const status = getActivityStatus(lastActivity);
  switch (status) {
    case 'active': return 'Active';
    case 'recent': return 'Recent';
    case 'inactive': return 'Inactive';
    default: return 'Unknown';
  }
}

function getCompletionStatusClass(progress) {
  if (progress >= 100) return 'completed';
  if (progress >= 50) return 'in-progress-high';
  if (progress > 0) return 'in-progress-low';
  return 'not-started';
}

function getCompletionStatusLabel(progress) {
  if (progress >= 100) return 'Completed';
  if (progress > 0) return 'In Progress';
  return 'Not Started';
}

function getCompletedCoursesCount(courses) {
  return courses.filter(course => course.progress >= 100).length;
}

function viewStudent(student) {
  selectedStudent.value = student;
  showStudentModal.value = true;
}

function closeStudentModal() {
  showStudentModal.value = false;
}

// Watch for filter changes to reset pagination
watch([() => filters.value.courseId, () => filters.value.progress, () => filters.value.search], () => {
  currentPage.value = 1;
});

// Lifecycle hooks
// Debounced search function
let searchTimeout;
function debouncedSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    fetchStudents();
  }, 500);
}

// Watchers for filters - refetch data when filters change
watch(() => filters.value.search, debouncedSearch);

watch(() => filters.value.courseId, () => {
  currentPage.value = 1; // Reset to first page when filtering
  fetchStudents();
});

watch(() => filters.value.progress, () => {
  currentPage.value = 1; // Reset to first page when filtering
  fetchStudents();
});

// Auto-refresh data every 5 minutes to keep activity info current
let refreshInterval;

onMounted(() => {
  fetchData();
  
  // Set up auto-refresh for last activity updates
  refreshInterval = setInterval(() => {
    fetchStudents();
  }, 5 * 60 * 1000); // 5 minutes
});

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});
</script>

<style scoped>
.students-container {
  width: 100%;
  padding: 0 20px;
}

/* Header Section */
.students-page-header {
  background: linear-gradient(135deg, #3f66ff, #4d85f5);
  color: white;
  padding: 40px;
  border-radius: 12px;
  margin-bottom: 32px;
  text-align: center;
}

.header-content h1 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 8px;
}

.header-content p {
  font-size: 1.1rem;
  opacity: 0.9;
  margin: 0;
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
  background: linear-gradient(135deg, #3f66ff, #4d85f5);
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
  width: 56px;
  height: 56px;
  border-radius: 50%;
  color: white !important;
  font-size: 1.8rem;
  background-color: rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  margin-right: 16px;
  flex-shrink: 0;
}

.stat-icon i {
  color: white !important;
  font-size: 1.8rem !important;
  font-weight: bold;
  line-height: 1;
  display: block;
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

/* Student Controls */
.student-controls {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  margin-bottom: 24px;
  padding: 20px;
}

.student-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.student-header h4 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 15px;
}

.search-box {
  position: relative;
  width: 300px;
}

.search-input {
  width: 100%;
  padding: 10px 15px 10px 40px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #4d85f5;
  box-shadow: 0 0 0 3px rgba(77, 133, 245, 0.1);
}

.search-box i {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
}

.loading-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  color: #6b7280;
  background: #f8fafc;
  padding: 6px 12px;
  border-radius: 20px;
  border: 1px solid #e5e7eb;
}

.loading-indicator i {
  color: #0077b6;
}

/* Filters */
.filters-row {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  border-top: 1px solid #e0e0e0;
  padding-top: 20px;
}

.filter-item {
  display: flex;
  flex-direction: column;
  min-width: 200px;
}

.filter-item label {
  font-size: 0.9rem;
  margin-bottom: 5px;
  color: #6b7280;
  font-weight: 500;
}

.form-select {
  padding: 10px 12px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s;
}

.form-select:focus {
  outline: none;
  border-color: #4d85f5;
  box-shadow: 0 0 0 3px rgba(77, 133, 245, 0.1);
}

/* Error and Loading States */
.alert {
  padding: 15px 20px;
  border-radius: 8px;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.alert-danger {
  background-color: #fee;
  border: 1px solid #fcc;
  color: #c33;
}

.spinner-border {
  width: 3rem;
  height: 3rem;
}

.visually-hidden {
  position: absolute !important;
  width: 1px !important;
  height: 1px !important;
  padding: 0 !important;
  margin: -1px !important;
  overflow: hidden !important;
  clip: rect(0, 0, 0, 0) !important;
  white-space: nowrap !important;
  border: 0 !important;
}

/* Students Table */
.students-table-container {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  margin-bottom: 24px;
}

.students-table {
  width: 100%;
}

.table-header {
  display: grid;
  grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr;
  gap: 24px;
  background-color: #f8fafc;
  border-bottom: 1px solid #e5e7eb;
  padding: 16px 20px;
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
}

.header-cell {
  display: flex;
  align-items: center;
  font-weight: 600;
}

.header-cell i {
  color: #6b7280;
  margin-right: 8px;
}

.student-row {
  display: grid;
  grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr;
  gap: 24px;
  padding: 20px;
  border-bottom: 1px solid #f3f4f6;
  transition: background-color 0.2s ease;
}

.student-row:hover {
  background-color: #f8fafc;
}

.student-row:last-child {
  border-bottom: none;
}

.table-cell {
  display: flex;
  align-items: center;
}

.student-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.student-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  border: 2px solid #e5e7eb;
  flex-shrink: 0;
}

.student-details {
  flex: 1;
}

.student-name {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: #111827;
}

.student-email {
  font-size: 0.85rem;
  color: #6b7280;
  margin: 0;
}

.course-count-badge {
  display: flex;
  flex-direction: column;
  align-items: center;
  background-color: #f0f9ff;
  color: #0369a1;
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #e0f2fe;
}

.count-value {
  font-size: 1.25rem;
  font-weight: 700;
  line-height: 1;
}

.count-label {
  font-size: 0.75rem;
  margin-top: 2px;
  opacity: 0.8;
}

.activity-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.activity-time {
  font-size: 0.9rem;
  color: #374151;
  font-weight: 500;
}

.activity-status {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.activity-status.active {
  color: #059669;
}

.activity-status.active i {
  color: #10b981;
}

.activity-status.recent {
  color: #d97706;
}

.activity-status.recent i {
  color: #f59e0b;
}

.activity-status.inactive {
  color: #6b7280;
}

.activity-status.inactive i {
  color: #9ca3af;
}

.progress-info {
  width: 100%;
}

.progress-bar-container {
  display: flex;
  align-items: center;
  gap: 8px;
}

.progress-bar {
  flex: 1;
  height: 8px;
  background-color: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3f66ff, #4d85f5);
  border-radius: 4px;
  transition: width 0.3s ease;
}

.progress-percentage {
  font-size: 0.85rem;
  font-weight: 600;
  color: #4d85f5;
  min-width: 45px;
  text-align: right;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.9rem;
  transition: all 0.2s;
  cursor: pointer;
  text-decoration: none;
  border: none;
}

.btn-primary {
  background-color: #4d85f5;
  color: white;
  box-shadow: 0 2px 5px rgba(77, 133, 245, 0.3);
}

.btn-primary:hover {
  background-color: #3b66cc;
  box-shadow: 0 4px 8px rgba(77, 133, 245, 0.4);
}

.btn-secondary {
  background-color: #f9fafb;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background-color: #f3f4f6;
}

.btn-action {
  padding: 8px 16px;
  font-size: 0.85rem;
}

.search-input {
  position: relative;
}

.search-input input {
  width: 100%;
  padding-right: 35px;
}

.search-input i {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

/* Students List - Modern Card Design */
.students-list {
  display: flex;
  flex-direction: column;
  gap: 0;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  margin-bottom: 20px;
  width: 100%;
}

/* Header */
.students-header {
  display: grid;
  grid-template-columns: 2.5fr 2fr 1.8fr 1.5fr 1fr;
  gap: 24px;
  padding: 20px 24px;
  background: linear-gradient(135deg, #f9fafb, #f3f4f6);
  border-bottom: 2px solid #e5e7eb;
  font-weight: 600;
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 0;
}

.students-header > div {
  text-transform: uppercase;
  letter-spacing: 0.5px;
  line-height: 1.4;
  font-size: 0.85rem;
  color: #374151;
  font-weight: 700;
}

.header-student {
  padding-left: 4px;
}

.header-courses {
  text-align: left;
}

.header-activity {
  text-align: left;
}

.header-progress {
  text-align: left;
}

.header-actions {
  text-align: center;
  padding-right: 4px;
}

/* Student Card */
.student-card {
  display: grid;
  grid-template-columns: 2.5fr 2fr 1.8fr 1.5fr 1fr;
  gap: 24px;
  padding: 20px 24px;
  border-bottom: 1px solid #f3f4f6;
  transition: all 0.2s ease;
  align-items: center;
}

.student-card:first-child {
  margin-top: 8px;
}

.student-card:last-child {
  border-bottom: none;
}

.student-card:hover {
  background-color: #f8fafc;
  transform: translateX(2px);
}

/* Student Section */
.student-section {
  display: flex;
  align-items: center;
  gap: 16px;
  min-width: 0;
}

.student-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background-color: #e5e7eb;
  background-size: cover;
  background-position: center;
  flex-shrink: 0;
  border: 2px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.student-details {
  display: flex;
  flex-direction: column;
  min-width: 0;
  flex: 1;
}

.student-name {
  margin: 0 0 2px 0;
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.student-email {
  font-size: 0.85rem;
  color: #6b7280;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Course Count Section */
.courses-count-section {
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

/* Course Count Badge */
.course-count-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
  border: 1px solid #bae6fd;
  border-radius: 20px;
  padding: 6px 12px;
  flex-shrink: 0;
}

.course-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  background: linear-gradient(135deg, #0ea5e9, #0284c7);
  color: white;
  border-radius: 50%;
  font-size: 0.75rem;
  font-weight: 700;
  box-shadow: 0 2px 4px rgba(14, 165, 233, 0.3);
}

.course-text {
  font-size: 0.8rem;
  color: #0369a1;
  font-weight: 600;
}

/* Activity Section */
.activity-section {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.activity-label {
  font-size: 0.75rem;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.activity-value {
  font-size: 0.9rem;
  color: #4b5563;
  font-weight: 500;
}

/* Progress Section */
.progress-section {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.progress-label {
  font-size: 0.75rem;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.progress-content {
  display: flex;
  align-items: center;
  gap: 8px;
}

.progress-bar {
  height: 6px;
  background-color: #e5e7eb;
  border-radius: 3px;
  width: 80px;
  overflow: hidden;
  flex-shrink: 0;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981, #059669);
  border-radius: 3px;
  transition: width 0.3s ease;
}

.progress-percentage {
  font-size: 0.85rem;
  color: #4b5563;
  font-weight: 600;
  min-width: 35px;
}

/* Actions Section */
.actions-section {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0 4px;
}

.view-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: linear-gradient(135deg, #0077b6, #001d3d);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(0, 119, 182, 0.3);
  white-space: nowrap;
}

.view-btn:hover {
  background: linear-gradient(135deg, #001d3d, #1e40af);
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 119, 182, 0.4);
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
}

.header-actions .btn-outline-primary {
  width: 200px;
  margin: 0 auto;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 0.85rem;
}

.btn-primary {
  background-color: #0077b6;
  color: white;
  border: 1px solid #0077b6;
}

.btn-primary:hover {
  background-color: #2563eb;
}

.btn-outline-primary {
  background-color: transparent;
  color: #0077b6;
  border: 1px solid #0077b6;
}

.btn-outline-primary:hover {
  background-color: #0077b6;
  color: white;
}

.btn-outline-secondary {
  background-color: transparent;
  color: #6b7280;
  border: 1px solid #d1d5db;
}

.btn-outline-secondary:hover {
  background-color: #f3f4f6;
}

/* Empty state */
.empty-state {
  background-color: white;
  border-radius: 12px;
  padding: 60px 20px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  margin-bottom: 24px;
}

.empty-icon {
  font-size: 3rem;
  color: #ccc;
  margin-bottom: 20px;
}

.empty-state h3 {
  color: #666;
  font-weight: 500;
  margin-bottom: 15px;
}

.empty-state p {
  color: #888;
  font-size: 0.9rem;
}

/* Pagination */
.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  margin-bottom: 24px;
}

.pagination-info {
  font-size: 0.9rem;
  color: #6b7280;
  font-weight: 500;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.pagination-btn {
  display: flex;
  align-items: center;
  padding: 8px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background-color: white;
  color: #374151;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  gap: 6px;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #f3f4f6;
  border-color: #d1d5db;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  align-items: center;
  gap: 4px;
  margin: 0 12px;
}

.page-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background-color: white;
  color: #374151;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:hover {
  background-color: #f3f4f6;
  border-color: #d1d5db;
}

.page-btn.active {
  background-color: #4d85f5;
  border-color: #4d85f5;
  color: white;
}

.page-dots {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  color: #6b7280;
  font-weight: 500;
  font-size: 0.9rem;
}

/* Modal Styles */
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
  padding: 20px;
}

.modal-content {
  background-color: white;
  border-radius: 12px;
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 24px 0;
  margin-bottom: 20px;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: #111827;
}

.close-btn {
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 8px;
  border-radius: 4px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
}

.close-btn:hover {
  color: #6b7280;
  background-color: #f3f4f6;
}

.modal-body {
  padding: 0 24px;
}

.modal-footer {
  padding: 20px 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.student-details-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.student-profile {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background-color: #f8fafc;
  border-radius: 8px;
}

.profile-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  border: 3px solid white;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.profile-info {
  flex: 1;
}

.profile-info h4 {
  margin: 0 0 8px 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
}

.profile-info .email {
  font-size: 0.9rem;
  color: #6b7280;
  margin: 0 0 4px 0;
}

.profile-info .last-activity {
  font-size: 0.85rem;
  color: #6b7280;
  margin: 0;
  display: flex;
  align-items: center;
}

.enrollment-details h5,
.overall-progress h5 {
  margin: 0 0 16px 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #374151;
  display: flex;
  align-items: center;
}

.course-progress-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
  max-height: 400px;
  overflow-y: auto;
  padding-right: 8px;
}

.course-progress-list::-webkit-scrollbar {
  width: 6px;
}

.course-progress-list::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.course-progress-list::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.course-progress-list::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

.course-progress-item {
  padding: 16px;
  background-color: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.course-info h6 {
  margin: 0 0 8px 0;
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
}

.course-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.progress-text {
  font-size: 0.85rem;
  color: #6b7280;
  font-weight: 500;
}

.completion-status {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.completion-status.completed {
  background-color: #d1fae5;
  color: #065f46;
}

.completion-status.in-progress-high {
  background-color: #fef3c7;
  color: #92400e;
}

.completion-status.in-progress-low {
  background-color: #fef3c7;
  color: #92400e;
}

.completion-status.not-started {
  background-color: #f3f4f6;
  color: #6b7280;
}

.progress-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  padding: 16px;
  background-color: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.stat-item {
  text-align: center;
}

.stat-item .stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #4d85f5;
  margin-bottom: 4px;
  display: block;
}

.stat-item .stat-label {
  font-size: 0.8rem;
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .students-container {
    max-width: 100%;
    padding: 0 16px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .students-container {
    padding: 0 15px;
  }
  
  .students-page-header {
    padding: 24px;
  }
  
  .header-content h1 {
    font-size: 2rem;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  
  .student-header {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }
  
  .header-actions {
    flex-direction: column;
    gap: 10px;
  }
  
  .search-box {
    width: 100%;
  }
  
  .filters-row {
    flex-direction: column;
    gap: 15px;
  }
  
  .filter-item {
    min-width: 100%;
  }
  
  .table-header {
    display: none;
  }
  
     .student-row {
     grid-template-columns: 1fr;
     gap: 15px;
     border: 1px solid #e5e7eb;
     border-radius: 8px;
     margin-bottom: 15px;
     padding: 16px;
   }
  
  .table-cell {
    display: flex;
    flex-direction: column;
    align-items: stretch;
  }
  
  .student-info {
    flex-direction: row;
    gap: 12px;
  }
  
  .course-count-badge {
    align-self: flex-start;
  }
  
  .pagination-container {
    flex-direction: column;
    gap: 15px;
  }
  
  .pagination-controls {
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .page-numbers {
    margin: 0 8px;
  }
  
  .modal-content {
    margin: 10px;
    max-width: calc(100vw - 20px);
  }
  
  .progress-stats {
    grid-template-columns: 1fr;
    gap: 12px;
  }
}

@media (max-width: 480px) {
  .stat-card {
    padding: 15px;
  }
  
  .stat-icon {
    width: 48px;
    height: 48px;
    font-size: 1.5rem;
  }
  
  .stat-value {
    font-size: 1.5rem;
  }
  
  .student-controls {
    padding: 15px;
  }
  
  .student-row {
    padding: 15px;
  }
  
  .pagination-btn {
    padding: 6px 12px;
    font-size: 0.8rem;
  }
  
  .page-btn {
    width: 36px;
    height: 36px;
  }
}
</style>