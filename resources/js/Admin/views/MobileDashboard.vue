<template>
  <div class="mobile-dashboard">
    <!-- Stats Cards - Stacked for mobile -->
    <div class="mobile-stats-container">
      <div class="mobile-stats-card">
        <div class="stats-icon users">
          <i class="fas fa-users"></i>
        </div>
        <div class="stats-content">
          <div class="stats-value">{{ stats.totalUsers }}</div>
          <div class="stats-label">Total Users</div>
          <div class="stats-change positive">
            <i class="fas fa-arrow-up"></i> {{ stats.userGrowth }}%
          </div>
        </div>
      </div>
      
      <div class="mobile-stats-card">
        <div class="stats-icon courses">
          <i class="fas fa-book-open"></i>
        </div>
        <div class="stats-content">
          <div class="stats-value">{{ stats.activeCourses }}</div>
          <div class="stats-label">Active Courses</div>
          <div class="stats-change positive">
            <i class="fas fa-arrow-up"></i> {{ stats.courseGrowth }}%
          </div>
        </div>
      </div>
      
      <div class="mobile-stats-card future-feature">
        <div class="stats-icon revenue">
          <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stats-content">
          <div class="stats-value">${{ stats.monthlyRevenue.toLocaleString() }}</div>
          <div class="stats-label">Monthly Revenue</div>
          <div class="stats-change" :class="stats.revenueGrowth >= 0 ? 'positive' : 'negative'">
            <i :class="stats.revenueGrowth >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
            {{ Math.abs(stats.revenueGrowth) }}%
          </div>
        </div>
        <div class="mobile-future-overlay">
          <div class="mobile-future-text">
            <i class="fas fa-cog fa-spin"></i>
            <span>Future</span>
          </div>
        </div>
      </div>
      
      <div class="mobile-stats-card">
        <div class="stats-icon enrollments">
          <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stats-content">
          <div class="stats-value">{{ stats.newEnrollments }}</div>
          <div class="stats-label">New Enrollments</div>
          <div class="stats-change positive">
            <i class="fas fa-arrow-up"></i> {{ stats.enrollmentGrowth }}%
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Chart Section -->
    <div class="mobile-chart-section">
      <div class="mobile-chart-card">
        <div class="chart-header">
          <h3>User Growth</h3>
          <select v-model="userChartPeriod" @change="updateUserChart" class="mobile-select">
            <option value="week">Week</option>
            <option value="month">Month</option>
            <option value="year">Year</option>
          </select>
        </div>
        <div class="chart-body">
          <canvas ref="userChartRef" class="mobile-chart"></canvas>
        </div>
      </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="mobile-activity-section">
      <div class="section-header">
        <h3>Recent Activity</h3>
        <button class="view-all-btn">View All</button>
      </div>
      <div class="mobile-activity-list">
        <div v-for="(activity, index) in recentActivities.slice(0, 5)" :key="index" class="mobile-activity-item">
          <div class="activity-icon" :class="activity.type">
            <i :class="getActivityIcon(activity.type)"></i>
          </div>
          <div class="activity-content">
            <div class="activity-title">{{ activity.title }}</div>
            <div class="activity-time">{{ activity.time }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- New Users Section -->
    <div class="mobile-users-section">
      <div class="section-header">
        <h3>New Users</h3>
        <button class="view-all-btn">View All</button>
      </div>
      <div class="mobile-users-list">
        <div v-for="(user, index) in newUsers.slice(0, 4)" :key="index" class="mobile-user-item">
          <img :src="user.avatar" :alt="user.name" class="user-avatar">
          <div class="user-details">
            <div class="user-name">{{ user.name }}</div>
            <div class="user-email">{{ user.email }}</div>
          </div>
          <div class="user-role" :class="user.role">{{ user.role }}</div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="mobile-loading">
      <div class="loading-spinner"></div>
      <p>Loading dashboard...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="mobile-error">
      <i class="fas fa-exclamation-triangle"></i>
      <p>{{ error }}</p>
      <button @click="fetchDashboardData" class="retry-btn">Retry</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import Chart from 'chart.js/auto';
import axios from 'axios';

// Chart references
const userChartRef = ref(null);
let userChart = null;

// Chart period selector
const userChartPeriod = ref('month');

// Data from API
const stats = ref({
  totalUsers: 0,
  userGrowth: 0,
  activeCourses: 0,
  courseGrowth: 0,
  monthlyRevenue: 0,
  revenueGrowth: 0,
  newEnrollments: 0,
  enrollmentGrowth: 0
});

const recentActivities = ref([]);
const newUsers = ref([]);
const loading = ref(true);
const error = ref(null);

// Helper function to get appropriate icon for activity type
const getActivityIcon = (type) => {
  const icons = {
    user: 'fas fa-user-plus',
    course: 'fas fa-book',
    payment: 'fas fa-credit-card',
    certificate: 'fas fa-certificate',
    enrollment: 'fas fa-user-graduate'
  };
  return icons[type] || 'fas fa-bell';
};

// Fetch dashboard data from API
const fetchDashboardData = async () => {
  try {
    loading.value = true;
    error.value = null;
    
    const response = await axios.get('/api/admin/dashboard-stats');
    console.log('Mobile Dashboard API Response:', response.data);
    
    if (response.data && response.data.stats) {
      stats.value = response.data.stats;
    } else if (response.data) {
      const data = response.data;
      stats.value = {
        totalUsers: data.totalUsers || 0,
        userGrowth: data.userGrowth || 0,
        activeCourses: data.activeCourses || 0,
        courseGrowth: data.courseGrowth || 0, 
        monthlyRevenue: data.monthlyRevenue || 0,
        revenueGrowth: data.revenueGrowth || 0,
        newEnrollments: data.newEnrollments || 0,
        enrollmentGrowth: data.enrollmentGrowth || 0
      };
    }
    
    if (response.data) {
      recentActivities.value = response.data.recentActivities || [];
      newUsers.value = response.data.recentUsers || [];
    }
    
    loading.value = false;
  } catch (err) {
    console.error('Error fetching mobile dashboard data:', err);
    error.value = 'Failed to load dashboard data. Please try again.';
    loading.value = false;
    
    stats.value = {
      totalUsers: 0,
      userGrowth: 0,
      activeCourses: 0,
      courseGrowth: 0,
      monthlyRevenue: 0,
      revenueGrowth: 0,
      newEnrollments: 0,
      enrollmentGrowth: 0
    };
  }
};

// Initialize mobile user chart
const initUserChart = async () => {
  try {
    if (!userChartRef.value) {
      console.warn('User chart ref not available yet');
      return;
    }

    let labels = [];
    let data = [];
    
    try {
      const response = await axios.get(`/api/admin/user-growth-chart?period=${userChartPeriod.value}`);
      if (response.data && response.data.labels && response.data.data) {
        labels = response.data.labels;
        data = response.data.data;
      }
    } catch (err) {
      console.warn('Using fallback chart data:', err);
      if (userChartPeriod.value === 'week') {
        labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        data = [1, 0, 1, 0, 0, 0, 1];
      } else if (userChartPeriod.value === 'month') {
        labels = Array.from({ length: 30 }, (_, i) => `${i+1}`);
        data = Array(30).fill(0);
        data[5] = 1;
        data[15] = 1;
        data[25] = 1;
      } else {
        labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        data = [0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 0, 0];
      }
    }
    
    const userCanvas = userChartRef.value;
    const userCtx = userCanvas.getContext('2d');
    
    if (userChart) {
      userChart.destroy();
    }
    
    userChart = new Chart(userCtx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'New Users',
          data: data,
          borderColor: '#7c3aed',
          backgroundColor: 'rgba(124, 58, 237, 0.1)',
          tension: 0.3,
          fill: true,
          pointRadius: 3,
          pointHoverRadius: 5
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          x: {
            display: true,
            ticks: {
              font: {
                size: 10
              }
            }
          },
          y: {
            display: true,
            ticks: {
              font: {
                size: 10
              }
            }
          }
        }
      }
    });
  } catch (error) {
    console.error('Error initializing mobile user chart:', error);
  }
};

// Update chart based on selected period
const updateUserChart = () => {
  initUserChart();
};

// For interval cleanup
let refreshInterval = null;

// Lifecycle hooks
onMounted(async () => {
  await fetchDashboardData();
  
  nextTick(() => {
    initUserChart();
  });
  
  // Refresh data every 5 minutes
  refreshInterval = setInterval(() => {
    fetchDashboardData();
    initUserChart();
  }, 5 * 60 * 1000);
});

// Cleanup on unmount
onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
  
  if (userChart) {
    userChart.destroy();
    userChart = null;
  }
});
</script>

<style scoped>
/* Mobile Dashboard Layout */
.mobile-dashboard {
  padding: 16px;
  background-color: #f8fafc;
  min-height: 100vh;
}

/* Mobile Stats Cards */
.mobile-stats-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 24px;
}

.mobile-stats-card {
  background-color: white;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  align-items: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transition: transform 0.2s ease;
  position: relative;
}

.mobile-stats-card:active {
  transform: scale(0.98);
}

.stats-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  color: white;
  font-size: 1.2rem;
}

.stats-icon.users {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
}

.stats-icon.courses {
  background: linear-gradient(135deg, #10b981, #059669);
}

.stats-icon.revenue {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.stats-icon.enrollments {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.stats-content {
  flex: 1;
}

.stats-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 2px;
}

.stats-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 4px;
}

.stats-change {
  font-size: 0.75rem;
  font-weight: 600;
}

.stats-change.positive {
  color: #059669;
}

.stats-change.negative {
  color: #dc2626;
}

/* Mobile Chart Section */
.mobile-chart-section {
  margin-bottom: 24px;
}

.mobile-chart-card {
  background-color: white;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.chart-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.mobile-select {
  padding: 6px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background-color: white;
  font-size: 0.875rem;
  color: #1f2937;
}

.chart-body {
  height: 200px;
}

.mobile-chart {
  width: 100% !important;
  height: 100% !important;
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

.view-all-btn {
  background: none;
  border: none;
  color: #7c3aed;
  font-size: 0.875rem;
  font-weight: 500;
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
}

.view-all-btn:active {
  background-color: rgba(124, 58, 237, 0.1);
}

/* Mobile Activity Section */
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
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  color: white;
  font-size: 0.875rem;
}

.activity-icon.user {
  background-color: #6366f1;
}

.activity-icon.course {
  background-color: #10b981;
}

.activity-icon.payment {
  background-color: #f59e0b;
}

.activity-icon.certificate {
  background-color: #8b5cf6;
}

.activity-icon.enrollment {
  background-color: #3b82f6;
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

.activity-time {
  font-size: 0.75rem;
  color: #6b7280;
}

/* Mobile Users Section */
.mobile-users-section {
  margin-bottom: 24px;
}

.mobile-users-list {
  background-color: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.mobile-user-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #f3f4f6;
}

.mobile-user-item:last-child {
  border-bottom: none;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 12px;
}

.user-details {
  flex: 1;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 2px;
}

.user-email {
  font-size: 0.75rem;
  color: #6b7280;
}

.user-role {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: capitalize;
}

.user-role.admin {
  background-color: #fef3c7;
  color: #92400e;
}

.user-role.teacher {
  background-color: #dbeafe;
  color: #1e40af;
}

.user-role.student {
  background-color: #d1fae5;
  color: #065f46;
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
  border-top: 3px solid #7c3aed;
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
  background-color: #7c3aed;
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
  background-color: #6d28d9;
}

/* Mobile Future Development Overlay */
.future-feature {
  position: relative;
}

.mobile-future-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  z-index: 10;
}

.mobile-future-text {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
  animation: mobilePulse 2s infinite;
}

.mobile-future-text i {
  font-size: 1rem;
}

@keyframes mobilePulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.03);
  }
}

/* Responsive Design */
@media (max-width: 480px) {
  .mobile-dashboard {
    padding: 12px;
  }
  
  .mobile-stats-card {
    padding: 12px;
  }
  
  .stats-value {
    font-size: 1.25rem;
  }
  
  .chart-body {
    height: 160px;
  }
  
  .mobile-future-text {
    padding: 10px 14px;
    font-size: 0.8rem;
    gap: 6px;
  }
  
  .mobile-future-text i {
    font-size: 0.9rem;
  }
}
</style> 