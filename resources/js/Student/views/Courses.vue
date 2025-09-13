<template>
  <div class="courses-container">
    <!-- Header Section -->
    <div class="courses-header">
      <div class="header-content">
        <h1>My Courses</h1>
        <p>Track your learning journey and manage your enrolled courses</p>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
      <!-- Active Courses Card -->
      <div class="stat-card bg-primary">
        <div class="stat-icon">
          <i class="bi bi-book"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ loading ? '...' : activeCourses }}</h3>
          <p class="stat-label">Active Courses</p>
        </div>
      </div>
      
      <!-- Completed Courses Card -->
      <div class="stat-card bg-success">
        <div class="stat-icon">
          <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ loading ? '...' : completedCourses }}</h3>
          <p class="stat-label">Completed Courses</p>
        </div>
      </div>
      
      <!-- Average Progress Card -->
      <div class="stat-card bg-warning">
        <div class="stat-icon">
          <i class="bi bi-graph-up"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ loading ? '...' : averageProgress + '%' }}</h3>
          <p class="stat-label">Average Progress</p>
        </div>
      </div>
      
      <!-- Hours Spent Card -->
      <div class="stat-card bg-info">
        <div class="stat-icon">
          <i class="bi bi-clock"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ loading ? '...' : totalHoursSpent }}</h3>
          <p class="stat-label">Hours Spent</p>
        </div>
      </div>
    </div>
    
    <!-- Course Controls -->
    <div class="course-controls">
      <div class="course-header">
        <h4>My Courses</h4>
        <div class="search-box">
          <input 
            type="text" 
            class="search-input" 
            placeholder="Search courses..." 
            v-model="searchQuery"
            @input="filterCourses"
          >
          <i class="bi bi-search"></i>
        </div>
      </div>
      
      <!-- Tabs for Active/Completed Courses -->
      <ul class="nav-tabs">
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: activeTab === 'active' }"
            href="#" 
            @click.prevent="activeTab = 'active'"
          >
            <i class="bi bi-play-circle me-1"></i>
            Active Courses
          </a>
        </li>
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: activeTab === 'completed' }"
            href="#" 
            @click.prevent="activeTab = 'completed'"
          >
            <i class="bi bi-check-circle me-1"></i>
            Completed Courses
          </a>
        </li>
      </ul>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="alert alert-danger" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i>
      {{ error }}
      <button @click="fetchEnrollments" class="btn btn-sm btn-outline-danger ms-3">
        Try Again
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !error" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">Loading your courses...</p>
    </div>

    <!-- Active Courses -->
    <div v-if="activeTab === 'active' && !loading && !error">
      <div class="courses-grid" v-if="filteredActiveCourses.length > 0">
        <div 
          v-for="enrollment in filteredActiveCourses" 
          :key="enrollment.id"
          class="course-card"
        >
          <div class="course-header">
            <img 
              :src="enrollment.course.thumbnail || '/assets/images/course-placeholder.jpg'" 
              class="course-thumbnail" 
              alt="Course"
            >
            <span class="badge level-badge">{{ enrollment.course.level }}</span>
          </div>
          <div class="course-body">
            <h5 class="course-title">{{ enrollment.course.title }}</h5>
            <p class="course-description">{{ enrollment.course.short_description || 'No description available' }}</p>
            
            <div class="course-meta">
              <div class="meta-item">
                <i class="bi bi-clock me-1"></i>
                <span>{{ enrollment.course.duration_hours || 0 }} hours</span>
              </div>
              <div class="meta-item">
                <i class="bi bi-calendar me-1"></i>
                <span>Enrolled: {{ formatDate(enrollment.enrolled_at) }}</span>
              </div>
            </div>
            
            <div class="progress-container">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="progress-label">Progress</span>
                <span class="progress-value">{{ enrollment.progress_percentage }}%</span>
              </div>
              <div class="progress-bar">
                <div 
                  class="progress-fill" 
                  :style="{ width: enrollment.progress_percentage + '%' }"
                ></div>
              </div>
            </div>
          </div>
          <div class="course-footer">
            <router-link 
              :to="`/student/learning/${enrollment.course.id}`" 
              class="btn btn-primary btn-continue"
            >
              Continue Learning
            </router-link>
          </div>
        </div>
      </div>
      <div v-else class="empty-state">
        <div class="empty-icon">
          <i class="bi bi-journal-x"></i>
        </div>
        <h5>
          {{ searchQuery ? 'No active courses match your search.' : 'You have no active courses.' }}
        </h5>
        <router-link to="/student/browse" class="btn btn-primary mt-3">
          Browse Courses
        </router-link>
      </div>
    </div>

    <!-- Completed Courses -->
    <div v-if="activeTab === 'completed' && !loading && !error">
      <div class="courses-grid" v-if="filteredCompletedCourses.length > 0">
        <div 
          v-for="enrollment in filteredCompletedCourses" 
          :key="enrollment.id"
          class="course-card"
        >
          <div class="completed-badge">
            <i class="bi bi-check-circle-fill"></i>
          </div>
          <div class="course-header">
            <img 
              :src="enrollment.course.thumbnail || '/assets/images/course-placeholder.jpg'" 
              class="course-thumbnail" 
              alt="Course"
            >
            <span class="badge level-badge">{{ enrollment.course.level }}</span>
          </div>
          <div class="course-body">
            <h5 class="course-title">{{ enrollment.course.title }}</h5>
            <p class="course-description">{{ enrollment.course.short_description || 'No description available' }}</p>
            
            <div class="course-meta">
              <div class="meta-item">
                <i class="bi bi-clock me-1"></i>
                <span>{{ enrollment.course.duration_hours || 0 }} hours</span>
              </div>
              <div class="meta-item">
                <i class="bi bi-calendar-check me-1"></i>
                <span>Completed: {{ formatDate(enrollment.completed_at) }}</span>
              </div>
            </div>
            
            <div class="achievement-badge">
              <i class="bi bi-trophy"></i>
              <span>Course Completed</span>
            </div>
          </div>
          <div class="course-footer d-flex">
            <router-link 
              :to="`/student/learning/${enrollment.course.id}`" 
              class="btn btn-outline-primary me-2 flex-grow-1"
            >
              Review Course
            </router-link>
            <a 
              :href="`/student/certificates/${enrollment.id}`" 
              class="btn btn-certificate"
              target="_blank"
              title="View Certificate"
            >
              <i class="bi bi-award"></i>
            </a>
          </div>
        </div>
      </div>
      <div v-else class="empty-state">
        <div class="empty-icon">
          <i class="bi bi-journal-check"></i>
        </div>
        <h5>
          {{ searchQuery ? 'No completed courses match your search.' : 'You have not completed any courses yet.' }}
        </h5>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'StudentCourses',
  data() {
    return {
      activeTab: 'active',
      searchQuery: '',
      // Real data from API
      enrollments: [],
      filteredActiveCourses: [],
      filteredCompletedCourses: [],
      loading: true,
      error: null
    }
  },
  computed: {
    // Calculate total progress across all courses
    averageProgress() {
      if (this.enrollments.length === 0) return 0;
      
      const totalProgress = this.enrollments.reduce((sum, enrollment) => {
        return sum + enrollment.progress_percentage;
      }, 0);
      
      return Math.round(totalProgress / this.enrollments.length);
    },
    
    // Calculate total hours spent based on progress
    totalHoursSpent() {
      let hoursSpent = 0;
      
      this.enrollments.forEach(enrollment => {
        const totalHours = enrollment.course.duration_hours || 0;
        const progressPercentage = enrollment.progress_percentage / 100;
        hoursSpent += totalHours * progressPercentage;
      });
      
      return Math.round(hoursSpent);
    },

    // Calculate active courses count
    activeCourses() {
      return this.enrollments.filter(enrollment => enrollment.status === 'active').length;
    },

    // Calculate completed courses count  
    completedCourses() {
      return this.enrollments.filter(enrollment => enrollment.status === 'completed').length;
    }
  },
  async mounted() {
    await this.fetchEnrollments();
  },
  methods: {
    async fetchEnrollments() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get('/api/student/enrollments');
        
        if (response.data) {
          this.enrollments = response.data;
          this.filterCourses();
        }
      } catch (error) {
        if (error.response?.status === 401) {
          this.error = 'Authentication required. Please log in again.';
        } else {
          this.error = 'Failed to load your courses. Please try again later.';
        }
      } finally {
        this.loading = false;
      }
    },

    filterCourses() {
      const query = this.searchQuery.toLowerCase();
      
      // Filter active courses
      this.filteredActiveCourses = this.enrollments
        .filter(enrollment => 
          enrollment.status === 'active' && 
          (query === '' || 
           enrollment.course.title.toLowerCase().includes(query) ||
           enrollment.course.short_description?.toLowerCase().includes(query))
        );
      
      // Filter completed courses
      this.filteredCompletedCourses = this.enrollments
        .filter(enrollment => 
          enrollment.status === 'completed' && 
          (query === '' || 
           enrollment.course.title.toLowerCase().includes(query) ||
           enrollment.course.short_description?.toLowerCase().includes(query))
        );
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    }
  },

  watch: {
    searchQuery() {
      this.filterCourses();
    }
  }
}
</script>

<style scoped>
.courses-container {
  width: 100%;
  padding: 0 20px;
}

/* Header Section */
.courses-header {
  background: linear-gradient(135deg, #001d3d, #0077b6);
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

/* Course Controls */
.course-controls {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  margin-bottom: 24px;
  padding: 20px;
}

.course-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.course-header h4 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
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
  border-color: #0077b6;
  box-shadow: 0 0 0 3px rgba(0, 119, 182, 0.1);
}

.search-box i {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
}

/* Tabs Styling */
.nav-tabs {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 0;
  border-bottom: 1px solid #e0e0e0;
}

.nav-item {
  margin-right: 5px;
}

.nav-link {
  display: inline-block;
  padding: 10px 20px;
  color: #666;
  text-decoration: none;
  font-weight: 500;
  border-radius: 8px 8px 0 0;
  transition: all 0.2s;
  position: relative;
}

.nav-link:hover {
  color: #0077b6;
  background-color: rgba(0, 119, 182, 0.05);
}

.nav-link.active {
  color: #0077b6;
  border-bottom: 3px solid #0077b6;
  font-weight: 600;
}

.nav-link i {
  margin-right: 5px;
}

/* Courses Grid */
.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.course-card {
  background-color: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
  position: relative;
}

.course-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.course-header {
  position: relative;
}

.course-thumbnail {
  width: 100%;
  height: 160px;
  object-fit: cover;
  display: block;
}

.level-badge {
  position: absolute;
  top: 15px;
  left: 15px;
  background-color: #0077b6;
  color: white;
  font-size: 0.75rem;
  font-weight: 500;
  padding: 5px 10px;
  border-radius: 20px;
}

.completed-badge {
  position: absolute;
  top: 15px;
  right: 15px;
  background-color: #40d787;
  color: white;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  z-index: 1;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.course-body {
  padding: 20px;
  flex-grow: 1;
}

.course-title {
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0 0 10px 0;
  color: #333;
}

.course-description {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 15px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  height: 40px;
}

.course-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 15px;
}

.meta-item {
  font-size: 0.85rem;
  color: #666;
  display: flex;
  align-items: center;
}

.meta-item i {
  color: #0077b6;
  margin-right: 5px;
}

.progress-container {
  margin-top: auto;
}

.progress-label {
  font-size: 0.85rem;
  color: #666;
  font-weight: 500;
}

.progress-value {
  font-size: 0.85rem;
  color: #0077b6;
  font-weight: 600;
}

.progress-bar {
  height: 6px;
  background-color: #e0e0e0;
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background-color: #0077b6;
  border-radius: 3px;
}

.achievement-badge {
  display: inline-flex;
  align-items: center;
  background-color: #fff8e1;
  color: #f59f0b;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  margin-top: 10px;
}

.achievement-badge i {
  margin-right: 5px;
  font-size: 1rem;
}

.course-footer {
  padding: 15px 20px;
  border-top: 1px solid #f0f0f0;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.95rem;
  transition: all 0.2s;
  cursor: pointer;
  text-decoration: none;
}

.btn-primary {
  background-color: #0077b6;
  color: white;
  border: none;
  box-shadow: 0 2px 5px rgba(0, 119, 182, 0.3);
}

.btn-primary:hover {
  background-color: #001d3d;
  box-shadow: 0 4px 8px rgba(0, 119, 182, 0.4);
}

.btn-outline-primary {
  border: 1px solid #0077b6;
  color: #0077b6;
  background-color: transparent;
}

.btn-outline-primary:hover {
  background-color: #0077b6;
  color: white;
}

.btn-continue {
  width: 100%;
}

.btn-certificate {
  width: 42px;
  height: 42px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #fff8e1;
  color: #f59f0b;
  border: 1px solid #ffebc4;
}

.btn-certificate:hover {
  background-color: #f59f0b;
  color: white;
}

.btn-certificate i {
  font-size: 1.2rem;
}

/* Empty State */
.empty-state {
  background-color: white;
  border-radius: 12px;
  padding: 60px 20px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.empty-icon {
  font-size: 3rem;
  color: #ccc;
  margin-bottom: 20px;
}

.empty-state h5 {
  color: #666;
  font-weight: 500;
  margin-bottom: 15px;
}

/* Loading and Error States */
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

/* Responsive Design Improvements */
@media (max-width: 1200px) {
  .courses-container {
    max-width: 100%;
    padding: 0 16px;
  }
  
  .courses-header {
    padding: 24px;
  }

  .header-content h1 {
    font-size: 2rem;
  }

  .header-content p {
    font-size: 1rem;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  
  .courses-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
  }
}

@media (max-width: 768px) {
  .courses-container {
    padding: 0 15px;
  }
  
  .course-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .search-box {
    width: 100%;
    max-width: none;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .stat-card {
    padding: 16px;
  }
  
  .stat-icon {
    width: 48px;
    height: 48px;
    font-size: 1.5rem;
    margin-right: 12px;
  }
  
  .stat-value {
    font-size: 1.5rem;
  }
  
  .courses-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .course-controls {
    padding: 16px;
  }
  
  .nav-tabs {
    flex-wrap: wrap;
  }
  
  .nav-link {
    padding: 8px 16px;
    font-size: 0.9rem;
  }
  
  .course-body {
    padding: 16px;
  }
  
  .course-footer {
    padding: 12px 16px;
  }
  
  .course-footer.d-flex {
    gap: 8px;
  }
  
  .btn {
    padding: 8px 16px;
    font-size: 0.9rem;
  }
}

@media (max-width: 480px) {
  .courses-container {
    padding: 0 12px;
  }
  
  .course-controls {
    padding: 12px;
  }
  
  .course-header h4 {
    font-size: 1.3rem;
  }
  
  .search-input {
    padding: 8px 12px 8px 36px;
    font-size: 0.9rem;
  }
  
  .search-box i {
    left: 12px;
  }
  
  .nav-link {
    padding: 6px 12px;
    font-size: 0.85rem;
  }
  
  .course-title {
    font-size: 1.1rem;
  }
  
  .course-description {
    font-size: 0.85rem;
  }
  
  .meta-item {
    font-size: 0.8rem;
  }
  
  .course-thumbnail {
    height: 140px;
  }
  
  .stat-card {
    padding: 12px;
  }
  
  .stat-icon {
    width: 40px;
    height: 40px;
    font-size: 1.2rem;
    margin-right: 10px;
  }
  
  .stat-value {
    font-size: 1.3rem;
  }
  
  .stat-label {
    font-size: 0.8rem;
  }
}

/* Fix for potential overflow issues */
.courses-container {
  overflow-x: hidden;
}

.course-card {
  min-height: 350px;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.course-title {
  word-wrap: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
}

.course-description {
  word-wrap: break-word;
  overflow-wrap: break-word;
}

/* Utility classes */
.d-flex {
  display: flex;
}

.justify-content-between {
  justify-content: space-between;
}

.align-items-center {
  align-items: center;
}

.mb-1 {
  margin-bottom: 0.25rem;
}

.me-1 {
  margin-right: 0.25rem;
}

.me-2 {
  margin-right: 0.5rem;
}

.ms-3 {
  margin-left: 1rem;
}

.mt-3 {
  margin-top: 1rem;
}

.py-5 {
  padding-top: 3rem;
  padding-bottom: 3rem;
}

.text-center {
  text-align: center;
}

.text-muted {
  color: #6c757d;
}

.text-primary {
  color: #0077b6;
}

.flex-grow-1 {
  flex-grow: 1;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.btn-outline-danger {
  color: #dc3545;
  border-color: #dc3545;
  background-color: transparent;
}

.btn-outline-danger:hover {
  color: white;
  background-color: #dc3545;
}
</style>