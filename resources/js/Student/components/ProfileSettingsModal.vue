<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h3>Profile Settings</h3>
        <button class="close-button" @click="$emit('close')">&times;</button>
      </div>
      
      <div class="modal-body">
        <!-- Profile Image Upload Section -->
        <div class="image-upload-section">
          <div 
            class="profile-image-container"
            :class="{ 'drag-over': isDragging }"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
          >
            <img 
              v-if="imagePreview || userData.avatar" 
              :src="imagePreview || userData.avatar" 
              :key="imagePreview || userData.avatar"
              alt="Profile Image"
              class="profile-image"
            >
            <div v-else class="no-image">
              <i class="fas fa-user"></i>
            </div>
            <div class="image-overlay">
              <i class="fas fa-camera"></i>
              <span>Change Photo</span>
            </div>
          </div>
          
          <div class="upload-options">
            <label class="upload-btn">
              <i class="fas fa-upload"></i> 
              Upload Image
              <input 
                type="file" 
                ref="fileInput"
                @change="handleFileSelect" 
                accept="image/*" 
                class="hidden-input"
              >
            </label>
            <button 
              v-if="imagePreview || userData.avatar" 
              class="delete-btn" 
              @click="removeImage"
            >
              <i class="fas fa-trash"></i> Remove
            </button>
          </div>
          
          <p class="upload-info">Recommended: Square image, at least 200x200 pixels</p>
        </div>
        
        <!-- Profile Information Section -->
        <div class="form-section">
          <h4>Personal Information</h4>
          
          <div class="form-group">
            <label for="profile-name">Full Name</label>
            <input 
              type="text" 
              id="profile-name" 
              v-model="userData.name" 
              class="form-input"
              placeholder="Your full name"
            >
          </div>
          
          <div class="form-group">
            <label for="profile-email">Email Address</label>
            <input 
              type="email" 
              id="profile-email" 
              v-model="userData.email" 
              class="form-input"
              placeholder="your.email@example.com"
              disabled
            >
            <small class="form-help">Email cannot be changed. Contact administrator for assistance.</small>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button class="btn btn-cancel" @click="$emit('close')" :disabled="loading">Cancel</button>
        <button class="btn btn-save" @click="saveChanges" :disabled="loading">
          <i v-if="loading" class="fas fa-spinner fa-spin me-1"></i>
          {{ loading ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'update:user']);

// Create a copy of user data to avoid direct mutation
const userData = reactive({
  name: props.user.name || '',
  email: props.user.email || '',
  avatar: props.user.avatar || null
});

// Image handling
const imagePreview = ref(null);
const isDragging = ref(false);
const fileInput = ref(null);
const selectedFile = ref(null);
const loading = ref(false);

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    processSelectedFile(file);
  }
};

const handleDrop = (event) => {
  isDragging.value = false;
  
  if (event.dataTransfer.files.length) {
    const file = event.dataTransfer.files[0];
    if (file.type.startsWith('image/')) {
      processSelectedFile(file);
    }
  }
};

const processSelectedFile = (file) => {
  // Validate file size (5MB max)
  if (file.size > 5 * 1024 * 1024) {
    return;
  }
  
  // Create object URL for preview
  imagePreview.value = URL.createObjectURL(file);
  selectedFile.value = file;
};

const removeImage = async () => {
  try {
    // If there's an existing avatar, remove it from server
    if (userData.avatar && !imagePreview.value) {
      await axios.delete('/api/student/profile/remove-image');
    }
    
    // Clear preview and selected file
    imagePreview.value = null;
    selectedFile.value = null;
    userData.avatar = null;
  
    // Clear file input
    if (fileInput.value) {
      fileInput.value.value = '';
    }
  } catch (error) {
    console.error('Failed to remove image:', error);
  }
};

const saveChanges = async () => {
  loading.value = true;
  
  try {
    // First, update profile information (name only for students)
    const profileResponse = await axios.put('/api/student/profile', {
      name: userData.name
    });
    
    // If there's a new image to upload
    if (selectedFile.value) {
      const formData = new FormData();
      formData.append('profile_image', selectedFile.value);
      
      const imageResponse = await axios.post('/api/student/profile/upload-image', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      
      // Update avatar URL with the new uploaded image and add cache-busting
      const avatarUrl = imageResponse.data.avatar_url;
      const cacheBustingUrl = avatarUrl + '?t=' + Date.now();
      userData.avatar = cacheBustingUrl;
    }
    
    // Update the user data with response
    const updatedUser = profileResponse.data.user;
    
    // If we uploaded a new image, make sure the avatar URL is included
    if (selectedFile.value && userData.avatar) {
      updatedUser.avatar = userData.avatar;
    }
  
    // Emit the updated user data to parent component
    emit('update:user', updatedUser);
    
    // Close modal immediately after successful save
    emit('close');
    
  } catch (error) {
    console.error('❌ Error updating profile:', error);
    // Show error in console but don't show toast notification
    console.error('Failed to update profile:', error.response?.data?.message || error.message);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
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

.modal-content {
  background-color: white;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: #111827;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #9ca3af;
  cursor: pointer;
  transition: color 0.2s;
  line-height: 1;
}

.close-button:hover {
  color: #6b7280;
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  padding: 20px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Image upload styles */
.image-upload-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 24px;
}

.profile-image-container {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  overflow: hidden;
  position: relative;
  margin-bottom: 16px;
  background-color: #f3f4f6;
  border: 3px solid #e5e7eb;
  transition: all 0.3s ease;
  cursor: pointer;
}

.profile-image-container:hover .image-overlay {
  opacity: 1;
}

.drag-over {
  border-color: #6a5acd;
  box-shadow: 0 0 0 3px rgba(106, 90, 205, 0.2);
}

.profile-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  color: #9ca3af;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.image-overlay i {
  font-size: 2rem;
  margin-bottom: 8px;
}

.upload-options {
  display: flex;
  gap: 10px;
  margin-bottom: 8px;
}

.hidden-input {
  display: none;
}

.upload-btn {
  background-color: #6a5acd;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 8px 16px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.upload-btn:hover {
  background-color: #5a4fcf;
}

.delete-btn {
  background-color: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 8px 16px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.delete-btn:hover {
  background-color: #dc2626;
}

.upload-info {
  font-size: 0.85rem;
  color: #6b7280;
  margin-top: 4px;
  text-align: center;
}

/* Form styles */
.form-section {
  margin-bottom: 24px;
}

.form-section h4 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #374151;
  margin: 0 0 16px 0;
  padding-bottom: 8px;
  border-bottom: 1px solid #e5e7eb;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
  color: #374151;
}

.form-input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.95rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #6a5acd;
  box-shadow: 0 0 0 3px rgba(106, 90, 205, 0.1);
}

.form-input:disabled {
  background-color: #f3f4f6;
  cursor: not-allowed;
}

.form-help {
  display: block;
  margin-top: 4px;
  font-size: 0.8rem;
  color: #6b7280;
}

/* Button styles */
.btn {
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel {
  background-color: #f3f4f6;
  color: #4b5563;
  border: 1px solid #d1d5db;
}

.btn-cancel:hover {
  background-color: #e5e7eb;
}

.btn-save {
  background-color: #6a5acd;
  color: white;
  border: 1px solid #6a5acd;
}

.btn-save:hover {
  background-color: #5a4fcf;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style> 