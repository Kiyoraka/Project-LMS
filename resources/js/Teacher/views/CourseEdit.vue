<template>
  <div class="course-edit-container">
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
      </div>
      <p>Loading course details...</p>
    </div>
    
    <div v-else-if="course" class="course-edit">
            <!-- Simple Header -->
      <div class="simple-header">
        <div class="header-content">
          <div class="header-left">
            <h1 class="page-title">Edit Course</h1>
            <p class="page-subtitle">{{ course.title }}</p>
          </div>
          
          <div class="header-actions">
            <router-link 
              :to="{ name: 'teacher.courses.view', params: { id: course.id }}" 
              class="btn btn-secondary"
            >
              <i class="fas fa-eye"></i>
              View Course
            </router-link>
            <router-link :to="{ name: 'teacher.courses' }" class="btn btn-outline">
              <i class="fas fa-arrow-left"></i>
              Back to Courses
            </router-link>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <form @submit.prevent="saveCourse" class="edit-form">
          <!-- Basic Information Section -->
          <div class="form-section">
            <div class="section-header">
              <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
            </div>
            
            <div class="form-grid">
              <div class="form-group full-width">
                <label for="title">Course Title *</label>
                <input 
                  type="text" 
                  id="title"
                  v-model="courseForm.title"
                  required
                  class="form-input"
                  placeholder="Enter course title"
                >
              </div>
              
              <div class="form-group full-width">
                <label for="short_description">Short Description *</label>
                <textarea 
                  id="short_description"
                  v-model="courseForm.short_description"
                  rows="3"
                  required
                  class="form-input"
                  placeholder="Brief description of the course"
                ></textarea>
              </div>
              
              <div class="form-group full-width">
                <label for="description">Full Description</label>
                <textarea 
                  id="description"
                  v-model="courseForm.description"
                  rows="5"
                  class="form-input"
                  placeholder="Detailed course description, learning objectives, etc."
                ></textarea>
              </div>
              
              <div class="form-group">
                <label for="level">Course Level *</label>
                <select id="level" v-model="courseForm.level" required class="form-input">
                  <option value="">Select Level</option>
                  <option value="beginner">Beginner</option>
                  <option value="intermediate">Intermediate</option>
                  <option value="advanced">Advanced</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="status">Course Status *</label>
                <select id="status" v-model="courseForm.status" required class="form-input">
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="archived">Archived</option>
                </select>
              </div>
            </div>

            <!-- Course Image Upload -->
            <div class="form-group full-width">
              <label for="course-image">Course Image</label>
              <div class="image-upload-area">
                <input 
                  type="file" 
                  id="course-image"
                  ref="courseImageInput"
                  @change="handleImageUpload"
                  accept="image/*"
                  class="file-input"
                  style="display: none;"
                >
                
                <div v-if="imagePreview && imagePreview !== 'loading'" class="image-preview">
                  <img :src="imagePreview" alt="Course preview" class="preview-image">
                  <div class="image-actions">
                    <button type="button" @click="$refs.courseImageInput.click()" class="btn btn-sm btn-secondary">
                      <i class="fas fa-camera"></i>
                      Change Image
                    </button>
                    <button type="button" @click="removeImage" class="btn btn-sm btn-danger">
                      <i class="fas fa-times"></i>
                      Remove
                    </button>
                  </div>
                </div>
                
                <div v-else-if="imagePreview === 'loading'" class="image-loading">
                  <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                  </div>
                  <p>Processing image...</p>
                </div>
                
                <div v-else-if="course.thumbnail && !removeCurrentImageFlag" class="current-image">
                  <img :src="`/storage/${course.thumbnail}`" alt="Current course image" class="preview-image">
                  <div class="image-actions">
                    <button type="button" @click="$refs.courseImageInput.click()" class="btn btn-sm btn-secondary">
                      <i class="fas fa-camera"></i>
                      Change Image
                    </button>
                    <button type="button" @click="removeCurrentImage" class="btn btn-sm btn-danger">
                      <i class="fas fa-times"></i>
                      Remove
                    </button>
                  </div>
                </div>
                
                <div v-else class="upload-placeholder" @click="$refs.courseImageInput.click()">
                  <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                  </div>
                  <h4>Upload Course Image</h4>
                  <p>Click here to select an image file</p>
                  <small>Recommended: 800x600px, max 5MB (JPG, PNG, GIF)</small>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Video Chapters Section -->
          <div class="form-section">
            <div class="section-header">
              <h3><i class="fas fa-play-circle"></i> Video Chapters</h3>
              <button type="button" @click="addChapter" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Chapter
              </button>
            </div>
            
            <div class="chapters-container">
              <div v-for="(chapter, index) in courseForm.chapters" :key="index" class="chapter-card">
                <div class="chapter-header">
                  <h4 class="chapter-title">
                    <span class="chapter-number">{{ index + 1 }}</span>
                    Chapter {{ index + 1 }}
                  </h4>
                  <button 
                    type="button" 
                    @click="removeChapter(index)"
                    class="remove-chapter-btn"
                    v-if="courseForm.chapters.length > 1"
                    title="Remove chapter"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
                
                <div class="chapter-content">
                  <div class="form-group">
                    <label :for="`chapter-title-${index}`">Chapter Title *</label>
                    <input 
                      type="text" 
                      :id="`chapter-title-${index}`"
                      v-model="chapter.title"
                      placeholder="Enter chapter title"
                      required
                      class="form-input"
                    >
                  </div>
                  
                  <div class="form-group">
                    <label :for="`chapter-description-${index}`">Chapter Description</label>
                    <textarea 
                      :id="`chapter-description-${index}`"
                      v-model="chapter.description"
                      placeholder="Brief description of this chapter"
                      rows="2"
                      class="form-input"
                    ></textarea>
                  </div>
                  
                  <div class="form-row">
                    <div class="form-group flex-2">
                      <label :for="`chapter-video-${index}`">YouTube Video URL *</label>
                      <input 
                        type="url" 
                        :id="`chapter-video-${index}`"
                        v-model="chapter.video_url"
                        placeholder="https://www.youtube.com/watch?v=..."
                        required
                        class="form-input"
                      >
                      <small class="form-help">
                        <i class="fas fa-shield-alt"></i>
                        Use unlisted YouTube videos for better security
                      </small>
                    </div>
                    
                    <div class="form-group">
                      <label :for="`chapter-duration-${index}`">Duration (minutes)</label>
                      <input 
                        type="number" 
                        :id="`chapter-duration-${index}`"
                        v-model="chapter.duration_minutes"
                        placeholder="0"
                        min="0"
                        class="form-input"
                      >
                    </div>
                  </div>
                </div>
              </div>
              
              <div v-if="courseForm.chapters.length === 0" class="empty-chapters">
                <div class="empty-icon">
                  <i class="fas fa-video"></i>
                </div>
                <h4>No chapters yet</h4>
                <p>Add your first video chapter to get started</p>
                <button type="button" @click="addChapter" class="btn btn-primary">
                  <i class="fas fa-plus"></i> Add First Chapter
                </button>
              </div>
            </div>
          </div>
          
          <!-- Form Actions -->
          <div class="form-actions">
            <div class="actions-left">
              <span class="save-info">
                <i class="fas fa-info-circle"></i>
                Changes are saved automatically when you click "Save Changes"
              </span>
            </div>
            <div class="actions-right">
              <router-link :to="{ name: 'teacher.courses.view', params: { id: course.id }}" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Cancel
              </router-link>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <i v-if="saving" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-save"></i>
                {{ saving ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
    
    <div v-else class="error-state">
      <div class="error-content">
        <i class="fas fa-exclamation-triangle"></i>
        <h3>Course Not Found</h3>
        <p>The course you're trying to edit doesn't exist or you don't have permission to edit it.</p>
        <router-link :to="{ name: 'teacher.courses' }" class="btn btn-primary">
          <i class="fas fa-arrow-left"></i>
          Back to Courses
        </router-link>
      </div>
    </div>
  </div>

  <!-- Toast Notifications -->
  <Toast 
    ref="toastRef"
    :show="toast.show"
    :type="toast.type"
    :title="toast.title"
    :message="toast.message"
    @hide="toast.show = false"
  />
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Toast from '../components/Toast.vue';

const route = useRoute();
const router = useRouter();
const courseId = route.params.id;

const loading = ref(true);
const saving = ref(false);
const course = ref(null);

// Toast notifications
const toast = ref({
  show: false,
  type: 'info',
  title: '',
  message: ''
});

const showToast = (type, title, message) => {
  toast.value = {
    show: true,
    type,
    title,
    message
  };
};

// Image upload state
const imagePreview = ref(null);
const selectedImage = ref(null);
const removeCurrentImageFlag = ref(false);

const courseForm = reactive({
  title: '',
  short_description: '',
  description: '',
  level: '',
  status: 'draft',
  chapters: []
});

// Computed properties
const getTotalDuration = computed(() => {
  return courseForm.chapters.reduce((total, chapter) => {
    return total + (parseInt(chapter.duration_minutes) || 0);
  }, 0);
});

async function fetchCourse() {
  try {
    const response = await axios.get(`/api/teacher/courses/${courseId}`);
    course.value = response.data;
    
    // Populate form with course data
    courseForm.title = course.value.title;
    courseForm.short_description = course.value.short_description;
    courseForm.description = course.value.description || '';
    courseForm.level = course.value.level;
    courseForm.status = course.value.status;
    
    // Convert modules/lessons to chapters format
    courseForm.chapters = [];
    if (course.value.modules && course.value.modules.length > 0) {
      course.value.modules.forEach(module => {
        if (module.lessons && module.lessons.length > 0) {
          module.lessons.forEach(lesson => {
            courseForm.chapters.push({
              title: lesson.title,
              description: lesson.description || '',
              video_url: lesson.video_url || '',
              duration_minutes: lesson.duration_minutes || 0
            });
          });
        }
      });
    }
    
    // If no chapters found, add an empty one
    if (courseForm.chapters.length === 0) {
      courseForm.chapters.push({
        title: '',
        description: '',
        video_url: '',
        duration_minutes: 0
      });
    }
  } catch (error) {
    console.error('Error fetching course:', error);
    course.value = null;
  } finally {
    loading.value = false;
  }
}

async function saveCourse() {
  if (saving.value) return;
  
  // Validate chapters
  const validChapters = courseForm.chapters.filter(chapter => 
    chapter.title.trim() && chapter.video_url.trim()
  );
  
  if (validChapters.length === 0) {
    showToast('warning', 'Validation Error', 'Please add at least one chapter with a title and video URL.');
    return;
  }
  
  saving.value = true;
  
  try {
    // Create FormData for file upload
    const formData = new FormData();
    
    // Add course data
    formData.append('title', courseForm.title);
    formData.append('short_description', courseForm.short_description);
    formData.append('description', courseForm.description || '');
    formData.append('level', courseForm.level);
    formData.append('status', courseForm.status);
    
    // Add chapters data
    formData.append('chapters', JSON.stringify(validChapters));
    
    // Handle image
    if (selectedImage.value) {
      formData.append('thumbnail', selectedImage.value);
    } else if (removeCurrentImageFlag.value) {
      formData.append('remove_thumbnail', 'true');
    }
    
    const response = await axios.post(`/api/teacher/courses/${courseId}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'X-HTTP-Method-Override': 'PUT'
      },
    });
    
    if (response.data.success) {
      showToast('success', 'Success', 'Course updated successfully!');
      // Navigate back to course view after a short delay
      setTimeout(() => {
        router.push({ name: 'teacher.courses.view', params: { id: courseId } });
      }, 1500);
    } else {
      showToast('error', 'Error', 'Failed to update course');
    }
  } catch (error) {
    console.error('Error saving course:', error);
    
    if (error.response && error.response.data && error.response.data.errors) {
      const errorMessages = Object.values(error.response.data.errors).flat().join(', ');
      showToast('error', 'Validation Errors', errorMessages);
    } else {
      showToast('error', 'Error', 'An error occurred while saving the course');
    }
  } finally {
    saving.value = false;
  }
}

function addChapter() {
  courseForm.chapters.push({
    title: '',
    description: '',
    video_url: '',
    duration_minutes: 0
  });
}

function removeChapter(index) {
  courseForm.chapters.splice(index, 1);
}

// Status helper functions
function getStatusClass(status) {
  return `status-${status}`;
}

function getStatusIcon(status) {
  const icons = {
    published: 'fas fa-check-circle',
    draft: 'fas fa-edit',
    archived: 'fas fa-archive'
  };
  return icons[status] || 'fas fa-circle';
}

function formatStatus(status) {
  return status.charAt(0).toUpperCase() + status.slice(1);
}

// Image handling functions
function handleImageUpload(event) {
  const file = event.target.files[0];
  if (!file) return;
  
  // Validate file size (5MB max)
  if (file.size > 5 * 1024 * 1024) {
    showToast('warning', 'File Too Large', 'File size must be less than 5MB');
    event.target.value = ''; // Clear the input
    return;
  }
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
    showToast('warning', 'Invalid File Type', 'Please select an image file (JPG, PNG, GIF)');
    event.target.value = ''; // Clear the input
    return;
  }
  
  selectedImage.value = file;
  removeCurrentImageFlag.value = false;
  
  // Show loading state briefly
  imagePreview.value = 'loading';
  
  // Create preview
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
    showToast('success', 'Image Uploaded', 'Course image has been updated successfully');
  };
  reader.onerror = () => {
    imagePreview.value = null;
    selectedImage.value = null;
    showToast('error', 'Upload Error', 'Failed to process the image file');
  };
  reader.readAsDataURL(file);
}

function removeImage() {
  imagePreview.value = null;
  selectedImage.value = null;
  const input = document.getElementById('course-image');
  if (input) input.value = '';
}

function removeCurrentImage() {
  removeCurrentImageFlag.value = true;
  imagePreview.value = null;
  selectedImage.value = null;
}

onMounted(() => {
  fetchCourse();
});
</script>

<style scoped>
.course-edit-container {
  width: 100%;
  min-height: 100vh;
  background-color: #f8fafc;
}

/* Loading & Error States */
.loading-state, .error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  text-align: center;
}

.loading-spinner {
  font-size: 2rem;
  color: #0077b6;
  margin-bottom: 20px;
}

.error-content {
  max-width: 400px;
  padding: 40px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.error-content i {
  font-size: 3rem;
  color: #f59e0b;
  margin-bottom: 20px;
}

.error-content h3 {
  margin-bottom: 10px;
  color: #374151;
}

.error-content p {
  color: #6b7280;
  margin-bottom: 20px;
}

/* Simple Header */
.simple-header {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 20px 0;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  flex: 1;
}

.page-title {
  font-size: 1.8rem;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: #1f2937;
}

.page-subtitle {
  font-size: 1rem;
  color: #6b7280;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
  flex-shrink: 0;
}

/* Main Content */
.main-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
}

.edit-form {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

/* Form Sections */
.form-section {
  padding: 30px;
  border-bottom: 1px solid #f1f5f9;
}

.form-section:last-child {
  border-bottom: none;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.section-header h3 {
  font-size: 1.3rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.section-header h3 i {
  color: #0077b6;
}

/* Form Layout */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  align-items: start;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-size: 0.9rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 5px;
}

.form-input {
  padding: 12px 16px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s ease;
  background: white;
}

.form-input:focus {
  outline: none;
  border-color: #0077b6;
  box-shadow: 0 0 0 3px rgba(0, 119, 182, 0.1);
}

.form-input:invalid {
  border-color: #ef4444;
}

/* Chapters */
.chapters-container {
  space-y: 20px;
}

.chapter-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 25px;
  margin-bottom: 20px;
  transition: all 0.2s ease;
}

.chapter-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.chapter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.chapter-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.chapter-number {
  background: #0077b6;
  color: white;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 700;
}

.remove-chapter-btn {
  background: #fee2e2;
  color: #dc2626;
  border: none;
  border-radius: 6px;
  padding: 8px 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.9rem;
}

.remove-chapter-btn:hover {
  background: #fecaca;
  transform: translateY(-1px);
}

.chapter-content .form-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
  align-items: start;
}

.form-help {
  font-size: 0.8rem;
  color: #6b7280;
  margin-top: 6px;
  display: flex;
  align-items: center;
  gap: 5px;
}

.form-help i {
  color: #0077b6;
}

/* Empty State */
.empty-chapters {
  text-align: center;
  padding: 60px 20px;
  background: #f8fafc;
  border: 2px dashed #cbd5e1;
  border-radius: 12px;
}

.empty-icon {
  font-size: 3rem;
  color: #94a3b8;
  margin-bottom: 20px;
}

.empty-chapters h4 {
  font-size: 1.2rem;
  color: #475569;
  margin-bottom: 10px;
}

.empty-chapters p {
  color: #64748b;
  margin-bottom: 20px;
}

/* Image Upload */
.image-upload-area {
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  overflow: hidden;
  transition: border-color 0.2s;
}

.image-upload-area:hover {
  border-color: #9ca3af;
}

.upload-placeholder {
  padding: 40px 20px;
  text-align: center;
  cursor: pointer;
  background: #f9fafb;
  transition: background-color 0.2s;
}

.upload-placeholder:hover {
  background: #f3f4f6;
}

.upload-icon {
  font-size: 2.5rem;
  color: #9ca3af;
  margin-bottom: 15px;
}

.upload-placeholder h4 {
  margin: 0 0 8px 0;
  color: #374151;
  font-size: 1.1rem;
  font-weight: 600;
}

.upload-placeholder p {
  margin: 0 0 8px 0;
  color: #6b7280;
  font-size: 0.9rem;
}

.upload-placeholder small {
  color: #9ca3af;
  font-size: 0.8rem;
}

.image-preview, .current-image {
  position: relative;
  background: #f9fafb;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
  transition: all 0.2s ease;
}

.image-preview:hover, .current-image:hover {
  border-color: #d1d5db;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.image-preview::before, .current-image::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, transparent 60%, rgba(0, 0, 0, 0.1) 100%);
  opacity: 0;
  transition: opacity 0.2s ease;
  pointer-events: none;
}

.image-preview:hover::before, .current-image:hover::before {
  opacity: 1;
}

.preview-image {
  width: 100%;
  max-height: 300px;
  min-height: 200px;
  object-fit: contain;
  display: block;
  border-radius: 6px;
  background: #f8f9fa;
}

.image-actions {
  position: absolute;
  top: 10px;
  right: 10px;
  display: flex;
  gap: 8px;
  flex-direction: column;
}

.image-actions .btn {
  opacity: 1;
  background: rgba(255, 255, 255, 0.95);
  border: 2px solid rgba(0, 0, 0, 0.2);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  font-weight: 700;
  min-width: 110px;
  justify-content: center;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
}

.image-actions .btn:hover {
  background: rgba(255, 255, 255, 1);
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
}

/* Form Actions */
.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 25px 30px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.actions-left .save-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  color: #64748b;
}

.actions-right {
  display: flex;
  gap: 12px;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 20px;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
  border: none;
}

.btn-sm {
  padding: 8px 16px;
  font-size: 0.85rem;
}

.btn-primary {
  background: #0077b6;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #2563eb;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 119, 182, 0.3);
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover {
  background: #4b5563;
}

.btn-outline {
  background: transparent;
  color: #6b7280;
  border: 1px solid #d1d5db;
}

.btn-outline:hover {
  background: #f9fafb;
  border-color: #9ca3af;
  color: #374151;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

.btn-danger {
  background: #dc2626;
  color: white;
  border: 1px solid #dc2626;
}

.btn-danger:hover:not(:disabled) {
  background: #b91c1c;
  box-shadow: 0 3px 8px rgba(220, 38, 38, 0.3);
}

/* Special styling for image action buttons */
.image-actions .btn-secondary {
  background: #059669;
  border-color: #059669;
  color: white;
}

.image-actions .btn-secondary:hover {
  background: #047857;
  border-color: #047857;
}

.image-actions .btn-danger {
  background: #ef4444 !important;
  border: 2px solid #dc2626 !important;
  color: white !important;
  font-weight: 700 !important;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
  box-shadow: 0 3px 8px rgba(239, 68, 68, 0.3) !important;
}

.image-actions .btn-danger:hover {
  background: #dc2626 !important;
  border-color: #b91c1c !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.5) !important;
}

.image-actions .btn-danger i {
  color: white !important;
  font-weight: bold !important;
}

/* Image loading state */
.image-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  color: #6b7280;
}

.image-loading .loading-spinner {
  font-size: 2rem;
  color: #0077b6;
  margin-bottom: 15px;
}

.image-loading p {
  margin: 0;
  font-size: 0.9rem;
  font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
  }

  .page-title {
    font-size: 1.5rem;
  }

  .header-actions {
    width: 100%;
    justify-content: flex-start;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .chapter-content .form-row {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }

  .actions-left,
  .actions-right {
    width: 100%;
    justify-content: center;
  }

  .actions-right {
    flex-direction: column;
    gap: 10px;
  }

  /* Mobile image actions */
  .image-actions {
    flex-direction: row;
    top: auto;
    bottom: 10px;
    left: 10px;
    right: 10px;
    gap: 10px;
  }

  .image-actions .btn {
    min-width: auto;
    flex: 1;
    font-size: 0.8rem;
    padding: 6px 12px;
  }

  .image-actions .btn-danger {
    background: #ef4444 !important;
    border-color: #dc2626 !important;
    color: white !important;
  }

  .preview-image {
    max-height: 250px;
    min-height: 150px;
  }
}

@media (max-width: 480px) {
  .simple-header {
    padding: 15px 0;
  }

  .main-content {
    padding: 20px 15px;
  }

  .form-section {
    padding: 20px 15px;
  }

  .chapter-card {
    padding: 20px 15px;
  }
}
</style> 