<template>
  <!-- Desktop Layout -->
  <div v-if="!isMobile" class="student-layout" id="vue-student-layout">
    <!-- Profile Settings Modal -->
    <ProfileSettingsModal 
      v-if="showProfileModal"
      :user="user"
      @close="showProfileModal = false"
      @update:user="updateUserData"
    />
    
    <!-- Change Password Modal -->
    <ChangePasswordModal
      v-if="showPasswordModal"
      @close="showPasswordModal = false"
      @password-changed="handlePasswordChanged"
    />
    
    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="vue-sidebar">
      <div class="sidebar-header">
        <img :src="logoUrl" alt="LMS Prototype" class="logo">
        <h1>Student Dashboard</h1>
      </div>
      
      <nav class="sidebar-nav">
        <router-link :to="{ name: 'student.dashboard' }" class="nav-item">
          <i class="bi bi-speedometer2"></i>
          <span>Dashboard</span>
        </router-link>
        
        <router-link :to="{ name: 'student.courses' }" class="nav-item">
          <i class="bi bi-journal-text"></i>
          <span>My Courses</span>
        </router-link>
        
        <router-link :to="{ name: 'student.browse' }" class="nav-item">
          <i class="bi bi-grid-3x3-gap"></i>
          <span>Browse Courses</span>
        </router-link>
        
        <router-link :to="{ name: 'student.certificates' }" class="nav-item">
          <i class="bi bi-award"></i>
          <span>Certificates</span>
        </router-link>
        
        <router-link :to="{ name: 'student.messages' }" class="nav-item">
          <i class="bi bi-chat-dots"></i>
          <span>Messages</span>
        </router-link>
        
        <a href="#" @click.prevent="handleLogout" class="nav-item logout">
          <i class="bi bi-box-arrow-right"></i>
          <span>Logout</span>
        </a>
      </nav>
    </aside>
    
    <!-- Main Content Area -->
    <main class="main-content">
      <!-- Header -->
      <header class="header">
        <h1 class="header-title">{{ pageTitle }}</h1>
        
        <div class="user-menu" ref="userMenuRef">
          <button class="user-menu-button" @click="toggleProfileMenu" :class="{ 'active': isProfileMenuOpen }">
            <div class="user-avatar">
              <img v-if="user.avatar" :src="user.avatar" :alt="user.name" :key="user.avatar" class="avatar-image">
              <i v-else class="bi bi-person"></i>
            </div>
            <span>{{ user.name }}</span>
            <i class="bi bi-chevron-down" :class="{ 'rotate': isProfileMenuOpen }"></i>
          </button>
          
          <div v-if="isProfileMenuOpen" class="user-menu-dropdown">
            <div class="profile-header">
              <div class="profile-avatar">
                <img v-if="user.avatar" :src="user.avatar" :alt="user.name" :key="user.avatar" class="avatar-image">
                <i v-else class="bi bi-person"></i>
              </div>
              <div class="profile-info">
                <div class="profile-name">{{ user.name }}</div>
                <div class="profile-role">Student</div>
              </div>
            </div>
            
            <div class="dropdown-divider"></div>
            
            <a href="#" @click.prevent="openProfileSettings" class="dropdown-item">
              <i class="bi bi-person-gear"></i>
              Profile Settings
            </a>
            
            <a href="#" @click.prevent="openChangePassword" class="dropdown-item">
              <i class="bi bi-shield-lock"></i>
              Change Password
            </a>
            
            <div class="dropdown-divider"></div>
            
            <a href="#" @click.prevent="handleLogout" class="dropdown-item logout-item">
              <i class="bi bi-box-arrow-right"></i>
              Logout
            </a>
          </div>
        </div>
      </header>
      
      <!-- Page Content -->
      <div class="content-body">
        <router-view />
      </div>
    </main>
  </div>

  <!-- Mobile Layout -->
  <div v-else class="mobile-student-app">
    <!-- Profile Settings Modal -->
    <ProfileSettingsModal 
      v-if="showProfileModal"
      :user="user"
      @close="showProfileModal = false"
      @update:user="updateUserData"
    />
    
    <!-- Change Password Modal -->
    <ChangePasswordModal
      v-if="showPasswordModal"
      @close="showPasswordModal = false"
      @password-changed="handlePasswordChanged"
    />

    <!-- Mobile Header -->
    <header class="mobile-header">
      <div class="mobile-header-content">
        <div class="mobile-logo">
          <img :src="logoUrl" alt="LMS Prototype" class="mobile-logo-img">
          <span class="mobile-title">Student</span>
        </div>
        <div class="mobile-header-actions">
          <button @click="toggleMobileMenu" class="mobile-menu-btn">
            <i class="bi bi-list"></i>
          </button>
          <div class="mobile-user-avatar" @click="toggleProfileMenu">
            <img v-if="user.avatar" :src="user.avatar" :alt="user.name" :key="user.avatar" class="avatar-img">
            <i v-else class="bi bi-person"></i>
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
            <i class="bi bi-x"></i>
          </button>
        </div>
        <div class="mobile-nav-items">
          <router-link :to="{ name: 'student.dashboard' }" class="mobile-nav-item" @click="closeMobileMenu">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
          </router-link>
          <router-link :to="{ name: 'student.courses' }" class="mobile-nav-item" @click="closeMobileMenu">
            <i class="bi bi-journal-text"></i>
            <span>My Courses</span>
          </router-link>
          <router-link :to="{ name: 'student.browse' }" class="mobile-nav-item" @click="closeMobileMenu">
            <i class="bi bi-grid-3x3-gap"></i>
            <span>Browse Courses</span>
          </router-link>
          <router-link :to="{ name: 'student.certificates' }" class="mobile-nav-item" @click="closeMobileMenu">
            <i class="bi bi-award"></i>
            <span>Certificates</span>
          </router-link>
          <router-link :to="{ name: 'student.messages' }" class="mobile-nav-item" @click="closeMobileMenu">
            <i class="bi bi-chat-dots"></i>
            <span>Messages</span>
          </router-link>
          <a href="#" @click.prevent="handleLogout" class="mobile-nav-item logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
          </a>
        </div>
      </div>
    </nav>

    <!-- Mobile Profile Menu -->
    <div v-if="isProfileMenuOpen" class="mobile-profile-menu" @click="closeProfileMenu">
      <div class="mobile-profile-content" @click.stop>
        <div class="mobile-profile-header">
          <div class="mobile-profile-avatar">
            <img v-if="user.avatar" :src="user.avatar" :alt="user.name" :key="user.avatar" class="avatar-img">
            <i v-else class="bi bi-person"></i>
          </div>
          <div class="mobile-profile-info">
            <div class="mobile-profile-name">{{ user.name }}</div>
            <div class="mobile-profile-role">Student</div>
          </div>
          <button @click="closeProfileMenu" class="mobile-profile-close">
            <i class="bi bi-x"></i>
          </button>
        </div>
        <div class="mobile-profile-actions">
          <a href="#" @click.prevent="openProfileSettings" class="mobile-profile-action">
            <i class="bi bi-person-gear"></i>
            <span>Profile Settings</span>
          </a>
          <a href="#" @click.prevent="openChangePassword" class="mobile-profile-action">
            <i class="bi bi-shield-lock"></i>
            <span>Change Password</span>
          </a>
          <a href="#" @click.prevent="handleLogout" class="mobile-profile-action logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Mobile Main Content -->
    <main class="mobile-main-content">
      <div class="mobile-content-body">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useSiteSettings } from '../composables/useSiteSettings.js';
import { useDeviceDetection } from '../composables/useDeviceDetection.js';
import axios from 'axios';
// Import modals
import ProfileSettingsModal from './components/ProfileSettingsModal.vue';
import ChangePasswordModal from './components/ChangePasswordModal.vue';

export default {
  name: 'StudentApp',
  components: {
    ProfileSettingsModal,
    ChangePasswordModal
  },
  setup() {
    const route = useRoute();
    const isProfileMenuOpen = ref(false);
    const showProfileModal = ref(false);
    const showPasswordModal = ref(false);
    const userMenuRef = ref(null);
    
    // Mobile menu state
    const mobileMenuOpen = ref(false);

    // Device detection
    const { isMobile, isTablet, isDesktop } = useDeviceDetection();

    // Site settings for dynamic logo
    const { logoUrl, siteName, initializeSettings } = useSiteSettings();

    // User data
    const user = ref({
      name: 'Student User',
      avatar: '/assets/images/avatar.jpg',
      role: 'student'
    });

    // Load user data from API
    const loadUserData = async () => {
      try {
        const response = await axios.get('/api/user');
        const userData = response.data;
        user.value = {
          id: userData.id,
          name: userData.name,
          email: userData.email,
          avatar: userData.avatar, // Use the avatar URL directly from API
          role: userData.role
        };
      } catch (error) {
        console.error('Error loading user data:', error);
        // Keep default values on error
      }
    };

    // Compute page title based on current route
    const pageTitle = computed(() => {
      switch (route.name) {
        case 'student.dashboard':
          return 'Dashboard Overview';
        case 'student.courses':
          return 'My Courses';
        case 'student.browse':
          return 'Browse Courses';
        case 'student.certificates':
          return 'My Certificates';
        case 'student.messages':
          return 'Messages';
        case 'student.learning':
          return 'Learning';
        default:
          return 'Student Dashboard';
      }
    });

    // Toggle profile menu
    const toggleProfileMenu = () => {
      isProfileMenuOpen.value = !isProfileMenuOpen.value;
    };

    // Open profile settings and close dropdown
    const openProfileSettings = () => {
      isProfileMenuOpen.value = false;
      showProfileModal.value = true;
    };

    // Open change password and close dropdown  
    const openChangePassword = () => {
      isProfileMenuOpen.value = false;
      showPasswordModal.value = true;
    };

    // Mobile menu functions
    const toggleMobileMenu = () => {
      mobileMenuOpen.value = !mobileMenuOpen.value;
      // Close profile menu if open
      if (mobileMenuOpen.value) {
        isProfileMenuOpen.value = false;
      }
    };

    const closeMobileMenu = () => {
      mobileMenuOpen.value = false;
    };

    const closeProfileMenu = () => {
      isProfileMenuOpen.value = false;
    };

    // Handle outside click
    const handleOutsideClick = (event) => {
      if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
        isProfileMenuOpen.value = false;
      }
    };

    // Update user data
    const updateUserData = (newUserData) => {
      // Update user data directly without overwriting with API call
      user.value = { ...user.value, ...newUserData };
    };

    // Handle password changed
    const handlePasswordChanged = () => {
      // Handle password change success
    };

    // Logout function
    const handleLogout = async () => {
      try {
        await axios.post('/logout');
        window.location.href = '/';
      } catch (error) {
        console.error('Logout failed', error);
      }
    };

    // Lifecycle hooks
    onMounted(async () => {
      document.addEventListener('click', handleOutsideClick);
      
      // Initialize site settings
      await initializeSettings();
      await loadUserData(); // Load user data on mount
    });

    onUnmounted(() => {
      document.removeEventListener('click', handleOutsideClick);
    });

    return {
      route,
      isProfileMenuOpen,
      showProfileModal,
      showPasswordModal,
      userMenuRef,
      mobileMenuOpen,
      isMobile,
      isTablet,
      isDesktop,
      user,
      pageTitle,
      logoUrl,
      toggleProfileMenu,
      toggleMobileMenu,
      closeMobileMenu,
      closeProfileMenu,
      handleOutsideClick,
      updateUserData,
      handlePasswordChanged,
      handleLogout,
      openProfileSettings,
      openChangePassword
    };
  }
};
</script>

<style scoped>
/* Student Layout Styles */
.student-layout {
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
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-item.router-link-active {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

.nav-item.logout {
  margin-top: auto;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.7);
}

.nav-item.logout:hover {
  background: rgba(220, 53, 69, 0.2);
}

.nav-item i {
  width: 24px;
  margin-right: 12px;
  font-size: 1.1rem;
}

.nav-item span {
  font-size: 0.95rem;
  font-weight: 500;
}

/* Main Content Area */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Header */
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  background: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.header-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0;
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

.user-avatar .avatar-image {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.user-menu-button i.bi-chevron-down {
  margin-left: 8px;
  transition: transform 0.2s ease;
}

.user-menu-button i.bi-chevron-down.rotate {
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

.profile-avatar .avatar-image {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
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
  flex: 1;
  padding: 20px;
  overflow-y: auto;
}

/* Responsive Design */
@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    left: -280px;
    top: 0;
    bottom: 0;
    z-index: 1000;
  }

  .sidebar.open {
    left: 0;
  }

  .main-content {
    margin-left: 0;
  }

  .header {
    padding: 12px 16px;
  }

  .header-title {
    font-size: 1.2rem;
  }

  /* Fix profile dropdown overlap in mobile */
  .user-menu {
    position: relative;
  }

  .user-menu-dropdown {
    position: fixed;
    top: 60px;
    right: 16px;
    left: 16px;
    width: auto;
    min-width: auto;
    z-index: 1100;
    margin-top: 0;
  }

  .user-menu-button {
    font-size: 14px;
    padding: 6px 12px;
  }

  .user-menu-button span {
    display: none;
  }

  .profile-header {
    padding: 16px 20px;
  }

  .profile-avatar {
    width: 48px;
    height: 48px;
    font-size: 1.2rem;
  }

  .profile-name {
    font-size: 1rem;
  }

  .dropdown-item {
    padding: 12px 20px;
  }
}

/* Extra small mobile screens */
@media (max-width: 480px) {
  .header {
    padding: 8px 12px;
  }

  .header-title {
    font-size: 1.1rem;
  }

  .user-menu-dropdown {
    right: 8px;
    left: 8px;
  }
}

/* Mobile Layout Styles */
.mobile-student-app {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #f8f9fa;
}

/* Mobile Header */
.mobile-header {
  background: linear-gradient(135deg, #001d3d, #0077b6);
  color: white;
  padding: 12px 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 100;
}

.mobile-header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  max-width: 100%;
}

.mobile-logo {
  display: flex;
  align-items: center;
  gap: 8px;
}

.mobile-logo-img {
  height: 32px;
  width: auto;
}

.mobile-title {
  font-weight: 600;
  font-size: 1.1rem;
}

.mobile-header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.mobile-menu-btn {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.mobile-menu-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.mobile-user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border: 2px solid rgba(255, 255, 255, 0.3);
  transition: all 0.2s ease;
}

.mobile-user-avatar:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
}

.mobile-user-avatar .avatar-img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.mobile-user-avatar i {
  font-size: 1.2rem;
  color: white;
}

/* Mobile Navigation Menu */
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
  animation: overlay-appear 0.3s ease;
}

@keyframes overlay-appear {
  from { opacity: 0; }
  to { opacity: 1; }
}

.mobile-nav-content {
  background: white;
  width: 100%;
  border-radius: 20px 20px 0 0;
  max-height: 70vh;
  overflow-y: auto;
  animation: slide-in-bottom 0.3s ease;
}

@keyframes slide-in-bottom {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
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
  font-weight: 600;
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
  color: #6a5acd;
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
  border-bottom: none;
}

.mobile-nav-item i {
  margin-right: 12px;
  font-size: 1.1rem;
  width: 20px;
  text-align: center;
}

.mobile-nav-item span {
  font-size: 1rem;
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
  align-items: flex-start;
  justify-content: flex-end;
  padding: 60px 16px 16px;
  animation: overlay-appear 0.3s ease;
}

.mobile-profile-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  min-width: 280px;
  max-width: 100%;
  animation: slide-in-top 0.3s ease;
}

@keyframes slide-in-top {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.mobile-profile-header {
  background: linear-gradient(135deg, #001d3d, #0077b6);
  color: white;
  padding: 20px;
  border-radius: 12px 12px 0 0;
  display: flex;
  align-items: center;
}

.mobile-profile-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  margin-right: 15px;
  border: 3px solid white;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.mobile-profile-avatar .avatar-img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.mobile-profile-info {
  flex: 1;
}

.mobile-profile-name {
  font-weight: 700;
  margin-bottom: 4px;
  font-size: 1.1rem;
}

.mobile-profile-role {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.9);
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  padding: 4px 12px;
  display: inline-block;
}

.mobile-profile-close {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.mobile-profile-close:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.mobile-profile-actions {
  padding: 8px 0;
}

.mobile-profile-action {
  display: flex;
  align-items: center;
  padding: 16px 20px;
  color: #4a5568;
  text-decoration: none;
  transition: background-color 0.2s ease;
}

.mobile-profile-action:hover {
  background-color: #f7fafc;
  color: #0077b6;
}

.mobile-profile-action.logout:hover {
  background-color: #fef2f2;
  color: #dc2626;
}

.mobile-profile-action i {
  width: 20px;
  margin-right: 12px;
  font-size: 1.1rem;
  text-align: center;
}

.mobile-profile-action span {
  font-size: 1rem;
}

/* Mobile Main Content */
.mobile-main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.mobile-content-body {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
}
</style>