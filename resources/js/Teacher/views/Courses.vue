<template>
  <div class="courses-container">
    <!-- Header Section -->
    <div class="courses-header">
      <div class="header-content">
        <h1>Course Management</h1>
        <p>Create, manage, and track your courses and student progress</p>
      </div>
    </div>
    
    <!-- Stats Grid -->
    <div class="stats-grid">
      <!-- Total Courses Card -->
      <div class="stat-card bg-primary">
        <div class="stat-icon">
          <i class="fas fa-book"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ loading ? '...' : totalCourses }}</h3>
          <p class="stat-label">Total Courses</p>
        </div>
          </div>
          
      <!-- Published Courses Card -->
      <div class="stat-card bg-success">
        <div class="stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ loading ? '...' : publishedCourses }}</h3>
          <p class="stat-label">Published Courses</p>
        </div>
          </div>
          
      <!-- Total Students Card -->
      <div class="stat-card bg-warning">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ loading ? '...' : totalStudents }}</h3>
          <p class="stat-label">Total Students</p>
        </div>
      </div>
      
      <!-- Average Completion Card -->
      <div class="stat-card bg-info">
        <div class="stat-icon">
          <i class="fas fa-chart-bar"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ loading ? '...' : averageCompletion + '%' }}</h3>
          <p class="stat-label">Avg Completion</p>
        </div>
      </div>
    </div>
    
    <!-- Course Controls -->
    <div class="course-controls">
      <div class="course-header">
        <h4>My Courses</h4>
        <div class="header-actions">
          <div class="search-box">
            <input 
              type="text" 
              class="search-input" 
              placeholder="Search courses..." 
              v-model="filters.search"
            >
              <i class="fas fa-search"></i>
            </div>
          <button @click="showCreateModal = true" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Create Course
          </button>
          </div>
        </div>
      
      <!-- Tabs for Different Course Statuses -->
      <ul class="nav-tabs">
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: filters.status === '' }"
            href="#" 
            @click.prevent="setStatusFilter('')"
          >
            <i class="fas fa-th me-1"></i>
            All Courses
          </a>
        </li>
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: filters.status === 'published' }"
            href="#" 
            @click.prevent="setStatusFilter('published')"
          >
            <i class="fas fa-check-circle me-1"></i>
            Published
          </a>
        </li>
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: filters.status === 'draft' }"
            href="#" 
            @click.prevent="setStatusFilter('draft')"
          >
            <i class="fas fa-edit me-1"></i>
            Draft
          </a>
        </li>
      </ul>
      </div>

    <!-- Error Message -->
    <div v-if="error" class="alert alert-danger" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>
      {{ error }}
      <button @click="fetchCourses" class="btn btn-sm btn-outline-danger ms-3">
        Try Again
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !error" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">Loading your courses...</p>
    </div>
    
    <!-- Courses Grid -->
    <div v-if="!loading && !error">
      <div class="courses-grid" v-if="paginatedCourses.length > 0">
        <div 
          v-for="course in paginatedCourses" 
          :key="course.id"
          class="course-card"
        >
        <div class="course-header">
            <img 
              :src="course.thumbnail ? `/storage/${course.thumbnail}` : '/assets/images/course-placeholder.jpg'" 
              class="course-thumbnail" 
              alt="Course"
            >
            <span class="badge level-badge">{{ course.level || 'All Levels' }}</span>
            <span class="status-badge" :class="getStatusClass(course.status)">
              {{ course.status.charAt(0).toUpperCase() + course.status.slice(1) }}
            </span>
          </div>
        <div class="course-body">
            <h5 class="course-title">{{ course.title }}</h5>
            <p class="course-description">{{ course.short_description || 'No description available' }}</p>
          
          <div class="course-meta">
            <div class="meta-item">
                <i class="fas fa-users me-1"></i>
                <span>{{ course.enrollments_count || 0 }} Students</span>
            </div>
            <div class="meta-item">
                <i class="fas fa-folder me-1"></i>
                <span>{{ course.modules_count || 0 }} Modules</span>
            </div>
            <div class="meta-item">
                <i class="fas fa-play me-1"></i>
                <span>{{ course.lessons_count || 0 }} Lessons</span>
            </div>
          </div>
          
            <div class="progress-container">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="progress-label">Completion Rate</span>
                <span class="progress-value">{{ course.completion_rate || 0 }}%</span>
              </div>
              <div class="progress-bar">
                <div 
                  class="progress-fill" 
                  :style="{ width: (course.completion_rate || 0) + '%' }"
                ></div>
              </div>
            </div>
          </div>
          <div class="course-footer">
            <router-link 
              :to="{ name: 'teacher.courses.view', params: { id: course.id }}" 
              class="btn btn-primary btn-action"
            >
              <i class="fas fa-eye me-1"></i>
              View
            </router-link>
            <router-link 
              :to="{ name: 'teacher.courses.edit', params: { id: course.id }}" 
              class="btn btn-outline-primary btn-action"
            >
              <i class="fas fa-edit me-1"></i>
              Edit
            </router-link>
            <button @click="confirmDeleteCourse(course)" class="btn btn-danger btn-action">
              <i class="fas fa-trash me-1"></i>
              Delete
            </button>
          </div>
        </div>
      </div>
      <div v-else class="empty-state">
      <div class="empty-icon">
          <i class="fas fa-book-open"></i>
      </div>
        <h5>
          {{ filters.search || filters.status ? 'No courses match your criteria.' : 'You have no courses yet.' }}
        </h5>
        <button @click="showCreateModal = true" class="btn btn-primary mt-3">
          <i class="fas fa-plus-circle me-1"></i>
        Create Your First Course
        </button>
      </div>
    </div>
    
    <!-- Pagination -->
    <div class="pagination" v-if="filteredCourses.length > 0">
      <button class="pagination-btn" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
        <i class="fas fa-chevron-left"></i> Previous
      </button>
      <div class="page-info">
        Page {{ currentPage }} of {{ totalPages }}
      </div>
      <button class="pagination-btn" :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">
        Next <i class="fas fa-chevron-right"></i>
      </button>
    </div>

    <!-- Course Creation Modal -->
    <div v-if="showCreateModal" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Create New Course</h2>
          <button @click="closeModal" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <form @submit.prevent="submitCourse" class="course-form">
          <div class="form-section">
            <h3>Course Details</h3>
            
            <div class="form-group">
              <label for="course-title">Course Title *</label>
              <input 
                type="text" 
                id="course-title"
                v-model="courseForm.title"
                placeholder="Enter course title"
                required
              >
            </div>
            
            <div class="form-group">
              <label for="course-description">Short Description *</label>
              <textarea 
                id="course-description"
                v-model="courseForm.short_description"
                placeholder="Brief description of the course"
                rows="3"
                required
              ></textarea>
            </div>
            
            <div class="form-group">
              <label for="course-full-description">Full Description</label>
              <textarea 
                id="course-full-description"
                v-model="courseForm.description"
                placeholder="Detailed course description, learning objectives, etc."
                rows="4"
              ></textarea>
            </div>
            
            <div class="form-group">
              <label for="course-image">Course Image</label>
              <div class="image-upload-container">
                <input 
                  type="file" 
                  id="course-image"
                  ref="courseImageInput"
                  @change="handleImageUpload"
                  accept="image/*"
                  class="file-input"
                >
                <div class="image-preview" v-if="imagePreview">
                  <img :src="imagePreview" alt="Course preview" class="preview-image">
                  <button type="button" @click="removeImage" class="remove-image-btn">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div v-else class="upload-placeholder" @click="$refs.courseImageInput.click()">
                  <i class="fas fa-camera"></i>
                  <p>Click to upload course image</p>
                  <small>Recommended: 800x600px, max 5MB</small>
                </div>
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="course-level">Level *</label>
                <select id="course-level" v-model="courseForm.level" required>
                  <option value="">Select Level</option>
                  <option value="beginner">Beginner</option>
                  <option value="intermediate">Intermediate</option>
                  <option value="advanced">Advanced</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="course-status">Status *</label>
                <select id="course-status" v-model="courseForm.status" required>
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="form-section">
            <h3>Video Chapters</h3>
            
            <div v-for="(chapter, index) in courseForm.chapters" :key="index" class="chapter-item">
              <div class="chapter-header">
                <h4>Chapter {{ index + 1 }}</h4>
                <button 
                  type="button" 
                  @click="removeChapter(index)"
                  class="remove-chapter-btn"
                  v-if="courseForm.chapters.length > 1"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
              
              <div class="form-group">
                <label :for="`chapter-title-${index}`">Chapter Title *</label>
                <input 
                  type="text" 
                  :id="`chapter-title-${index}`"
                  v-model="chapter.title"
                  placeholder="Enter chapter title"
                  required
                >
              </div>
              
              <div class="form-group">
                <label :for="`chapter-description-${index}`">Chapter Description</label>
                <textarea 
                  :id="`chapter-description-${index}`"
                  v-model="chapter.description"
                  placeholder="Brief description of this chapter"
                  rows="2"
                ></textarea>
              </div>
              
              <div class="form-group">
                <label :for="`chapter-video-${index}`">Chapter Video *</label>
                <div class="video-upload-container">
                  <input
                    type="file"
                    :id="`chapter-video-${index}`"
                    :ref="`chapterVideoInput${index}`"
                    @change="handleVideoUpload($event, index)"
                    accept="video/*"
                    class="file-input"
                    required
                  >
                  <div class="video-preview" v-if="chapter.videoPreview">
                    <div class="video-info">
                      <i class="fas fa-video"></i>
                      <span>{{ chapter.videoName || 'Video selected' }}</span>
                    </div>
                    <button type="button" @click="removeVideo(index)" class="remove-video-btn">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                  <div v-else class="upload-placeholder" @click="$refs[`chapterVideoInput${index}`][0].click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click to upload chapter video</p>
                    <small>Supported formats: MP4, AVI, MOV, WebM (max 500MB)</small>
                  </div>
                </div>
              </div>
            </div>
            
            <button type="button" @click="addChapter" class="add-chapter-btn">
              <i class="fas fa-plus"></i> Add Another Chapter
            </button>
          </div>
          
          <div class="form-actions">
            <button type="button" @click="closeModal" class="btn btn-secondary">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
              <i v-if="isSubmitting" class="fas fa-spinner fa-spin"></i>
              {{ isSubmitting ? 'Creating...' : 'Create Course' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
    <div class="modal-content delete-modal" @click.stop>
      <div class="modal-header">
        <h2>Delete Course</h2>
        <button @click="closeDeleteModal" class="close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="modal-body">
        <div class="warning-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <p>Are you sure you want to delete <strong>"{{ courseToDelete?.title }}"</strong>?</p>
        <p class="warning-text">This action cannot be undone. All course content, modules, and lessons will be permanently deleted.</p>
        <p v-if="courseToDelete?.enrollments_count > 0" class="error-text">
          <i class="fas fa-exclamation-circle"></i>
          <strong>Warning:</strong> This course has {{ courseToDelete.enrollments_count }} active enrollment(s). 
          You cannot delete a course with active enrollments.
        </p>
      </div>
      
      <div class="modal-actions">
        <button @click="closeDeleteModal" class="btn btn-secondary">Cancel</button>
        <button 
          @click="deleteCourse" 
          class="btn btn-danger" 
          :disabled="isDeleting || (courseToDelete?.enrollments_count > 0)"
        >
          <i v-if="isDeleting" class="fas fa-spinner fa-spin"></i>
          <i v-else class="fas fa-trash"></i>
          {{ isDeleting ? 'Deleting...' : 'Delete Course' }}
        </button>
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
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import Toast from '../components/Toast.vue';

// Filters state
const filters = ref({
  status: '',
  sortBy: 'created_desc',
  search: ''
});

// Pagination
const currentPage = ref(1);
const perPage = 9;

// Course data
const courses = ref([]);
const totalCourses = ref(0);
const loading = ref(true);
const error = ref(null);

// Modal state
const showCreateModal = ref(false);
const isSubmitting = ref(false);
const showDeleteModal = ref(false);
const isDeleting = ref(false);
const courseToDelete = ref(null);

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

// Course form data
const courseForm = ref({
  title: '',
  short_description: '',
  description: '',
  level: '',
  status: 'draft',
  chapters: [
    {
      title: '',
      description: '',
      video_url: '',
      videoFile: null,
      videoPreview: false,
      videoName: ''
    }
  ]
});

// Computed properties for stats
const publishedCourses = computed(() => {
  return courses.value.filter(course => course.status === 'published').length;
});

const totalStudents = computed(() => {
  return courses.value.reduce((total, course) => {
    return total + (course.enrollments_count || 0);
  }, 0);
});

const averageCompletion = computed(() => {
  if (courses.value.length === 0) return 0;
  
  const totalCompletion = courses.value.reduce((total, course) => {
    return total + (course.completion_rate || 0);
  }, 0);
  
  return Math.round(totalCompletion / courses.value.length);
});

// Computed properties for filtering
const filteredCourses = computed(() => {
  let result = [...courses.value];
  
  // Apply status filter
  if (filters.value.status) {
    result = result.filter(course => course.status === filters.value.status);
  }
  
  // Apply search filter
  if (filters.value.search) {
    const searchTerm = filters.value.search.toLowerCase();
    result = result.filter(course => 
      course.title.toLowerCase().includes(searchTerm) || 
      (course.short_description && course.short_description.toLowerCase().includes(searchTerm))
    );
  }
  
  return result;
});

// Fetch courses from API
async function fetchCourses() {
  loading.value = true;
  error.value = null;
  
  try {
    const params = new URLSearchParams({
      status: filters.value.status,
      search: filters.value.search,
      sort_by: filters.value.sortBy
    });
    
    const response = await axios.get(`/api/teacher/courses?${params}`);
    
    if (response.data) {
      courses.value = response.data.data || response.data;
      totalCourses.value = response.data.total || courses.value.length;
    }
  } catch (error) {
    console.error('Error fetching courses:', error);
    if (error.response?.status === 401) {
      error.value = 'Authentication required. Please log in again.';
    } else {
      error.value = 'Failed to load your courses. Please try again later.';
    }
    courses.value = [];
    totalCourses.value = 0;
  } finally {
    loading.value = false;
  }
}

// Filter functions
const setStatusFilter = (status) => {
  filters.value.status = status;
};

// Status class function
const getStatusClass = (status) => {
  return `status-${status}`;
};

// Watch for filter changes
watch(filters, () => {
  // Auto-refetch when filters change if needed
}, { deep: true });

// Modal methods
function closeModal() {
  showCreateModal.value = false;
  resetForm();
}

function resetForm() {
  courseForm.value = {
    title: '',
    short_description: '',
    description: '',
    level: '',
    status: 'draft',
    chapters: [
      {
        title: '',
        description: '',
        video_url: '',
        videoFile: null,
        videoPreview: false,
        videoName: ''
      }
    ]
  };
  // Reset image
  imagePreview.value = null;
  selectedImage.value = null;
}

function addChapter() {
  courseForm.value.chapters.push({
    title: '',
    description: '',
    video_url: '',
    videoFile: null,
    videoPreview: false,
    videoName: ''
  });
}

function removeChapter(index) {
  if (courseForm.value.chapters.length > 1) {
    courseForm.value.chapters.splice(index, 1);
  }
}

// Image upload functions
function handleImageUpload(event) {
  const file = event.target.files[0];
  if (file) {
  // Validate file size (5MB max)
  if (file.size > 5 * 1024 * 1024) {
      showToast('error', 'File Too Large', 'File size must be less than 5MB');
    return;
  }
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
      showToast('error', 'Invalid File Type', 'Please select an image file');
    return;
  }
  
  selectedImage.value = file;
  
  // Create preview
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
  }
}

function removeImage() {
  imagePreview.value = null;
  selectedImage.value = null;
  if (document.getElementById('course-image')) {
    document.getElementById('course-image').value = '';
  }
}

// Video upload functions
function handleVideoUpload(event, index) {
  const file = event.target.files[0];
  if (file) {
    // Validate file size (500MB max)
    if (file.size > 500 * 1024 * 1024) {
      showToast('error', 'File Too Large', 'Video file size must be less than 500MB');
      return;
    }

    // Validate file type
    if (!file.type.startsWith('video/')) {
      showToast('error', 'Invalid File Type', 'Please select a video file');
      return;
    }

    courseForm.value.chapters[index].videoFile = file;
    courseForm.value.chapters[index].videoPreview = true;
    courseForm.value.chapters[index].videoName = file.name;
  }
}

function removeVideo(index) {
  courseForm.value.chapters[index].videoFile = null;
  courseForm.value.chapters[index].videoPreview = false;
  courseForm.value.chapters[index].videoName = '';

  // Reset the file input
  const input = document.getElementById(`chapter-video-${index}`);
  if (input) {
    input.value = '';
  }
}

// Form submission
async function submitCourse() {
  isSubmitting.value = true;

  try {
    const formData = new FormData();

    // Add basic course data
    formData.append('title', courseForm.value.title);
    formData.append('short_description', courseForm.value.short_description);
    formData.append('description', courseForm.value.description || '');
    formData.append('level', courseForm.value.level);
    formData.append('status', courseForm.value.status);

    // Add image if selected
    if (selectedImage.value) {
      formData.append('thumbnail', selectedImage.value);
    }

    // Add chapter data and video files
    const chaptersData = [];
    courseForm.value.chapters.forEach((chapter, index) => {
      chaptersData.push({
        title: chapter.title,
        description: chapter.description || ''
      });

      // Add video file if selected
      if (chapter.videoFile) {
        formData.append(`chapter_videos[${index}]`, chapter.videoFile);
      }
    });

    formData.append('chapters', JSON.stringify(chaptersData));

    const response = await axios.post('/api/teacher/courses', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    
    showToast('success', 'Course Created', 'Your course has been created successfully!');
      closeModal();
    await fetchCourses(); // Refresh the courses list
    
  } catch (error) {
    console.error('Error creating course:', error);
    
    let errorMessage = 'Failed to create course. Please try again.';
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat();
      errorMessage = errors.join(', ');
    }
    
    showToast('error', 'Error', errorMessage);
  } finally {
    isSubmitting.value = false;
  }
}

// Delete course functions
function confirmDeleteCourse(course) {
  courseToDelete.value = course;
  showDeleteModal.value = true;
}

function closeDeleteModal() {
  showDeleteModal.value = false;
  courseToDelete.value = null;
}

async function deleteCourse() {
  if (!courseToDelete.value) return;
  
  isDeleting.value = true;
  
  try {
    await axios.delete(`/api/teacher/courses/${courseToDelete.value.id}`);
    
    showToast('success', 'Course Deleted', 'Course has been deleted successfully.');
      closeDeleteModal();
      await fetchCourses(); // Refresh the courses list
    
  } catch (error) {
    console.error('Error deleting course:', error);
    
    let errorMessage = 'Failed to delete course. Please try again.';
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    }
    
    showToast('error', 'Error', errorMessage);
  } finally {
    isDeleting.value = false;
  }
}

// Pagination
const totalPages = computed(() => {
  return Math.ceil(filteredCourses.value.length / perPage);
});

const paginatedCourses = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  const end = start + perPage;
  return filteredCourses.value.slice(start, end);
});

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
}

onMounted(() => {
  fetchCourses();
});
</script>

<style scoped>
.courses-container {
  width: 100%;
  padding: 0 20px;
}

/* Header Section */
.courses-header {
  background: linear-gradient(135deg, #001d3d, #0077b6);
  color: white;
  padding: 40px;
  border-radius: 12px;
  margin-bottom: 32px;
  text-align: center;
}

.header-content h1 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 8px;
}

.header-content p {
  font-size: 1.1rem;
  opacity: 0.9;
  margin: 0;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 24px;
}

.stat-card {
  display: flex;
  align-items: center;
  padding: 20px;
  border-radius: 12px;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.bg-primary {
  background: linear-gradient(135deg, #001d3d, #0077b6);
}

.bg-success {
  background: linear-gradient(135deg, #0077b6, #003d5c);
}

.bg-warning {
  background: linear-gradient(135deg, #4a90a4, #001d3d);
}

.bg-info {
  background: linear-gradient(135deg, #0099cc, #004d66);
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  color: white !important;
  font-size: 1.8rem;
  background-color: rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  margin-right: 16px;
  flex-shrink: 0;
}

.stat-icon i {
  color: white !important;
  font-size: 1.8rem !important;
  font-weight: bold;
  line-height: 1;
  display: block;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.9rem;
  margin: 5px 0 0 0;
  opacity: 0.9;
}

/* Course Controls */
.course-controls {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  margin-bottom: 24px;
  padding: 20px;
}

.course-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.course-header h4 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 15px;
}

.search-box {
  position: relative;
  width: 300px;
}

.search-input {
  width: 100%;
  padding: 10px 15px 10px 40px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #0077b6;
  box-shadow: 0 0 0 3px rgba(77, 133, 245, 0.1);
}

.search-box i {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
}

/* Tabs Styling */
.nav-tabs {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 0;
  border-bottom: 1px solid #e0e0e0;
}

.nav-item {
  margin-right: 5px;
}

.nav-link {
  display: inline-block;
  padding: 10px 20px;
  color: #666;
  text-decoration: none;
  font-weight: 500;
  border-radius: 8px 8px 0 0;
  transition: all 0.2s;
  position: relative;
}

.nav-link:hover {
  color: #0077b6;
  background-color: rgba(77, 133, 245, 0.05);
}

.nav-link.active {
  color: #0077b6;
  border-bottom: 3px solid #0077b6;
  font-weight: 600;
}

.nav-link i {
  margin-right: 5px;
}

/* Courses Grid */
.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.course-card {
  background-color: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
  position: relative;
}

.course-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.course-header {
  position: relative;
}

.course-thumbnail {
  width: 100%;
  height: 160px;
  object-fit: cover;
  display: block;
}

.level-badge {
  position: absolute;
  top: 15px;
  left: 15px;
  background-color: #0077b6;
  color: white;
  font-size: 0.75rem;
  font-weight: 500;
  padding: 5px 10px;
  border-radius: 20px;
}

.status-badge {
  position: absolute;
  top: 15px;
  right: 15px;
  font-size: 0.75rem;
  font-weight: 500;
  padding: 5px 10px;
  border-radius: 20px;
  text-transform: uppercase;
}

.status-published {
  background-color: #40d787;
  color: white;
}

.status-draft {
  background-color: #ffbc3e;
  color: white;
}

.status-archived {
  background-color: #999;
  color: white;
}

.course-body {
  padding: 20px;
  flex-grow: 1;
}

.course-title {
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0 0 10px 0;
  color: #333;
}

.course-description {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 15px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  height: 40px;
}

.course-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 15px;
}

.meta-item {
  font-size: 0.85rem;
  color: #666;
  display: flex;
  align-items: center;
}

.meta-item i {
  color: #0077b6;
  margin-right: 5px;
}

.progress-container {
  margin-top: auto;
}

.progress-label {
  font-size: 0.85rem;
  color: #666;
  font-weight: 500;
}

.progress-value {
  font-size: 0.85rem;
  color: #0077b6;
  font-weight: 600;
}

.progress-bar {
  height: 6px;
  background-color: #e0e0e0;
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background-color: #0077b6;
  border-radius: 3px;
}

.course-footer {
  padding: 15px 20px;
  border-top: 1px solid #f0f0f0;
  display: flex;
  gap: 10px;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.95rem;
  transition: all 0.2s;
  cursor: pointer;
  text-decoration: none;
  border: none;
}

.btn-primary {
  background-color: #0077b6;
  color: white;
  box-shadow: 0 2px 5px rgba(77, 133, 245, 0.3);
}

.btn-primary:hover {
  background-color: #001d3d;
  box-shadow: 0 4px 8px rgba(77, 133, 245, 0.4);
}

.btn-outline-primary {
  border: 1px solid #0077b6;
  color: #0077b6;
  background-color: transparent;
}

.btn-outline-primary:hover {
  background-color: #0077b6;
  color: white;
}

.btn-danger {
  background-color: #ef4444;
  color: white;
}

.btn-danger:hover {
  background-color: #dc2626;
}

.btn-action {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px 8px;
  font-size: 0.85rem;
  font-weight: 500;
  min-height: 40px;
}

.btn-action i {
  font-size: 0.9rem;
}

.btn-view {
  flex: 1;
}

.btn-edit, .btn-delete {
  width: 42px;
  height: 42px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Empty State */
.empty-state {
  background-color: white;
  border-radius: 12px;
  padding: 60px 20px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.empty-icon {
  font-size: 3rem;
  color: #ccc;
  margin-bottom: 20px;
}

.empty-state h5 {
  color: #666;
  font-weight: 500;
  margin-bottom: 15px;
}

/* Loading and Error States */
.alert {
  padding: 15px 20px;
  border-radius: 8px;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.alert-danger {
  background-color: #fee;
  border: 1px solid #fcc;
  color: #c33;
}

.spinner-border {
  width: 3rem;
  height: 3rem;
}

.visually-hidden {
  position: absolute !important;
  width: 1px !important;
  height: 1px !important;
  padding: 0 !important;
  margin: -1px !important;
  overflow: hidden !important;
  clip: rect(0, 0, 0, 0) !important;
  white-space: nowrap !important;
  border: 0 !important;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  
  .courses-container {
    max-width: 100%;
    padding: 0 16px;
  }
  
  .courses-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
  }
}

@media (max-width: 768px) {
  .courses-container {
    padding: 0 15px;
  }
  
  .courses-header {
    padding: 24px;
  }
  
  .header-content h1 {
    font-size: 2rem;
  }
  
  .course-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .header-actions {
    flex-direction: column;
    width: 100%;
    gap: 10px;
  }
  
  .search-box {
    width: 100%;
    max-width: none;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .stat-card {
    padding: 16px;
  }
  
  .stat-icon {
    width: 48px;
    height: 48px;
    font-size: 1.5rem;
    margin-right: 12px;
    color: white !important;
    background-color: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
    justify-content: center;
    border-radius: 50%;
  }
  
  .stat-icon i {
    color: white !important;
    font-size: 1.5rem !important;
    display: block;
  }
  
  .stat-value {
    font-size: 1.5rem;
  }
  
  .courses-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .course-controls {
    padding: 16px;
  }
  
  .nav-tabs {
    flex-wrap: wrap;
  }
  
  .nav-link {
  padding: 8px 16px;
  font-size: 0.9rem;
  }
  
  .course-body {
    padding: 16px;
  }
  
  .course-footer {
    padding: 12px 16px;
  }
  
  .btn {
    padding: 8px 16px;
    font-size: 0.9rem;
  }
}

/* Utility classes */
.d-flex {
  display: flex;
}

.justify-content-between {
  justify-content: space-between;
}

.align-items-center {
  align-items: center;
}

.mb-1 {
  margin-bottom: 0.25rem;
}

.me-1 {
  margin-right: 0.25rem;
}

.me-2 {
  margin-right: 0.5rem;
}

.ms-3 {
  margin-left: 1rem;
}

.mt-3 {
  margin-top: 1rem;
}

.py-5 {
  padding-top: 3rem;
  padding-bottom: 3rem;
}

.text-center {
  text-align: center;
}

.text-muted {
  color: #6c757d;
}

.text-primary {
  color: #0077b6;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.btn-outline-danger {
  color: #dc3545;
  border-color: #dc3545;
  background-color: transparent;
}

.btn-outline-danger:hover {
  color: white;
  background-color: #dc3545;
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
  padding: 20px;
}

.modal-content {
  background-color: white;
  border-radius: 12px;
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 24px 0;
  margin-bottom: 20px;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: #111827;
}

.close-btn {
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 8px;
  border-radius: 4px;
  transition: color 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
}

.close-btn:hover {
  color: #6b7280;
  background-color: #f3f4f6;
}

.course-form {
  padding: 0 24px 24px;
}

.form-section {
  margin-bottom: 32px;
}

.form-section h3 {
  margin: 0 0 16px 0;
  font-size: 1.2rem;
  font-weight: 600;
  color: #374151;
  padding-bottom: 8px;
  border-bottom: 2px solid #e5e7eb;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
  color: #374151;
  font-size: 0.9rem;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.95rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #0077b6;
  box-shadow: 0 0 0 3px rgba(77, 133, 245, 0.1);
}

.form-row {
  display: flex;
  gap: 16px;
}

.form-row .form-group {
  flex: 1;
}

.form-row .form-group.flex-2 {
  flex: 2;
}

.form-help {
  display: block;
  margin-top: 4px;
  font-size: 0.8rem;
  color: #6b7280;
}

.chapter-item {
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 16px;
}

.chapter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.chapter-header h4 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #374151;
}

.remove-chapter-btn {
  background-color: #fee2e2;
  color: #dc2626;
  border: none;
  border-radius: 4px;
  padding: 6px 8px;
  cursor: pointer;
  font-size: 0.85rem;
  transition: background-color 0.2s;
}

.remove-chapter-btn:hover {
  background-color: #fecaca;
}

.add-chapter-btn {
  background-color: #f0f9ff;
  color: #0369a1;
  border: 1px dashed #0ea5e9;
  border-radius: 6px;
  padding: 12px 20px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.add-chapter-btn:hover {
  background-color: #e0f2fe;
  border-color: #0284c7;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.btn-secondary {
  background-color: #f9fafb;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background-color: #f3f4f6;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Image Upload Styles */
.image-upload-container {
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  position: relative;
  overflow: hidden;
}

.file-input {
  position: absolute;
  top: -9999px;
  left: -9999px;
}

.upload-placeholder {
  padding: 40px 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  background-color: #f9fafb;
}

.upload-placeholder:hover {
  background-color: #f3f4f6;
  border-color: #9ca3af;
}

.upload-placeholder i {
  font-size: 2rem;
  color: #9ca3af;
  margin-bottom: 10px;
}

.upload-placeholder p {
  margin: 0 0 5px 0;
  color: #374151;
  font-weight: 500;
}

.upload-placeholder small {
  color: #6b7280;
  font-size: 0.8rem;
}

.image-preview {
  position: relative;
  display: inline-block;
  width: 100%;
}

.preview-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 6px;
}

.remove-image-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  background: rgba(0, 0, 0, 0.6);
  color: white;
  border: none;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
}

.remove-image-btn:hover {
  background: rgba(0, 0, 0, 0.8);
}

/* Video Upload Styles */
.video-upload-container {
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  position: relative;
  overflow: hidden;
}

.video-preview {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px;
  background-color: #f9fafb;
}

.video-info {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #374151;
}

.video-info i {
  font-size: 1.5rem;
  color: #0077b6;
}

.video-info span {
  font-size: 0.95rem;
  font-weight: 500;
}

.remove-video-btn {
  background-color: #fee2e2;
  color: #dc2626;
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
}

.remove-video-btn:hover {
  background-color: #fecaca;
}

/* Delete Modal Styles */
.delete-modal {
  max-width: 500px;
}

.delete-modal .modal-body {
  padding: 0 24px;
  text-align: center;
}

.warning-icon {
  font-size: 3rem;
  color: #f59e0b;
  margin-bottom: 20px;
}

.delete-modal p {
  margin-bottom: 15px;
  color: #374151;
}

.warning-text {
  font-size: 0.9rem;
  color: #6b7280;
}

.error-text {
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 6px;
  padding: 12px;
  color: #dc2626;
  font-size: 0.9rem;
  display: flex;
  align-items: flex-start;
  gap: 8px;
}

.error-text i {
  margin-top: 2px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px 24px 24px;
  border-top: 1px solid #e5e7eb;
  margin-top: 20px;
}

/* Pagination Styles */
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  margin-top: 20px;
}

.pagination-btn {
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 10px 16px;
  font-size: 0.9rem;
  font-weight: 500;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.pagination-btn:hover:not([disabled]) {
  background-color: #f3f4f6;
  border-color: #d1d5db;
}

.pagination-btn[disabled] {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #6b7280;
  font-size: 0.9rem;
  font-weight: 500;
}

/* Animation for spinner */
.fa-spin {
  animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>