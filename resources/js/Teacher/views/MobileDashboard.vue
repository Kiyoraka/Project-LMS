<template>
  <div class="mobile-teacher-dashboard">
    <!-- Stats Cards - Mobile Layout -->
    <div class="mobile-stats-grid">
      <div class="mobile-stat-card bg-primary">
        <div class="stat-icon">
          <i class="fas fa-book"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.totalCourses }}</div>
          <div class="stat-label">Total Courses</div>
        </div>
      </div>
      
      <div class="mobile-stat-card bg-success">
        <div class="stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.publishedCourses }}</div>
          <div class="stat-label">Published</div>
        </div>
      </div>
      
      <div class="mobile-stat-card bg-info">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.totalEnrollments }}</div>
          <div class="stat-label">Enrollments</div>
        </div>
      </div>
      
      <div class="mobile-stat-card bg-warning">
        <div class="stat-icon">
          <i class="fas fa-award"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.completedEnrollments }}</div>
          <div class="stat-label">Completed</div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mobile-actions-section">
      <div class="section-header">
        <h3>Quick Actions</h3>
      </div>
      <div class="mobile-action-buttons">
        <router-link :to="{ name: 'teacher.courses.create' }" class="mobile-action-btn primary">
          <i class="fas fa-plus-circle"></i>
          <span>Create Course</span>
        </router-link>
        <router-link :to="{ name: 'teacher.students' }" class="mobile-action-btn secondary">
          <i class="fas fa-users"></i>
          <span>Manage Students</span>
        </router-link>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="mobile-loading">
      <div class="loading-spinner"></div>
      <p>Loading dashboard...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Dashboard data
const loading = ref(true);

const stats = ref({
  totalCourses: 0,
  publishedCourses: 0,
  totalEnrollments: 0,
  completedEnrollments: 0
});

// Fetch dashboard data from API
async function fetchDashboardData() {
  try {
    loading.value = true;
    
    const statsResponse = await axios.get('/api/teacher/dashboard-stats');
    stats.value = statsResponse.data.stats;
    
    loading.value = false;
  } catch (error) {
    console.error('Error fetching mobile teacher dashboard data:', error);
    loading.value = false;
  }
}

// Lifecycle hooks
onMounted(() => {
  fetchDashboardData();
});
</script>

<style scoped>
.mobile-teacher-dashboard {
  padding: 16px;
  background-color: #f8fafc;
  min-height: 100vh;
}

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
}

.bg-primary {
  background: linear-gradient(135deg, #001d3d, #0077b6);
}

.bg-success {
  background: linear-gradient(135deg, #0077b6, #003d5c);
}

.bg-info {
  background: linear-gradient(135deg, #4a90a4, #001d3d);
}

.bg-warning {
  background: linear-gradient(135deg, #0099cc, #004d66);
}

.stat-icon {
  margin-right: 12px;
  font-size: 1.5rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1;
}

.stat-label {
  font-size: 0.8rem;
  opacity: 0.9;
}

.section-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 12px;
}

.mobile-action-buttons {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.mobile-action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 16px;
  border-radius: 12px;
  text-decoration: none;
  color: white;
  font-weight: 500;
}

.mobile-action-btn.primary {
  background: linear-gradient(135deg, #0077b6, #001d3d);
}

.mobile-action-btn.secondary {
  background: linear-gradient(135deg, #6b7280, #4b5563);
}

.mobile-action-btn i {
  font-size: 1.5rem;
  margin-bottom: 8px;
}

.mobile-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px 20px;
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f3f4f6;
  border-top: 3px solid #0077b6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style> 