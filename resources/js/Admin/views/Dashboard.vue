<template>
  <div class="dashboard">
    <!-- Stats Cards Row -->
    <div class="stats-grid">
      <div class="stats-card">
        <div class="stats-icon users">
          <i class="fas fa-users"></i>
        </div>
        <div class="stats-content">
          <h3>Total Users</h3>
          <div class="stats-value">{{ stats.totalUsers }}</div>
          <div class="stats-change positive">
            <i class="fas fa-arrow-up"></i> {{ stats.userGrowth }}% from last month
          </div>
        </div>
      </div>
      
      <div class="stats-card">
        <div class="stats-icon courses">
          <i class="fas fa-book-open"></i>
        </div>
        <div class="stats-content">
          <h3>Active Courses</h3>
          <div class="stats-value">{{ stats.activeCourses }}</div>
          <div class="stats-change positive">
            <i class="fas fa-arrow-up"></i> {{ stats.courseGrowth }}% from last month
          </div>
        </div>
      </div>
      
      <div class="stats-card future-feature">
        <div class="stats-icon revenue">
          <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stats-content">
          <h3>Monthly Revenue</h3>
          <div class="stats-value">${{ stats.monthlyRevenue.toLocaleString() }}</div>
          <div class="stats-change" :class="stats.revenueGrowth >= 0 ? 'positive' : 'negative'">
            <i :class="stats.revenueGrowth >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
            {{ Math.abs(stats.revenueGrowth) }}% from last month
          </div>
        </div>
        <div class="future-overlay">
          <div class="future-text">
            <i class="fas fa-cog fa-spin"></i>
            <span>Future Development</span>
          </div>
        </div>
      </div>
      
      <div class="stats-card">
        <div class="stats-icon enrollments">
          <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stats-content">
          <h3>New Enrollments</h3>
          <div class="stats-value">{{ stats.newEnrollments }}</div>
          <div class="stats-change positive">
            <i class="fas fa-arrow-up"></i> {{ stats.enrollmentGrowth }}% from last month
          </div>
        </div>
      </div>
    </div>
    
    <!-- Charts Section -->
    <div class="charts-container">
      <div class="chart-card">
        <div class="chart-header">
          <h3>User Growth</h3>
          <div class="chart-actions">
            <select v-model="userChartPeriod" @change="updateUserChart">
              <option value="week">Last Week</option>
              <option value="month">Last Month</option>
              <option value="year">Last Year</option>
            </select>
          </div>
        </div>
        <div class="chart-body">
          <!-- Chart component would be rendered here -->
          <canvas ref="userChartRef" class="chart-canvas"></canvas>
        </div>
      </div>
      
      <div class="chart-card future-feature">
        <div class="chart-header">
          <h3>Revenue Analysis</h3>
          <div class="chart-actions">
            <select v-model="revenueChartPeriod" @change="updateRevenueChart">
              <option value="week">Last Week</option>
              <option value="month">Last Month</option>
              <option value="year">Last Year</option>
            </select>
          </div>
        </div>
        <div class="chart-body">
          <!-- Chart component would be rendered here -->
          <canvas ref="revenueChartRef" class="chart-canvas"></canvas>
        </div>
        <div class="future-overlay">
          <div class="future-text">
            <i class="fas fa-cog fa-spin"></i>
            <span>Future Development</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Recent Activity & New Users Section -->
    <div class="dashboard-row">
      <div class="dashboard-col">
        <div class="card recent-activity">
          <div class="card-header">
            <h3>Recent Activity</h3>
            <a href="#" class="view-all">View All</a>
          </div>
          <div class="card-body">
            <div v-for="(activity, index) in recentActivities" :key="index" class="activity-item">
              <div class="activity-icon" :class="activity.type">
                <i :class="getActivityIcon(activity.type)"></i>
              </div>
              <div class="activity-content">
                <div class="activity-title">{{ activity.title }}</div>
                <div class="activity-meta">{{ activity.time }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="dashboard-col">
        <div class="card recent-registrations">
          <div class="card-header">
            <h3>Recent Registrations</h3>
            <a href="/admin/users" class="view-all">Manage Users</a>
          </div>
          <div class="card-body">
            <div v-if="loading" class="loading-state">
              <i class="fas fa-spinner fa-spin"></i>
              Loading recent registrations...
            </div>
            <div v-else-if="recentRegistrations.length === 0" class="empty-state">
              <i class="fas fa-user-plus"></i>
              No recent registrations
            </div>
            <div v-else>
              <div v-for="user in recentRegistrations" :key="user.id" class="registration-item">
                <div class="user-info">
                  <div class="user-avatar">
                    <i class="fas fa-user"></i>
                  </div>
                  <div class="user-details">
                    <div class="user-name">{{ user.name }}</div>
                    <div class="user-email">{{ user.email }}</div>
                    <div class="user-meta">
                      <span class="registration-date">{{ user.registration_date }}</span>
                      <span class="user-role" :class="user.role">{{ user.role }}</span>
                      <span v-if="user.is_recent" class="recent-badge">New</span>
                    </div>
                  </div>
                </div>
                <div class="user-actions">
                  <div class="activity-indicator">
                    <span v-if="user.last_login" class="active-user" :title="'Last login: ' + user.last_login_formatted">
                      <i class="fas fa-circle"></i>
                    </span>
                    <span v-else class="inactive-user" title="Never logged in">
                      <i class="fas fa-circle"></i>
                    </span>
                  </div>
                  <button 
                    @click="deleteUser(user)" 
                    class="delete-btn"
                    title="Delete user"
                    :disabled="user.role === 'super_admin'"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import Chart from 'chart.js/auto';
import axios from 'axios';

// Chart references
const userChartRef = ref(null);
const revenueChartRef = ref(null);
let userChart = null;
let revenueChart = null;

// Chart period selectors
const userChartPeriod = ref('month');
const revenueChartPeriod = ref('month');

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
const recentRegistrations = ref([]);
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
    
    // Use correct endpoint URL based on routes/api.php
    const response = await axios.get('/api/admin/dashboard-stats');
    console.log('Dashboard API Response:', response.data); // Debug logging
    
    // Handle different response structures
    if (response.data && response.data.stats) {
      // Standard format with stats object
      stats.value = response.data.stats;
    } else if (response.data) {
      // Direct data without stats wrapper
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
    
    // Handle recent activities and recent registrations from the response
    if (response.data) {
      recentActivities.value = response.data.recentActivities || [];
      recentRegistrations.value = response.data.recentUsers || [];
    }
    
    loading.value = false;
  } catch (err) {
    console.error('Error fetching dashboard data:', err);
    console.error('Error response:', err.response);
    console.error('Error status:', err.response?.status);
    console.error('Error data:', err.response?.data);
    error.value = 'Failed to load dashboard data. Please try again.';
    loading.value = false;
    
    // Ensure stats has default values even on error
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

// Delete user function
const deleteUser = async (user) => {
  if (!confirm(`Are you sure you want to delete user "${user.name}" (${user.email})?`)) {
    return;
  }
  
  try {
    const response = await axios.delete(`/api/admin/users/${user.id}`);
    
    // Remove user from the local list
    recentRegistrations.value = recentRegistrations.value.filter(u => u.id !== user.id);
    
    // Show success message
    alert(response.data.message || 'User deleted successfully');
    
    // Refresh dashboard stats
    fetchDashboardData();
    
  } catch (error) {
    console.error('Error deleting user:', error);
    
    let errorMessage = 'Failed to delete user. Please try again.';
    if (error.response?.data?.error) {
      errorMessage = error.response.data.error;
    }
    
    alert(errorMessage);
  }
};

// Initialize and update user growth chart with fallback data if API fails
const initUserChart = async () => {
  try {
    // Make sure refs are available
    if (!userChartRef.value) {
      console.warn('User chart ref not available yet, skipping chart initialization');
      return;
    }

    let labels = [];
    let data = [];
    
    try {
      // Try to fetch user growth data from API
      const response = await axios.get(`/api/admin/user-growth-chart?period=${userChartPeriod.value}`);
      console.log('User Growth API Response:', response.data);
      
      if (response.data && response.data.labels && response.data.data) {
        labels = response.data.labels;
        data = response.data.data;
      }
    } catch (err) {
      console.warn('Could not fetch user growth data, using fallback:', err);
      // Generate fallback data if API call fails
      if (userChartPeriod.value === 'week') {
        labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        data = [1, 0, 1, 0, 0, 0, 1]; // Show the 3 users registered on different days
      } else if (userChartPeriod.value === 'month') {
        labels = Array.from({ length: 30 }, (_, i) => `Day ${i+1}`);
        data = Array(30).fill(0);
        data[5] = 1;  // First user registered on day 6
        data[15] = 1; // Second user registered on day 16
        data[25] = 1; // Third user registered on day 26
      } else {
        labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        data = [0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 0, 0]; // All 3 users registered in June
      }
    }
    
    const userCanvas = userChartRef.value;
    const userCtx = userCanvas.getContext('2d');
    
    if (userChart) {
      userChart.destroy(); // Destroy existing chart if it exists
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
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  } catch (error) {
    console.error('Error initializing user chart:', error);
  }
};

// Initialize and update revenue chart with fallback data if API fails
const initRevenueChart = async () => {
  try {
    // Make sure refs are available
    if (!revenueChartRef.value) {
      console.warn('Revenue chart ref not available yet, skipping chart initialization');
      return;
    }

    let labels = [];
    let data = [];
    
    try {
      // Try to fetch revenue data from API
      const response = await axios.get(`/api/admin/revenue-chart?period=${revenueChartPeriod.value}`);
      console.log('Revenue API Response:', response.data);
      
      if (response.data && response.data.labels && response.data.data) {
        labels = response.data.labels;
        data = response.data.data;
      }
    } catch (err) {
      console.warn('Could not fetch revenue data, using fallback:', err);
      // Generate fallback data if API call fails
      if (revenueChartPeriod.value === 'week') {
        labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        data = [0, 0, 0, 0, 0, 0, 0]; // No revenue yet
      } else if (revenueChartPeriod.value === 'month') {
        labels = Array.from({ length: 30 }, (_, i) => `Day ${i+1}`);
        data = Array(30).fill(0); // No revenue yet
      } else {
        labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        data = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]; // No revenue yet
      }
    }
    
    const revenueCanvas = revenueChartRef.value;
    const revenueCtx = revenueCanvas.getContext('2d');
    
    if (revenueChart) {
      revenueChart.destroy(); // Destroy existing chart if it exists
    }
    
    revenueChart = new Chart(revenueCtx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Revenue',
          data: data,
          backgroundColor: 'rgba(124, 58, 237, 0.7)'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  } catch (error) {
    console.error('Error initializing revenue chart:', error);
  }
};

// Initialize both charts
const initCharts = () => {
  initUserChart();
  initRevenueChart();
};

// Update charts based on selected period
const updateUserChart = () => {
  initUserChart();
};

const updateRevenueChart = () => {
  initRevenueChart();
};

// For interval cleanup
let refreshInterval = null;

// Lifecycle hooks
onMounted(async () => {
  // Fetch dashboard data
  await fetchDashboardData();
  
  // Use nextTick to ensure DOM is fully rendered before initializing charts
  nextTick(() => {
    initCharts();
  });
  
  // Refresh data every 5 minutes
  refreshInterval = setInterval(() => {
    fetchDashboardData();
    initCharts();
  }, 5 * 60 * 1000);
});

// Cleanup on unmount
onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
  
  // Destroy charts if they exist
  if (userChart) {
    userChart.destroy();
    userChart = null;
  }
  
  if (revenueChart) {
    revenueChart.destroy();
    revenueChart = null;
  }
});
</script>

<style scoped>
/* Dashboard Layout */
.dashboard {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.stats-card {
  background-color: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  position: relative;
}

.stats-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.stats-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  color: white;
  font-size: 1.5rem;
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

.stats-content h3 {
  margin: 0 0 5px 0;
  font-size: 0.9rem;
  color: #6b7280;
  font-weight: 500;
}

.stats-value {
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 5px;
  color: #111827;
}

.stats-change {
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 5px;
}

.stats-change.positive {
  color: #10b981;
}

.stats-change.negative {
  color: #ef4444;
}

/* Charts Container */
.charts-container {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.chart-card {
  background-color: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  position: relative;
}

.chart-header {
  padding: 15px 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chart-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #374151;
}

.chart-actions select {
  padding: 6px 10px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  outline: none;
  font-size: 0.9rem;
  color: #4b5563;
  background-color: white;
  cursor: pointer;
}

.chart-actions select:focus {
  border-color: #7c3aed;
  box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.1);
}

.chart-body {
  padding: 20px;
}

.chart-canvas {
  height: 300px;
  width: 100%;
}

/* Future Development Overlay */
.future-feature {
  position: relative;
}

.future-overlay {
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

.future-text {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 24px;
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.95rem;
  box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
  animation: pulse 2s infinite;
}

.future-text i {
  font-size: 1.1rem;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

/* Dashboard Row & Column */
.dashboard-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.dashboard-col {
  display: flex;
  flex-direction: column;
}

/* Card Styles */
.card {
  background-color: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  flex-grow: 1;
}

.card-header {
  padding: 15px 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #374151;
}

.view-all {
  color: #7c3aed;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: color 0.2s ease;
}

.view-all:hover {
  color: #5b21b6;
}

.card-body {
  padding: 20px;
}

/* Activity Item */
.activity-item {
  display: flex;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f3f4f6;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  color: white;
  font-size: 0.9rem;
}

.activity-icon.user {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
}

.activity-icon.course {
  background: linear-gradient(135deg, #10b981, #059669);
}

.activity-icon.payment {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.activity-icon.certificate {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.activity-icon.enrollment {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.activity-content {
  flex: 1;
}

.activity-title {
  font-size: 0.9rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 2px;
}

.activity-meta {
  font-size: 0.8rem;
  color: #6b7280;
}

/* User Item */
.user-item {
  display: flex;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f3f4f6;
}

.user-item:last-child {
  border-bottom: none;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  margin-right: 12px;
  object-fit: cover;
}

.user-details {
  flex: 1;
}

.user-name {
  font-size: 0.9rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 2px;
}

.user-meta {
  font-size: 0.8rem;
  color: #6b7280;
}

.user-role {
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 500;
  text-transform: capitalize;
}

.user-role.student {
  background-color: #dbeafe;
  color: #1e40af;
}

.user-role.teacher {
  background-color: #d1fae5;
  color: #065f46;
}

.user-role.admin {
  background-color: #fef3c7;
  color: #92400e;
}

/* Recent Registrations Styles */
.recent-registrations .card-body {
  padding: 16px;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #6b7280;
}

.loading-state i, .empty-state i {
  font-size: 2rem;
  margin-bottom: 12px;
  display: block;
}

.loading-state i {
  color: #3b82f6;
}

.empty-state i {
  color: #9ca3af;
}

.registration-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #f3f4f6;
}

.registration-item:last-child {
  border-bottom: none;
}

.user-info {
  display: flex;
  align-items: center;
  flex: 1;
}

.user-info .user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  margin-right: 12px;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  font-size: 1.2rem;
}

.user-info .user-details {
  flex: 1;
}

.user-email {
  font-size: 0.8rem;
  color: #6b7280;
  margin-bottom: 4px;
}

.user-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.registration-date {
  font-size: 0.75rem;
  color: #6b7280;
  background: #f3f4f6;
  padding: 2px 6px;
  border-radius: 4px;
}

.recent-badge {
  background: #10b981;
  color: white;
  font-size: 0.7rem;
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 500;
}

.user-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.activity-indicator {
  display: flex;
  align-items: center;
}

.active-user, .inactive-user {
  font-size: 0.6rem;
  cursor: help;
}

.active-user {
  color: #10b981;
}

.inactive-user {
  color: #ef4444;
}

.delete-btn {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  padding: 6px;
  border-radius: 4px;
  font-size: 0.9rem;
  transition: background-color 0.2s;
}

.delete-btn:hover:not(:disabled) {
  background-color: #fee2e2;
}

.delete-btn:disabled {
  color: #9ca3af;
  cursor: not-allowed;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .charts-container {
    grid-template-columns: 1fr;
  }
  
  .dashboard-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-card {
    padding: 15px;
  }
  
  .chart-card {
    margin-bottom: 20px;
  }
  
  .dashboard {
    gap: 16px;
  }
  
  /* Mobile optimized overlay */
  .future-text {
    padding: 12px 16px;
  }
  
  /* Mobile recent registrations */
  .registration-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
    padding: 16px 0;
  }
  
  .user-info {
    width: 100%;
  }
  
  .user-actions {
    align-self: flex-end;
    margin-top: 8px;
  }
  
  .user-meta {
    gap: 6px;
  }
  
  .registration-date {
    font-size: 0.7rem;
  }
  
  .future-text i {
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .future-text {
    padding: 10px 14px;
    font-size: 0.8rem;
    gap: 6px;
  }
  
  .future-text i {
    font-size: 0.9rem;
  }
  
  .future-text span {
    display: none;
  }
  
  .future-text::after {
    content: 'Future';
  }
}
</style>
