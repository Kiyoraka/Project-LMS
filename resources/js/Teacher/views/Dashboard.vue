<template>
  <div class="dashboard-container">
    <!-- Stats Cards -->
    <div class="stats-grid">
      <!-- Total Courses Card -->
      <div class="stat-card bg-primary">
        <div class="stat-icon">
          <i class="fas fa-book"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ stats.totalCourses }}</h3>
          <p class="stat-label">Total Courses</p>
        </div>
      </div>
      
      <!-- Published Courses Card -->
      <div class="stat-card bg-success">
        <div class="stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ stats.publishedCourses }}</h3>
          <p class="stat-label">Published Courses</p>
        </div>
      </div>
      
      <!-- Total Enrollments Card -->
      <div class="stat-card bg-info">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ stats.totalEnrollments }}</h3>
          <p class="stat-label">Total Enrollments</p>
        </div>
      </div>
      
      <!-- Completed Enrollments Card -->
      <div class="stat-card bg-warning">
        <div class="stat-icon">
          <i class="fas fa-award"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ stats.completedEnrollments }}</h3>
          <p class="stat-label">Completed Enrollments</p>
        </div>
      </div>
    </div>
    
    <div class="dashboard-content">
      <div class="main-content">
        <!-- Enrollment Chart -->
        <div class="card chart-card">
          <div class="card-header">
            <h5>Enrollment Trends</h5>
            <div class="chart-period-selector">
              <select v-model="chartPeriod" @change="updateChart">
                <option value="3">Last 3 Months</option>
                <option value="6">Last 6 Months</option>
                <option value="12">Last 12 Months</option>
              </select>
            </div>
          </div>
          <div class="card-body">
            <div v-if="chartLoading" class="chart-loading">
              <div class="loading-spinner"></div>
              <p>Loading enrollment data...</p>
            </div>
            <canvas v-else ref="enrollmentChartRef" height="250"></canvas>
          </div>
        </div>
        
        <!-- Recent Courses -->
        <div class="card courses-card">
          <div class="card-header">
            <h5>Recent Courses</h5>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Course</th>
                  <th>Status</th>
                  <th>Students</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="course in recentCourses" :key="course.id" class="course-row">
                  <td class="course-info">
                    <div class="course-thumbnail" :style="{ backgroundImage: course.thumbnail ? `url(/storage/${course.thumbnail})` : 'none' }">
                      <i v-if="!course.thumbnail" class="fas fa-book"></i>
                    </div>
                    <div class="course-title">{{ course.title }}</div>
                  </td>
                  <td>
                    <span class="badge" :class="getStatusClass(course.status)">
                      {{ course.status.charAt(0).toUpperCase() + course.status.slice(1) }}
                    </span>
                  </td>
                  <td>{{ course.enrollments_count }}</td>
                  <td>{{ formatDate(course.created_at) }}</td>
                  <td>
                    <router-link :to="{ name: 'teacher.courses.view', params: { id: course.id }}" class="btn btn-primary btn-sm">
                      View
                    </router-link>
                  </td>
                </tr>
                <tr v-if="recentCourses.length === 0">
                  <td colspan="5" class="empty-data">
                    <p>No courses created yet</p>
                    <router-link :to="{ name: 'teacher.courses.create' }" class="btn btn-primary btn-sm">
                      Create Your First Course
                    </router-link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="card-footer" v-if="recentCourses.length > 0">
            <router-link :to="{ name: 'teacher.courses' }" class="btn btn-outline-primary btn-sm">
              View All Courses
            </router-link>
          </div>
        </div>
      </div>
      
      <div class="side-content">
        <!-- Recent Enrollments -->
        <div class="card enrollments-card">
          <div class="card-header">
            <h5><i class="fas fa-graduation-cap me-2"></i>Recent Enrollments</h5>
            <router-link :to="{ name: 'teacher.students' }" class="btn btn-outline-primary btn-sm">
              <i class="fas fa-arrow-right me-1"></i>View All
            </router-link>
          </div>
          <div class="card-body">
            <div class="enrollment-list">
              <div v-for="enrollment in recentEnrollments" :key="enrollment.id" class="enrollment-item">
                <div class="enrollment-card">
                  <!-- Student Info Section -->
                  <div class="student-section">
                    <div class="student-avatar">
                      <img v-if="enrollment.student.avatar" 
                           :src="`/storage/${enrollment.student.avatar}`" 
                           :alt="enrollment.student.name"
                           @error="handleImageError($event, enrollment.student.name)" />
                      <div v-else class="avatar-fallback">
                        {{ getInitials(enrollment.student.name) }}
                      </div>
                    </div>
                    <div class="student-details">
                      <h6 class="student-name">{{ enrollment.student.name }}</h6>
                      <div class="enrollment-time">
                        <i class="fas fa-clock me-1"></i>
                        {{ formatTimeAgo(enrollment.enrolled_at) }}
                      </div>
                    </div>
                  </div>
                  
                  <!-- Course Info Section -->
                  <div class="course-section">
                    <div class="course-title">{{ enrollment.course.title }}</div>
                    <div class="progress-section">
                      <div class="progress-bar-modern">
                        <div class="progress-fill-modern" :style="{ width: `${enrollment.progress_percentage}%` }"></div>
                      </div>
                      <div class="progress-info">
                        <span class="progress-percentage">{{ enrollment.progress_percentage }}%</span>
                        <span class="progress-status" :class="getProgressStatusClass(enrollment.progress_percentage)">
                          {{ getProgressStatusLabel(enrollment.progress_percentage) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div v-if="recentEnrollments.length === 0" class="empty-enrollments">
                <div class="empty-icon">
                  <i class="fas fa-user-graduate"></i>
                </div>
                <p>No recent enrollments</p>
                <small>New student enrollments will appear here</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import Chart from 'chart.js/auto';
import axios from 'axios';

// References for chart canvas
const enrollmentChartRef = ref(null);
let enrollmentChart = null;

// Dashboard data
const chartPeriod = ref(12);
const chartLoading = ref(false);
const stats = ref({
  totalCourses: 0,
  publishedCourses: 0,
  draftCourses: 0,
  totalEnrollments: 0,
  activeEnrollments: 0,
  completedEnrollments: 0
});

const recentCourses = ref([]);
const recentEnrollments = ref([]);
const chartData = ref({
  labels: [],
  data: []
});

// Fetch dashboard data from API
async function fetchDashboardData() {
  try {
    chartLoading.value = true;
    
    // Fetch dashboard stats
    const statsResponse = await axios.get('/api/teacher/dashboard-stats');
    stats.value = statsResponse.data.stats;
    
    // Fetch recent courses
    const coursesResponse = await axios.get('/api/teacher/recent-courses');
    recentCourses.value = coursesResponse.data;
    
    // Fetch recent enrollments
    const enrollmentsResponse = await axios.get('/api/teacher/recent-enrollments');
    recentEnrollments.value = enrollmentsResponse.data;
    
    // Fetch chart data
    const chartResponse = await axios.get(`/api/teacher/enrollment-chart?period=${chartPeriod.value}`);
    chartData.value = {
      labels: chartResponse.data.labels || [],
      data: chartResponse.data.data || []
    };
    
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    // Fallback to empty data
    stats.value = {
      totalCourses: 0,
      publishedCourses: 0,
      draftCourses: 0,
      totalEnrollments: 0,
      activeEnrollments: 0,
      completedEnrollments: 0
    };
    recentCourses.value = [];
    recentEnrollments.value = [];
    chartData.value = { labels: [], data: [] };
  } finally {
    chartLoading.value = false;
  }
}

// Update chart when period changes
async function updateChart() {
  await fetchDashboardData();
  updateChartData();
}

// Update chart data without recreating the chart
function updateChartData() {
  if (enrollmentChart && chartData.value) {
    enrollmentChart.data.labels = chartData.value.labels;
    enrollmentChart.data.datasets[0].data = chartData.value.data;
    enrollmentChart.update();
  }
}

// Initialize enrollment chart
function initializeChart() {
  nextTick(() => {
    if (!enrollmentChartRef.value) return;
    
    const ctx = enrollmentChartRef.value.getContext('2d');
    if (!ctx) return;
    
    enrollmentChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: chartData.value.labels,
        datasets: [{
          label: 'New Enrollments',
          data: chartData.value.data,
          backgroundColor: 'rgba(0, 119, 182, 0.1)',
          borderColor: '#0077b6',
          borderWidth: 2,
          tension: 0.4,
          fill: true,
          pointBackgroundColor: '#0077b6',
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            mode: 'index',
            intersect: false,
            callbacks: {
              title: function(tooltipItems) {
                return tooltipItems[0].label;
              },
              label: function(context) {
                return 'Enrollments: ' + context.raw;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        }
      }
    });
  });
}

// Helper functions
function getStatusClass(status) {
  switch (status) {
    case 'published': return 'badge-success';
    case 'draft': return 'badge-secondary';
    case 'archived': return 'badge-danger';
    default: return 'badge-secondary';
  }
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatTimeAgo(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffInMs = now - date;
  const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));
  
  if (diffInDays === 0) {
    return 'Today';
  } else if (diffInDays === 1) {
    return 'Yesterday';
  } else if (diffInDays < 7) {
    return `${diffInDays} days ago`;
  } else if (diffInDays < 30) {
    return `${Math.floor(diffInDays / 7)} weeks ago`;
  } else {
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
  }
}

function generateAvatar(name) {
  if (!name) return '';
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=4d85f5&color=ffffff&size=40&rounded=true&bold=true`;
}

function getProgressStatusClass(progress) {
  if (progress >= 100) return 'completed';
  if (progress > 0) return 'in-progress';
  return 'not-started';
}

function getProgressStatusLabel(progress) {
  if (progress >= 100) return 'Completed';
  if (progress > 0) return 'In Progress';
  return 'Not Started';
}

function handleImageError(event, name) {
  event.target.src = generateAvatar(name);
}

function getInitials(name) {
  if (!name) return '';
  const names = name.split(' ');
  if (names.length === 1) {
    return names[0].charAt(0).toUpperCase();
  } else {
    return names[0].charAt(0).toUpperCase() + names[1].charAt(0).toUpperCase();
  }
}

// Lifecycle hooks
onMounted(async () => {
  await fetchDashboardData();
  initializeChart();
});
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
  margin-bottom: 20px;
}

.stat-card {
  display: flex;
  align-items: center;
  padding: 20px;
  border-radius: 10px;
  color: white;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
  margin-right: 15px;
  font-size: 2rem;
  width: 40px;
  text-align: center;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  line-height: 1;
}

.stat-label {
  font-size: 0.9rem;
  margin-top: 5px;
  opacity: 0.9;
}

/* Dashboard Layout */
.dashboard-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
}

.card {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  margin-bottom: 20px;
  overflow: hidden;
}

.card-header {
  padding: 15px 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h5 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #374151;
}

.card-body {
  padding: 20px;
}

.card-footer {
  padding: 12px 20px;
  border-top: 1px solid #e5e7eb;
  text-align: center;
}

/* Charts */
.chart-period-selector select {
  padding: 6px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background-color: white;
  font-size: 0.9rem;
  outline: none;
}

.chart-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 250px;
  color: #6b7280;
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f3f4f6;
  border-top: 3px solid #0077b6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 12px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.chart-loading p {
  margin: 0;
  font-size: 0.9rem;
}

/* Data Tables */
.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th, .data-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.data-table th {
  font-weight: 600;
  color: #6b7280;
  font-size: 0.9rem;
  background-color: #f9fafb;
}

.course-info {
  display: flex;
  align-items: center;
}

.course-thumbnail {
  width: 40px;
  height: 40px;
  background-color: #e5e7eb;
  border-radius: 4px;
  margin-right: 12px;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
}

.course-title {
  font-weight: 500;
}

.badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.badge-success {
  background-color: #d1fae5;
  color: #059669;
}

.badge-secondary {
  background-color: #e5e7eb;
  color: #4b5563;
}

.badge-danger {
  background-color: #fee2e2;
  color: #dc2626;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
}

.btn-sm {
  padding: 4px 10px;
  font-size: 0.875rem;
}

.btn-block {
  display: flex;
  width: 100%;
  margin-bottom: 10px;
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

/* Enrollment List */
.enrollment-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.enrollment-item {
  margin-bottom: 12px;
}

.enrollment-item:last-child {
  margin-bottom: 0;
}

.enrollment-card {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 10px 15px;
  border-radius: 8px;
  background-color: #f9fafb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.student-section {
  display: flex;
  align-items: center;
  flex: 1;
}

.student-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  overflow: hidden;
  margin-right: 10px;
  flex-shrink: 0;
}

.student-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-fallback {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #4d85f5, #3b66cc);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
}

.student-details {
  display: flex;
  flex-direction: column;
}

.student-name {
  margin: 0 0 2px 0;
  font-size: 0.95rem;
  font-weight: 600;
  color: #374151;
}

.enrollment-time {
  font-size: 0.8rem;
  color: #6b7280;
  display: flex;
  align-items: center;
}

.enrollment-time i {
  margin-right: 5px;
  font-size: 0.8rem;
}

.course-section {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.course-title {
  font-size: 0.9rem;
  font-weight: 500;
  color: #4b5563;
  margin-bottom: 5px;
}

.progress-section {
  display: flex;
  align-items: center;
  gap: 10px;
}

.progress-bar-modern {
  width: 100%;
  height: 4px;
  background-color: #e5e7eb;
  border-radius: 2px;
  position: relative;
  flex-grow: 1;
}

.progress-fill-modern {
  height: 100%;
  background-color: #0077b6;
  border-radius: 2px;
  position: absolute;
  top: 0;
  left: 0;
}

.progress-info {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.75rem;
  color: #6b7280;
}

.progress-percentage {
  font-weight: 600;
  color: #374151;
}

.progress-status {
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 500;
}

.progress-status.completed {
  background-color: #d1fae5;
  color: #059669;
}

.progress-status.in-progress {
  background-color: #fef3c7;
  color: #d97706;
}

.progress-status.not-started {
  background-color: #e5e7eb;
  color: #6b7280;
}

.empty-enrollments {
  padding: 40px 0;
  text-align: center;
  color: #6b7280;
}

.empty-enrollments .empty-icon {
  font-size: 4rem;
  margin-bottom: 15px;
}

.empty-enrollments p {
  margin-bottom: 10px;
}

.empty-enrollments small {
  font-size: 0.8rem;
  color: #9ca3af;
}

/* Responsive */
@media (max-width: 1200px) {
  .dashboard-container {
    max-width: 100%;
    padding: 0 16px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 991px) {
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
  
  .dashboard-content {
    grid-template-columns: 1fr;
  }
  
  .enrollment-card {
    flex-direction: column;
    gap: 10px;
    align-items: stretch;
  }
  
  .student-section {
    justify-content: flex-start;
  }
  
  .course-section {
    padding-left: 50px;
  }
  
  .progress-section {
    flex-direction: column;
    gap: 5px;
    align-items: stretch;
  }
  
  .progress-info {
    justify-content: space-between;
  }
}
</style>