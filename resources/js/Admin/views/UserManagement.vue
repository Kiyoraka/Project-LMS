<template>
  <div class="user-management">
    <!-- Filters and Search -->
    <div class="filters-container">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search users by name, email, or role..."
          @input="applyFilters"
        >
      </div>
      
      <div class="filters">
        <div class="filter-group">
          <label>Role</label>
          <select v-model="filters.role" @change="applyFilters">
            <option value="">All Roles</option>
            <option value="super_admin">Super Admin</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
          </select>
        </div>
        
        <div class="filter-group">
          <label>Status</label>
          <select v-model="filters.status" @change="applyFilters">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        
        <div class="filter-group">
          <label>Sort By</label>
          <select v-model="filters.sortBy" @change="applyFilters">
            <option value="name">Name</option>
            <option value="email">Email</option>
            <option value="created_at">Date Created</option>
            <option value="last_login">Last Login</option>
          </select>
        </div>
      </div>
      <div class="reset-btn-container">
        <button @click="resetFilters" class="btn-outline btn-reset">
          <i class="fas fa-redo"></i> Reset
        </button>
      </div>
    </div>
    
    <!-- Users Table -->
    <div class="users-table-container">
      <table class="users-table">
        <thead>
          <tr>
            <th class="user-col">User</th>
            <th>Role</th>
            <th>Status</th>
            <th>Date Created</th>
            <th>Last Login</th>
            <th class="actions-col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in filteredUsers" :key="user.id">
            <td class="user-col">
              <div class="user-info">
                <img :src="user.avatar" :alt="user.name" class="user-avatar">
                <div class="user-details">
                  <div class="user-name">
                    {{ user.name }}
                    <span v-if="isNewRegistration(user)" class="new-badge">NEW</span>
                  </div>
                  <div class="user-email">{{ user.email }}</div>
                </div>
              </div>
            </td>
            <td>
              <span class="user-role" :class="user.role">{{ user.role }}</span>
            </td>
            <td>
              <span class="user-status" :class="user.status">{{ user.status }}</span>
            </td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td>{{ formatDate(user.last_login) }}</td>
            <td class="actions-col">
              <div class="actions">
                <button 
                  v-if="user.role !== 'super_admin'" 
                  @click="editUser(user)" 
                  class="action-btn edit"
                >
                  <i class="fas fa-edit"></i>
                </button>
                <button 
                  v-if="user.role !== 'super_admin'" 
                  @click="confirmDelete(user)" 
                  class="action-btn delete"
                >
                  <i class="fas fa-trash-alt"></i>
                </button>
                <button v-if="user.role === 'teacher' && user.status !== 'active'" @click="approveUser(user)" class="action-btn approve">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <div class="pagination">
      <div class="pagination-info">
        Showing {{ paginatedUsers.length }} of {{ filteredUsers.length }} users
      </div>
      <div class="pagination-controls">
        <button 
          @click="prevPage" 
          :disabled="currentPage === 1" 
          class="pagination-btn"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        
        <div v-for="page in totalPages" :key="page" class="page-number">
          <button 
            @click="goToPage(page)" 
            :class="{ active: currentPage === page }" 
            class="pagination-btn"
          >
            {{ page }}
          </button>
        </div>
        
        <button 
          @click="nextPage" 
          :disabled="currentPage === totalPages" 
          class="pagination-btn"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>
    
    <!-- Add User Modal -->
    <div v-if="showAddUserModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h3>Add New User</h3>
          <button @click="showAddUserModal = false" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="addUser">
            <div class="form-group">
              <label for="name">Full Name</label>
              <input type="text" id="name" v-model="newUser.name" required>
            </div>
            
            <div class="form-group">
              <label for="email">Email Address</label>
              <input type="email" id="email" v-model="newUser.email" required>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="role">Role</label>
                <select id="role" v-model="newUser.role" required>
                  <option value="super_admin">Super Admin</option>
                  <option value="teacher">Teacher</option>
                  <option value="student">Student</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="status">Status</label>
                <select id="status" v-model="newUser.status" required>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
            
            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" id="password" v-model="newUser.password" required>
            </div>
            
            <div class="form-group">
              <label for="confirm_password">Confirm Password</label>
              <input type="password" id="confirm_password" v-model="newUser.confirm_password" required>
            </div>
            
            <div class="form-actions">
              <button type="button" @click="showAddUserModal = false" class="btn-outline">
                Cancel
              </button>
              <button type="submit" class="btn-primary">
                Add User
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Edit User Modal -->
    <div v-if="showEditUserModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h3>Edit User</h3>
          <button @click="showEditUserModal = false" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="updateUser">
            <div class="form-group">
              <label for="edit_name">Full Name</label>
              <input type="text" id="edit_name" v-model="selectedUser.name" required>
            </div>
            
            <div class="form-group">
              <label for="edit_email">Email Address</label>
              <input type="email" id="edit_email" v-model="selectedUser.email" required>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="edit_role">Role</label>
                <select id="edit_role" v-model="selectedUser.role" required>
                  <option value="super_admin">Super Admin</option>
                  <option value="teacher">Teacher</option>
                  <option value="student">Student</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="edit_status">Status</label>
                <select id="edit_status" v-model="selectedUser.status" required>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
            
            <div class="form-group">
              <label for="edit_password">Password (leave blank to keep current)</label>
              <input type="password" id="edit_password" v-model="selectedUser.password">
              <span class="help-text">Only fill this if you want to change the password</span>
            </div>
            
            <div class="form-group" v-if="selectedUser.password">
              <label for="edit_confirm_password">Confirm Password</label>
              <input type="password" id="edit_confirm_password" v-model="selectedUser.confirm_password">
            </div>
            
            <div class="form-actions">
              <button type="button" @click="showEditUserModal = false" class="btn-outline">
                Cancel
              </button>
              <button type="submit" class="btn-primary">
                Update User
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay">
      <div class="modal delete-modal">
        <div class="modal-header">
          <h3>Confirm Deletion</h3>
          <button @click="showDeleteModal = false" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <p class="delete-message">
            Are you sure you want to delete the user <strong>{{ selectedUser?.name }}</strong>?
            This action cannot be undone.
          </p>
          
          <div class="form-actions">
            <button @click="showDeleteModal = false" class="btn-outline">
              Cancel
            </button>
            <button @click="deleteUser" class="btn-danger">
              Delete User
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Success Message Toast -->
    <div v-if="successMessage" class="toast-message success">
      <i class="fas fa-check-circle"></i>
      <span>{{ successMessage }}</span>
    </div>
    
    <!-- Error Message Toast -->
    <div v-if="error" class="toast-message error">
      <i class="fas fa-exclamation-circle"></i>
      <span>{{ error }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

// User modal states
const showAddUserModal = ref(false);
const showEditUserModal = ref(false);
const showDeleteModal = ref(false);

// Selected user for edit/delete
const selectedUser = ref(null);

// New user form data
const newUser = ref({
  name: '',
  email: '',
  role: 'student',
  status: 'active',
  password: '',
  confirm_password: ''
});

// Search and filters
const searchQuery = ref('');
const filters = ref({
  role: '',
  status: '',
  sortBy: 'name'
});

// Pagination
const currentPage = ref(1);
const perPage = ref(10);

// Users data
const users = ref([]);
const isLoading = ref(false);
const error = ref('');
const successMessage = ref('');


// Filter users based on search and filters
const filteredUsers = computed(() => {
  return users.value.filter(user => {
    // Search query filter
    if (searchQuery.value && !user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) &&
        !user.email.toLowerCase().includes(searchQuery.value.toLowerCase()) &&
        !user.role.toLowerCase().includes(searchQuery.value.toLowerCase())) {
      return false;
    }
    
    // Role filter
    if (filters.value.role && user.role !== filters.value.role) {
      return false;
    }
    
    // Status filter
    if (filters.value.status && user.status !== filters.value.status) {
      return false;
    }
    
    return true;
  }).sort((a, b) => {
    // Sorting
    const sortBy = filters.value.sortBy;
    if (sortBy === 'name') {
      return a.name.localeCompare(b.name);
    } else if (sortBy === 'email') {
      return a.email.localeCompare(b.email);
    } else if (sortBy === 'created_at') {
      return new Date(b.created_at) - new Date(a.created_at);
    } else if (sortBy === 'last_login') {
      return new Date(b.last_login) - new Date(a.last_login);
    }
    return 0;
  });
});

// Paginated users
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  const end = start + perPage.value;
  return filteredUsers.value.slice(start, end);
});

// Total pages for pagination
const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / perPage.value) || 1;
});

// Pagination methods
const goToPage = (page) => {
  currentPage.value = page;
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

// Filter methods
const applyFilters = () => {
  currentPage.value = 1; // Reset to first page when filters change
};

const resetFilters = () => {
  searchQuery.value = '';
  filters.value = {
    role: '',
    status: '',
    sortBy: 'name'
  };
  currentPage.value = 1;
};

// User management methods
const addUser = async () => {
  // Validate passwords match
  if (newUser.value.password !== newUser.value.confirm_password) {
    alert('Passwords do not match');
    return;
  }
  
  isLoading.value = true;
  error.value = '';
  
  try {
    // Send the new user data to the API
    const response = await axios.post('/api/admin/users', {
      name: newUser.value.name,
      email: newUser.value.email,
      role: newUser.value.role,
      is_active: newUser.value.status === 'active',
      password: newUser.value.password,
    });
    
    // Refresh the user list
    await fetchUsers();
    
    // Show success message
    successMessage.value = 'User created successfully!';
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
    
    // Reset form and close modal
    newUser.value = {
      name: '',
      email: '',
      role: 'student',
      status: 'active',
      password: '',
      confirm_password: ''
    };
    
    showAddUserModal.value = false;
  } catch (err) {
    console.error('Error creating user:', err);
    error.value = err.response?.data?.message || 'Failed to create user. Please try again.';
  } finally {
    isLoading.value = false;
  }
};

// Set up user for editing
const editUser = (user) => {
  selectedUser.value = { 
    ...user,
    // Ensure we have the right format for the form
    status: user.status || (user.is_active ? 'active' : 'inactive'),
    password: '',
    confirm_password: ''
  };
  showEditUserModal.value = true;
};

// Update an existing user
const updateUser = async () => {
  // Validate passwords if provided
  if (selectedUser.value.password && 
      selectedUser.value.password !== selectedUser.value.confirm_password) {
    alert('Passwords do not match');
    return;
  }
  
  isLoading.value = true;
  error.value = '';
  
  try {
    // Prepare the update data
    const userData = {
      name: selectedUser.value.name,
      email: selectedUser.value.email,
      role: selectedUser.value.role,
      is_active: selectedUser.value.status === 'active'
    };
    
    // Only include password if it was changed
    if (selectedUser.value.password) {
      userData.password = selectedUser.value.password;
    }
    
    // Send update to API
    const response = await axios.put(`/api/admin/users/${selectedUser.value.id}`, userData);
    
    // Refresh the user list
    await fetchUsers();
    
    // Show success message
    successMessage.value = 'User updated successfully!';
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
    
    // Close modal
    showEditUserModal.value = false;
  } catch (err) {
    console.error('Error updating user:', err);
    error.value = err.response?.data?.message || 'Failed to update user. Please try again.';
  } finally {
    isLoading.value = false;
  }
};

// Open delete confirmation
const confirmDelete = (user) => {
  selectedUser.value = user;
  showDeleteModal.value = true;
};

// Delete a user
const deleteUser = async () => {
  isLoading.value = true;
  error.value = '';
  
  try {
    // Send delete request to API
    await axios.delete(`/api/admin/users/${selectedUser.value.id}`);
    
    // Refresh the user list
    await fetchUsers();
    
    // Show success message
    successMessage.value = 'User deleted successfully!';
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
    
    // Close modal
    showDeleteModal.value = false;
  } catch (err) {
    console.error('Error deleting user:', err);
    error.value = err.response?.data?.message || 'Failed to delete user. Please try again.';
  } finally {
    isLoading.value = false;
  }
};

// Approve a user
const approveUser = async (user) => {
  isLoading.value = true;
  error.value = '';
  try {
    const response = await axios.put(`/api/admin/users/${user.id}`, {
      ...user,
      status: 'active',
      is_active: true
    });
    await fetchUsers();
    successMessage.value = 'User approved successfully!';
    setTimeout(() => { successMessage.value = ''; }, 3000);
  } catch (err) {
    console.error('Error approving user:', err);
    error.value = err.response?.data?.message || 'Failed to approve user. Please try again.';
  } finally {
    isLoading.value = false;
  }
};

// Helper functions
const formatDate = (dateString) => {
  if (!dateString) return 'Never';
  
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date);
};

// Check if user is a new registration (within 3 days)
const isNewRegistration = (user) => {
  if (!user.created_at) return false;
  
  const registrationDate = new Date(user.created_at);
  const threeDaysAgo = new Date();
  threeDaysAgo.setDate(threeDaysAgo.getDate() - 3);
  
  return registrationDate > threeDaysAgo;
};

// Generate avatar URL based on user name
const generateAvatarUrl = (name) => {
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random`;
};

// Process user data from API response
const processUserData = (userData) => {
  return userData.map(user => {
    // Add avatar if not present
    if (!user.avatar) {
      user.avatar = generateAvatarUrl(user.name);
    }
    
    // Ensure status field exists
    if (!user.status) {
      user.status = user.is_active ? 'active' : 'inactive';
    }
    
    return user;
  });
};


// Fetch users from API
const fetchUsers = async () => {
  isLoading.value = true;
  error.value = '';
  
  try {
    // Make API request to get users using session-based auth route
    const response = await axios.get('/api/admin/users', {
      params: {
        per_page: 50  // Request more users to allow client-side filtering
      }
    });
    
    // Check if response has data in expected format
    if (response.data && (response.data.data || Array.isArray(response.data))) {
      // Handle both paginated response {data: [...]} and direct array [...]
      const userData = response.data.data || response.data;
      // Process user data to ensure all fields are properly formatted
      users.value = processUserData(userData);
    } else {
      console.error('Unexpected API response format:', response.data);
      error.value = 'Received unexpected data format from server';
      users.value = [];
    }
  } catch (err) {
    console.error('Error fetching users:', err);
    
    // Improved error handling for authentication issues
    if (err.response?.status === 404 || err.response?.status === 401 || err.response?.status === 403) {
      error.value = 'Authentication required. Please log in with admin credentials to access user data.';
    } else {
      error.value = err.response?.data?.message || 'Failed to load users. Please try again.';
      users.value = [];
    }
  } finally {
    isLoading.value = false;
  }
};
// Lifecycle hooks
onMounted(async () => {
  try {
    isLoading.value = true;
    await fetchUsers();
    console.log('User Management component mounted with real database data');
  } catch (err) {
    console.error('Error loading users:', err);
    error.value = 'Failed to load users from database. Please try again.';
  } finally {
    isLoading.value = false;
  }
});
</script>

<style scoped>
/* User Management Page Styles */
.user-management {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding: 24px;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-title h2 {
  margin: 0 0 5px 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: #111827;
}

.header-title p {
  margin: 0;
  color: #6b7280;
  font-size: 0.95rem;
}

.btn-primary {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 5px rgba(139, 92, 246, 0.3);
}

.btn-primary:hover {
  background: linear-gradient(135deg, #9d6af9, #8952f3);
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(139, 92, 246, 0.4);
}

.btn-outline {
  background: white;
  color: #6b7280;
  border: 1px solid #d1d5db;
  padding: 9px 16px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-outline:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

/* Filters and Search */
.filters-container {
  background-color: white;
  border-radius: 12px;
  padding: 16px 20px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.search-box {
  position: relative;
  flex: 1;
}

.search-box input {
  width: 100%;
  padding: 10px 16px 10px 40px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.95rem;
  outline: none;
  transition: all 0.2s ease;
}

.search-box input:focus {
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.search-box i {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.filters {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
  min-width: 150px;
}

.filter-group label {
  font-size: 0.8rem;
  font-weight: 500;
  color: #4b5563;
}

.filter-group select {
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.9rem;
  color: #1f2937;
  background-color: white;
  outline: none;
  transition: all 0.2s ease;
}

.filter-group select:focus {
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

/* Users Table */
.users-table-container {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  overflow-x: auto;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}

.users-table th {
  text-align: left;
  padding: 16px 20px;
  font-size: 0.9rem;
  font-weight: 600;
  color: #4b5563;
  border-bottom: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.users-table td {
  padding: 18px 20px;
  border-bottom: 1px solid #f3f4f6;
  color: #374151;
  font-size: 0.95rem;
  vertical-align: middle;
}

.users-table tr:last-child td {
  border-bottom: none;
}

.users-table tr:hover {
  background-color: #f9fafb;
}

.user-col {
  width: 30%;
}

.actions-col {
  width: 120px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 500;
  color: #111827;
  margin-bottom: 4px;
  line-height: 1.4;
}

.user-email {
  font-size: 0.85rem;
  color: #6b7280;
}

/* New Registration Indicators */
.new-badge {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  font-size: 0.6rem;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  margin-left: 8px;
  white-space: nowrap;
}

.user-role {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
  text-transform: capitalize;
}

.user-role.super_admin {
  background-color: #fef3c7;
  color: #b45309;
}

.user-role.teacher {
  background-color: #d1fae5;
  color: #047857;
}

.user-role.student {
  background-color: #dbeafe;
  color: #1d4ed8;
}

.user-status {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
}

.user-status.active {
  background-color: #ecfdf5;
  color: #10b981;
}

.user-status.inactive {
  background-color: #fef2f2;
  color: #ef4444;
}

.actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
  color: white;
}

.action-btn.edit {
  background-color: #3b82f6;
}

.action-btn.edit:hover {
  background-color: #2563eb;
}

.action-btn.delete {
  background-color: #ef4444;
}

.action-btn.delete:hover {
  background-color: #dc2626;
}

.action-btn.approve {
  background: #10b981;
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.2s;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-btn.approve:hover {
  background: #059669;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
}

.pagination-info {
  font-size: 0.9rem;
  color: #6b7280;
}

.pagination-controls {
  display: flex;
  gap: 5px;
  align-items: center;
}

.pagination-btn {
  min-width: 32px;
  height: 32px;
  border: 1px solid #e5e7eb;
  background-color: white;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #4b5563;
  font-size: 0.9rem;
}

.pagination-btn:hover:not(:disabled) {
  border-color: #8b5cf6;
  color: #8b5cf6;
}

.pagination-btn:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

.pagination-btn.active {
  background-color: #8b5cf6;
  border-color: #8b5cf6;
  color: white;
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
}

.modal {
  background-color: white;
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.modal-header {
  padding: 16px 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 600;
  color: #111827;
}

.modal-close {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background-color: #f3f4f6;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s ease;
}

.modal-close:hover {
  background-color: #e5e7eb;
  color: #4b5563;
}

.modal-body {
  padding: 20px;
}

/* Form Styles */
.form-group {
  margin-bottom: 16px;
}

.form-row {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
}

.form-row .form-group {
  flex: 1;
  margin-bottom: 0;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-size: 0.9rem;
  font-weight: 500;
  color: #4b5563;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.95rem;
  color: #1f2937;
  outline: none;
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group select:focus {
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

/* New UI Elements */
.delete-modal {
  max-width: 450px;
}

.delete-message {
  font-size: 1rem;
  color: #4b5563;
  line-height: 1.5;
  margin-bottom: 20px;
}

.btn-danger {
  background-color: #ef4444;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-danger:hover {
  background-color: #dc2626;
  transform: translateY(-2px);
}

.toast-message {
  position: fixed;
  bottom: 20px;
  right: 20px;
  padding: 12px 16px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 10px;
  animation: slideIn 0.3s ease, fadeOut 0.3s ease 2.7s forwards;
  z-index: 2000;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.toast-message.success {
  background-color: #ecfdf5;
  border-left: 4px solid #10b981;
  color: #065f46;
}

.toast-message.error {
  background-color: #fef2f2;
  border-left: 4px solid #ef4444;
  color: #b91c1c;
}

.toast-message i {
  font-size: 1.2rem;
}

.help-text {
  display: block;
  font-size: 0.8rem;
  color: #6b7280;
  margin-top: 4px;
}

@keyframes slideIn {
  from { transform: translateX(100%); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}

@keyframes fadeOut {
  from { opacity: 1; }
  to { opacity: 0; }
}

/* Responsive styles */
@media (max-width: 991px) {
  .user-management {
    padding: 20px;
  }
  
  .filters {
    flex-direction: column;
    gap: 10px;
  }
  
  .filter-group, 
  .filter-group select {
    width: 100%;
  }
  
  .form-row {
    flex-direction: column;
    gap: 16px;
  }
  
  .user-col {
    width: auto;
  }
}

@media (max-width: 768px) {
  .user-management {
    padding: 16px;
  }
  
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .btn-primary {
    width: 100%;
    justify-content: center;
  }
  
  /* Mobile styles for new registration indicators */
  .new-badge {
    font-size: 0.55rem;
    padding: 1px 4px;
    margin-left: 6px;
  }
  
  .users-table th:nth-child(4),
  .users-table td:nth-child(4) {
    display: none;
  }
  
  .users-table {
    min-width: 600px;
  }
}

@media (max-width: 576px) {
  .user-management {
    padding: 12px;
  }
  
  .users-table th:nth-child(5),
  .users-table td:nth-child(5) {
    display: none;
  }
  
  .users-table {
    min-width: 500px;
  }
  
  .actions-col {
    min-width: 80px;
  }
  
  .pagination {
    flex-direction: column;
    gap: 16px;
  }
  
  .action-btn {
    width: 28px;
    height: 28px;
  }
}

.btn-reset {
  padding: 6px 12px;
  font-size: 0.95rem;
  border-radius: 6px;
  height: 32px;
}
</style>