<template>
  <!-- Mobile Layout -->
  <div v-if="isMobile" class="mobile-admin-app">
    <!-- Mobile Header -->
    <header class="mobile-header">
      <div class="mobile-header-content">
        <div class="mobile-logo">
          <img :src="logoUrl" alt="LMS Prototype" class="mobile-logo-img">
          <span class="mobile-title">Admin</span>
        </div>
        <div class="mobile-header-actions">
          <button @click="toggleMobileMenu" class="mobile-menu-btn">
            <i class="fas fa-bars"></i>
          </button>
          <div class="mobile-user-avatar" @click="toggleProfileMenu">
            <i class="fas fa-user"></i>
          </div>
        </div>
      </div>
    </header>

    <!-- Mobile Navigation Menu -->
    <nav v-if="mobileMenuOpen" class="mobile-nav" @click="closeMobileMenu">
      <div class="mobile-nav-content" @click.stop>
        <div class="mobile-nav-header">
          <h3>Navigation</h3>
          <button @click="closeMobileMenu" class="mobile-nav-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="mobile-nav-items">
          <router-link :to="{ name: 'admin.dashboard' }" class="mobile-nav-item" @click="closeMobileMenu">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
          </router-link>
          <router-link :to="{ name: 'admin.users' }" class="mobile-nav-item" @click="closeMobileMenu">
            <i class="fas fa-users"></i>
            <span>User Management</span>
          </router-link>
          <a href="#" @click.prevent="handleLogout" class="mobile-nav-item logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
          </a>
        </div>
      </div>
    </nav>

    <!-- Mobile Profile Menu -->
    <div v-if="profileMenuOpen" class="mobile-profile-menu" @click="closeProfileMenu">
      <div class="mobile-profile-content" @click.stop>
        <div class="mobile-profile-header">
          <div class="mobile-profile-avatar">
            <i class="fas fa-user"></i>
          </div>
          <div class="mobile-profile-info">
            <div class="mobile-profile-name">{{ user.name }}</div>
            <div class="mobile-profile-role">Super Admin</div>
          </div>
          <button @click="closeProfileMenu" class="mobile-profile-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="mobile-profile-actions">
          <a href="#" @click.prevent="openProfileSettings" class="mobile-profile-action">
            <i class="fas fa-user-gear"></i>
            Profile Settings
          </a>
          <a href="#" @click.prevent="openChangePassword" class="mobile-profile-action">
            <i class="fas fa-shield-alt"></i>
            Change Password
          </a>
          <a href="#" @click.prevent="handleLogout" class="mobile-profile-action logout">
            <i class="fas fa-sign-out-alt"></i>
            Logout
          </a>
        </div>
      </div>
    </div>

    <!-- Mobile Content -->
    <main class="mobile-content">
      <router-view></router-view>
    </main>

  </div>

  <!-- Desktop Layout -->
  <div v-else class="admin-layout" id="vue-admin-layout">
    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="vue-sidebar">
      <div class="sidebar-header">
        <img :src="logoUrl" alt="LMS Prototype" class="logo">
        <h1>Admin Dashboard</h1>
      </div>
      
      <nav class="sidebar-nav">
        <router-link :to="{ name: 'admin.dashboard' }" class="nav-item">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </router-link>
        
        
        <router-link :to="{ name: 'admin.users' }" class="nav-item">
          <i class="fas fa-users"></i>
          <span>User Management</span>
        </router-link>
        
        <a href="#" @click.prevent="handleLogout" class="nav-item logout">
          <i class="fas fa-sign-out-alt"></i>
          <span>Logout</span>
        </a>
      </nav>
    </aside>
    
    <!-- Main Content Area -->
    <main class="main-content">
      <header class="content-header">
        <div class="header-left">
          <button @click="toggleSidebar" class="menu-toggle">
            <i class="fas fa-bars"></i>
          </button>
          <h2>{{ pageTitle }}</h2>
        </div>
        
        <div class="header-right">
          <div class="search-box">
            <input type="text" placeholder="Search...">
            <i class="fas fa-search"></i>
          </div>
          
          <div class="user-menu" ref="userMenuRef">
            <button 
              class="user-menu-button" 
              @click="toggleProfileMenu"
              :class="{ 'active': profileMenuOpen }"
            >
              <div class="user-avatar">
                <i class="fas fa-user"></i>
              </div>
              <span>{{ user.name }}</span>
              <i class="fas fa-chevron-down" :class="{ 'rotate': profileMenuOpen }"></i>
            </button>
            
            <div v-if="profileMenuOpen" class="user-menu-dropdown">
              <div class="profile-header">
                <div class="profile-avatar">
                  <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                  <div class="profile-name">{{ user.name }}</div>
                  <div class="profile-role">Super Admin</div>
                </div>
              </div>
              
              <div class="dropdown-divider"></div>
              
              <a href="#" @click.prevent="openProfileSettings" class="dropdown-item">
                <i class="fas fa-user-gear"></i>
                Profile Settings
              </a>
              
              <a href="#" @click.prevent="openChangePassword" class="dropdown-item">
                <i class="fas fa-shield-alt"></i>
                Change Password
              </a>
              
              <div class="dropdown-divider"></div>
              
              <a href="#" @click.prevent="handleLogout" class="dropdown-item logout-item">
                <i class="fas fa-sign-out-alt"></i>
                Logout
              </a>
            </div>
          </div>
        </div>
      </header>
      
      <div class="content-body">
        <!-- View components will be rendered here -->
        <router-view></router-view>
      </div>
    </main>

    <!-- Profile Settings Modal (Desktop) -->
    <teleport to="body" v-if="showProfileSettingsModal">
      <div class="add-user-modal-overlay" @click="closeProfileSettingsModal">
        <div class="add-user-modal-container" @click.stop>
          <div class="add-user-modal-header">
            <h3>Profile Settings</h3>
            <button @click="closeProfileSettingsModal" class="add-user-modal-close">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="updateProfile" class="add-user-modal-form">
            <div v-if="isLoadingUserData" class="loading-state">
              <i class="fas fa-spinner fa-spin"></i>
              <span>Loading profile data...</span>
            </div>

            <template v-else>
              <div class="add-user-form-group">
                <label for="profile_name">Full Name</label>
                <input
                  type="text"
                  id="profile_name"
                  v-model="profileData.name"
                  placeholder="Enter your full name"
                  required
                  :disabled="isUpdatingProfile"
                >
              </div>

              <div class="add-user-form-group">
                <label for="profile_email">Email Address</label>
                <input
                  type="email"
                  id="profile_email"
                  v-model="profileData.email"
                  placeholder="Enter your email address"
                  required
                  :disabled="isUpdatingProfile"
                >
              </div>
            </template>

            <div class="add-user-modal-actions">
              <button type="button" @click="closeProfileSettingsModal" class="add-user-btn-outline">
                Cancel
              </button>
              <button type="submit" class="add-user-btn-primary" :disabled="isUpdatingProfile">
                <i v-if="isUpdatingProfile" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-save"></i>
                {{ isUpdatingProfile ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </teleport>

    <!-- Notification Modal -->
    <teleport to="body" v-if="showNotificationModal">
      <div class="notification-modal-overlay" @click="closeNotificationModal">
        <div class="notification-modal-container" @click.stop>
          <div class="notification-modal-header" :class="`notification-${notificationData.type}`">
            <div class="notification-icon">
              <i v-if="notificationData.type === 'success'" class="fas fa-check-circle"></i>
              <i v-else-if="notificationData.type === 'error'" class="fas fa-exclamation-circle"></i>
              <i v-else-if="notificationData.type === 'warning'" class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>{{ notificationData.title }}</h3>
            <button @click="closeNotificationModal" class="notification-modal-close">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <div class="notification-modal-body">
            <p>{{ notificationData.message }}</p>
          </div>

          <div class="notification-modal-actions">
            <button @click="closeNotificationModal" class="notification-btn-primary" :class="`notification-btn-${notificationData.type}`">
              <i class="fas fa-check"></i>
              OK
            </button>
          </div>
        </div>
      </div>
    </teleport>

    <!-- Change Password Modal -->
    <teleport to="body" v-if="showChangePasswordModal">
      <div class="add-user-modal-overlay" @click="closeChangePasswordModal">
        <div class="add-user-modal-container" @click.stop>
          <div class="add-user-modal-header">
            <h3>Change Password</h3>
            <button @click="closeChangePasswordModal" class="add-user-modal-close">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="changePassword" class="add-user-modal-form">
            <div class="add-user-form-group">
              <label for="current_password">Current Password</label>
              <input
                type="password"
                id="current_password"
                v-model="passwordData.current_password"
                placeholder="Enter your current password"
                required
                :disabled="isChangingPassword"
                autocomplete="current-password"
              >
            </div>

            <div class="add-user-form-group">
              <label for="new_password">New Password</label>
              <input
                type="password"
                id="new_password"
                v-model="passwordData.new_password"
                placeholder="Enter your new password (min. 8 characters)"
                required
                :disabled="isChangingPassword"
                autocomplete="new-password"
                minlength="8"
              >
            </div>

            <div class="add-user-form-group">
              <label for="new_password_confirmation">Confirm New Password</label>
              <input
                type="password"
                id="new_password_confirmation"
                v-model="passwordData.new_password_confirmation"
                placeholder="Confirm your new password"
                required
                :disabled="isChangingPassword"
                autocomplete="new-password"
                minlength="8"
              >
            </div>

            <div class="add-user-password-info">
              <div class="add-user-info-box">
                <i class="fas fa-info-circle"></i>
                <span>Password must be at least <strong>8 characters long</strong> and different from your current password.</span>
              </div>
            </div>

            <div class="add-user-modal-actions">
              <button type="button" @click="closeChangePasswordModal" class="add-user-btn-outline">
                Cancel
              </button>
              <button type="submit" class="add-user-btn-primary" :disabled="isChangingPassword">
                <i v-if="isChangingPassword" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-key"></i>
                {{ isChangingPassword ? 'Changing...' : 'Change Password' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useDeviceDetection } from '../composables/useDeviceDetection.js';
import { useSiteSettings } from '../composables/useSiteSettings.js';
import axios from 'axios';

// Device detection
const { isMobile } = useDeviceDetection();

// Site settings for dynamic logo
const { logoUrl, siteName, initializeSettings } = useSiteSettings();

// Mobile navigation state
const mobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value;
};

const closeMobileMenu = () => {
  mobileMenuOpen.value = false;
};

// Desktop sidebar toggle state
const sidebarCollapsed = ref(false);
const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
  document.body.classList.toggle('sidebar-collapsed', sidebarCollapsed.value);
};

// Profile dropdown state
const profileMenuOpen = ref(false);
const userMenuRef = ref(null);

// Profile settings modal state
const showProfileSettingsModal = ref(false);
const isUpdatingProfile = ref(false);
const profileData = ref({
  name: '',
  email: ''
});

// Notification modal state
const showNotificationModal = ref(false);
const notificationData = ref({
  type: 'success', // 'success', 'error', 'warning'
  title: '',
  message: ''
});

// Change password modal state
const showChangePasswordModal = ref(false);
const isChangingPassword = ref(false);
const passwordData = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

const toggleProfileMenu = () => {
  profileMenuOpen.value = !profileMenuOpen.value;
};

const closeProfileMenu = () => {
  profileMenuOpen.value = false;
};

// Close profile menu when clicking outside
const handleOutsideClick = (event) => {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    profileMenuOpen.value = false;
  }
};

// Fetch current user data from API
const fetchUserData = async () => {
  isLoadingUserData.value = true;
  userDataError.value = '';

  try {
    const response = await axios.get('/api/admin/profile');

    if (response.data && response.data.user) {
      user.value = {
        ...user.value,
        ...response.data.user
      };
    }
  } catch (error) {
    console.error('Error fetching user data:', error);
    userDataError.value = 'Failed to load user data';
  } finally {
    isLoadingUserData.value = false;
  }
};

// Profile settings handlers
const openProfileSettings = async () => {
  // Fetch fresh user data before opening modal
  await fetchUserData();

  // Load current user data into the form
  profileData.value.name = user.value.name;
  profileData.value.email = user.value.email;

  showProfileSettingsModal.value = true;
  profileMenuOpen.value = false;
};

const closeProfileSettingsModal = () => {
  showProfileSettingsModal.value = false;
  isUpdatingProfile.value = false;
};

// Notification helper functions
const showNotification = (type, title, message) => {
  notificationData.value = { type, title, message };
  showNotificationModal.value = true;
};

const closeNotificationModal = () => {
  showNotificationModal.value = false;
};

const updateProfile = async () => {
  // Validate form data
  if (!profileData.value.name.trim() || !profileData.value.email.trim()) {
    showNotification('error', 'Validation Error', 'Please fill in all required fields');
    return;
  }

  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(profileData.value.email)) {
    showNotification('error', 'Invalid Email', 'Please enter a valid email address');
    return;
  }

  isUpdatingProfile.value = true;

  try {
    // Send profile update to API
    const response = await axios.put('/api/admin/profile', {
      name: profileData.value.name.trim(),
      email: profileData.value.email.trim()
    });

    if (response.data && response.data.success) {
      // Update local user data with response from server
      user.value.name = response.data.user.name || profileData.value.name;
      user.value.email = response.data.user.email || profileData.value.email;

      // Show success message
      showNotification('success', 'Success!', 'Profile updated successfully!');

      closeProfileSettingsModal();
    } else {
      throw new Error(response.data.message || 'Update failed');
    }
  } catch (error) {
    console.error('Error updating profile:', error);

    // Show specific error message from server if available
    const errorMessage = error.response?.data?.message || 'Failed to update profile. Please try again.';
    showNotification('error', 'Update Failed', errorMessage);
  } finally {
    isUpdatingProfile.value = false;
  }
};

const openChangePassword = () => {
  // Clear the form
  passwordData.value = {
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
  };

  showChangePasswordModal.value = true;
  profileMenuOpen.value = false;
};

const closeChangePasswordModal = () => {
  showChangePasswordModal.value = false;
  isChangingPassword.value = false;

  // Clear the form
  passwordData.value = {
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
  };
};

const changePassword = async () => {
  // Validate form data
  if (!passwordData.value.current_password.trim()) {
    showNotification('error', 'Validation Error', 'Please enter your current password');
    return;
  }

  if (!passwordData.value.new_password.trim()) {
    showNotification('error', 'Validation Error', 'Please enter a new password');
    return;
  }

  if (passwordData.value.new_password.length < 8) {
    showNotification('error', 'Password Too Short', 'New password must be at least 8 characters long');
    return;
  }

  if (passwordData.value.new_password !== passwordData.value.new_password_confirmation) {
    showNotification('error', 'Password Mismatch', 'New password and confirmation do not match');
    return;
  }

  if (passwordData.value.current_password === passwordData.value.new_password) {
    showNotification('error', 'Same Password', 'New password must be different from current password');
    return;
  }

  isChangingPassword.value = true;

  try {
    // Send password change request to API
    const response = await axios.post('/api/admin/change-password', {
      current_password: passwordData.value.current_password,
      new_password: passwordData.value.new_password,
      new_password_confirmation: passwordData.value.new_password_confirmation
    });

    if (response.data && response.data.success) {
      // Show success message
      showNotification('success', 'Password Changed', 'Your password has been updated successfully!');
      closeChangePasswordModal();
    } else {
      throw new Error(response.data.message || 'Password change failed');
    }
  } catch (error) {
    console.error('Error changing password:', error);

    // Show specific error message from server if available
    const errorMessage = error.response?.data?.message || 'Failed to change password. Please try again.';
    showNotification('error', 'Password Change Failed', errorMessage);
  } finally {
    isChangingPassword.value = false;
  }
};

// User data (loaded from API)
const user = ref({
  id: null,
  name: 'Admin User',
  email: 'admin@example.com',
  avatar: '/assets/images/avatar.jpg',
  role: 'super_admin'
});

// Loading states
const isLoadingUserData = ref(false);
const userDataError = ref('');

// Compute page title based on current route
const route = useRoute();
const pageTitle = computed(() => {
  switch (route.name) {
    case 'admin.dashboard':
      return 'Dashboard Overview';
    case 'admin.users':
      return 'User Management';
    default:
      return 'Admin Dashboard';
  }
});

// Logout function
const handleLogout = async () => {
  try {
    await axios.post('/logout');
    // Force navigation to the root URL (welcome page)
    window.location.href = '/';
  } catch (error) {
    console.error('Logout failed', error);
  }
};

// Lifecycle hooks
onMounted(async () => {
  // Initialize any dashboard functionality on mount
  console.log('Admin dashboard mounted');

  // Initialize site settings
  await initializeSettings();

  // Fetch current user data
  await fetchUserData();

  // Add event listeners
  document.addEventListener('click', handleOutsideClick);
});

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick);
});
</script>

<style scoped>
/* Mobile Layout Styles */
.mobile-admin-app {
  min-height: 100vh;
  background-color: #f8fafc;
  display: flex;
  flex-direction: column;
}

.mobile-header {
  background: linear-gradient(135deg, #001d3d, #0077b6);
  color: white;
  padding: 12px 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 100;
}

.mobile-header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.mobile-logo {
  display: flex;
  align-items: center;
}

.mobile-logo-img {
  height: 32px;
  margin-right: 8px;
}

.mobile-title {
  font-size: 1.2rem;
  font-weight: 600;
}

.mobile-header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.mobile-menu-btn, .mobile-user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.mobile-menu-btn:active, .mobile-user-avatar:active {
  background: rgba(255, 255, 255, 0.3);
}

/* Mobile Navigation */
.mobile-nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  display: flex;
  align-items: flex-end;
}

.mobile-nav-content {
  background: white;
  width: 100%;
  border-radius: 20px 20px 0 0;
  max-height: 70vh;
  overflow-y: auto;
}

.mobile-nav-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.mobile-nav-header h3 {
  margin: 0;
  font-size: 1.2rem;
  color: #1f2937;
}

.mobile-nav-close {
  background: none;
  border: none;
  color: #6b7280;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 4px;
}

.mobile-nav-items {
  padding: 20px;
}

.mobile-nav-item {
  display: flex;
  align-items: center;
  padding: 16px 0;
  color: #374151;
  text-decoration: none;
  border-bottom: 1px solid #f3f4f6;
  transition: color 0.2s ease;
}

.mobile-nav-item:last-child {
  border-bottom: none;
}

.mobile-nav-item:active {
  color: #0077b6;
}

.mobile-nav-item.router-link-active {
  color: #0077b6;
  font-weight: 600;
}

.mobile-nav-item.logout {
  color: #dc2626;
  margin-top: 12px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.mobile-nav-item i {
  margin-right: 12px;
  font-size: 1.1rem;
  width: 20px;
  text-align: center;
}

/* Mobile Profile Menu */
.mobile-profile-menu {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  display: flex;
  align-items: flex-end;
}

.mobile-profile-content {
  background: white;
  width: 100%;
  border-radius: 20px 20px 0 0;
  max-height: 50vh;
}

.mobile-profile-header {
  display: flex;
  align-items: center;
  padding: 20px;
  background: linear-gradient(135deg, #001d3d, #0077b6);
  color: white;
  border-radius: 20px 20px 0 0;
}

.mobile-profile-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  font-size: 1.2rem;
}

.mobile-profile-info {
  flex: 1;
}

.mobile-profile-name {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 4px;
}

.mobile-profile-role {
  font-size: 0.9rem;
  opacity: 0.9;
}

.mobile-profile-close {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 4px;
}

.mobile-profile-actions {
  padding: 20px;
}

.mobile-profile-action {
  display: flex;
  align-items: center;
  padding: 16px 0;
  color: #374151;
  text-decoration: none;
  border-bottom: 1px solid #f3f4f6;
  transition: color 0.2s ease;
}

.mobile-profile-action:last-child {
  border-bottom: none;
}

.mobile-profile-action:active {
  color: #0077b6;
}

.mobile-profile-action.logout {
  color: #dc2626;
  margin-top: 12px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.mobile-profile-action i {
  margin-right: 12px;
  font-size: 1.1rem;
  width: 20px;
  text-align: center;
}

.mobile-content {
  flex: 1;
  overflow-y: auto;
}

/* Desktop Layout Styles */
.admin-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f8f9fa;
}

/* Sidebar Styles */
.sidebar {
  width: 280px;
  background: linear-gradient(135deg, #001d3d, #0077b6);
  color: white;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar-header {
  padding: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
}

.sidebar-header .logo {
  height: 50px;
  margin-bottom: 10px;
}

.sidebar-header h1 {
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0;
}

.sidebar-nav {
  padding: 20px 0;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all 0.2s ease;
  margin: 2px 0;
}

.nav-item:hover, .nav-item.router-link-active {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-item i {
  margin-right: 12px;
  font-size: 1.1rem;
  width: 20px;
  text-align: center;
}

.logout {
  margin-top: auto;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.7);
}

/* Main Content Styles */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow-x: hidden;
}

.content-header {
  height: 70px;
  background-color: white;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.header-left, .header-right {
  display: flex;
  align-items: center;
}

.menu-toggle {
  background: none;
  border: none;
  color: #6b7280;
  font-size: 1.2rem;
  cursor: pointer;
  margin-right: 15px;
  padding: 5px;
}

.content-header h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: #374151;
}

.search-box {
  position: relative;
  margin-right: 20px;
}

.search-box input {
  padding: 8px 15px 8px 35px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  outline: none;
  width: 250px;
  transition: all 0.3s ease;
}

.search-box input:focus {
  border-color: #0077b6;
  box-shadow: 0 0 0 3px rgba(0, 119, 182, 0.1);
}

.search-box i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.user-menu {
  position: relative;
}

.user-menu-button {
  display: flex;
  align-items: center;
  padding: 8px 16px;
  background: none;
  border: none;
  cursor: pointer;
  color: #4a5568;
  font-weight: 500;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.user-menu-button:hover,
.user-menu-button.active {
  background-color: #f7fafc;
  color: #2d3748;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  margin-right: 8px;
  background: linear-gradient(135deg, #001d3d, #0077b6);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: white;
}

.user-menu-button i.fa-chevron-down {
  margin-left: 8px;
  transition: transform 0.2s ease;
}

.user-menu-button i.fa-chevron-down.rotate {
  transform: rotate(180deg);
}

.user-menu-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(226, 232, 240, 0.8);
  min-width: 280px;
  z-index: 1000;
  margin-top: 8px;
  overflow: hidden;
  animation: dropdown-appear 0.2s ease-out;
}

@keyframes dropdown-appear {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.profile-header {
  display: flex;
  align-items: center;
  padding: 20px;
  background: linear-gradient(to right, #001d3d, #0077b6);
  color: white;
}

.profile-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  margin-right: 15px;
  border: 3px solid white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.profile-info {
  flex: 1;
}

.profile-name {
  font-weight: 700;
  color: white;
  margin-bottom: 4px;
  font-size: 1.1rem;
}

.profile-role {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.9);
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 30px;
  padding: 2px 10px;
  display: inline-block;
}

.dropdown-divider {
  margin: 8px 0;
  border-bottom: 1px solid #e2e8f0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  padding: 14px 20px;
  color: #4a5568;
  text-decoration: none;
  border-left: 3px solid transparent;
  transition: all 0.2s ease;
}

.dropdown-item:hover {
  background-color: #f3f4f6;
  border-left-color: #0077b6;
  color: #0077b6;
}

.dropdown-item i {
  width: 20px;
  margin-right: 12px;
  font-size: 1.1rem;
  text-align: center;
}

.logout-item {
  color: #dc2626;
}

.logout-item:hover {
  background-color: #fef2f2;
  border-left-color: #dc2626;
  color: #dc2626;
}

.content-body {
  padding: 20px;
  flex: 1;
  overflow-y: auto;
}

/* Responsive styles */
@media (max-width: 991px) {
  .sidebar {
    width: 70px;
  }
  
  .sidebar-header h1, .nav-item span {
    display: none;
  }
  
  .search-box {
    display: none;
  }
  
  .user-menu-button span {
    display: none;
  }
}

@media (max-width: 768px) {
  .admin-layout {
    display: none;
  }
}

/* Add User Modal Styles - For Profile Settings */
.add-user-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.add-user-modal-container {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.add-user-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  background: linear-gradient(135deg, #001d3d, #0077b6);
  color: white;
  border-radius: 12px 12px 0 0;
}

.add-user-modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.add-user-modal-close {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.add-user-modal-close:hover {
  background: rgba(255, 255, 255, 0.1);
}

.add-user-modal-form {
  padding: 24px;
}

.add-user-form-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 16px;
}

.add-user-form-group label {
  font-weight: 500;
  margin-bottom: 6px;
  color: #374151;
  font-size: 0.9rem;
}

.add-user-form-group input {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.95rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.add-user-form-group input:focus {
  outline: none;
  border-color: #0077b6;
  box-shadow: 0 0 0 3px rgba(0, 119, 182, 0.1);
}

.add-user-modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
}

.add-user-btn-primary {
  background: linear-gradient(135deg, #0077b6, #001d3d);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.add-user-btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #005577, #001122);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 119, 182, 0.3);
}

.add-user-btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.add-user-btn-outline {
  background: white;
  color: #6b7280;
  border: 1px solid #d1d5db;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.add-user-btn-outline:hover {
  background: #f9fafb;
  border-color: #9ca3af;
  color: #374151;
}

/* Loading state for profile modal */
.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 40px 20px;
  color: #6b7280;
  font-size: 0.95rem;
}

.loading-state i {
  font-size: 1.2rem;
  color: #0077b6;
}

/* Disabled input states */
.add-user-form-group input:disabled {
  background-color: #f9fafb;
  color: #9ca3af;
  cursor: not-allowed;
}

/* Notification Modal Styles */
.notification-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 10001;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.notification-modal-container {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.notification-modal-header {
  display: flex;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  gap: 12px;
}

.notification-modal-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  flex: 1;
}

.notification-icon {
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.notification-modal-close {
  background: none;
  border: none;
  font-size: 1.1rem;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background-color 0.2s;
  color: #6b7280;
}

.notification-modal-close:hover {
  background: #f3f4f6;
}

.notification-modal-body {
  padding: 20px 24px;
}

.notification-modal-body p {
  margin: 0;
  color: #374151;
  line-height: 1.5;
}

.notification-modal-actions {
  padding: 16px 24px 20px;
  display: flex;
  justify-content: flex-end;
}

.notification-btn-primary {
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  color: white;
}

/* Consistent Blue Theme for All Notifications */
.notification-success,
.notification-error,
.notification-warning {
  background: linear-gradient(135deg, #001d3d, #0077b6);
  color: white;
}

.notification-success .notification-icon,
.notification-error .notification-icon,
.notification-warning .notification-icon {
  color: white;
}

.notification-success .notification-modal-close,
.notification-error .notification-modal-close,
.notification-warning .notification-modal-close {
  color: white;
}

.notification-success .notification-modal-close:hover,
.notification-error .notification-modal-close:hover,
.notification-warning .notification-modal-close:hover {
  background: rgba(255, 255, 255, 0.1);
}

.notification-btn-success,
.notification-btn-error,
.notification-btn-warning {
  background: linear-gradient(135deg, #0077b6, #001d3d);
}

.notification-btn-success:hover,
.notification-btn-error:hover,
.notification-btn-warning:hover {
  background: linear-gradient(135deg, #005577, #001122);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 119, 182, 0.3);
}
</style>