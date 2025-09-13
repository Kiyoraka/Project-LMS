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

// Profile settings handlers
const openProfileSettings = () => {
  // In a real app, this would open a profile settings modal
  alert('Profile settings would open here');
  profileMenuOpen.value = false;
};

const openChangePassword = () => {
  // In a real app, this would open a change password modal
  alert('Change password form would open here');
  profileMenuOpen.value = false;
};

// Mock user data (would come from API in real app)
const user = ref({
  name: 'Admin User',
  avatar: '/assets/images/avatar.jpg',
  role: 'super_admin'
});

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
</style>