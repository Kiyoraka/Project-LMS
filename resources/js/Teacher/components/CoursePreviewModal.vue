<template>
  <div v-if="visible" class="modal-overlay" @click="$emit('close')">
    <div class="modal-content course-preview-modal" @click.stop>
      <div class="modal-header">
        <h3>
          <i class="fas fa-eye"></i>
          Course Preview
        </h3>
        <button class="close-button" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div v-if="loading" class="modal-body loading-state">
        <div class="loading-spinner">
          <i class="fas fa-spinner fa-spin"></i>
        </div>
        <p>Loading course preview...</p>
      </div>
      
      <div v-else-if="course" class="modal-body">
        <!-- Course Header -->
        <div class="preview-header">
          <div class="preview-badge">
            <i class="fas fa-eye"></i>
            Teacher Preview Mode
          </div>
          <div class="course-header">
            <img
              v-if="course.thumbnail"
              :src="course.thumbnail"
              :alt="course.title"
              class="course-thumbnail"
            >
            <div v-else class="course-placeholder">
              <i class="fas fa-book"></i>
            </div>
            <div class="course-info">
              <h2 class="course-title">{{ course.title }}</h2>
              <p class="course-description">{{ course.short_description }}</p>
              <div class="course-meta">
                <span class="meta-item">
                  <i class="fas fa-signal"></i>
                  {{ course.level }}
                </span>
                <span class="meta-item">
                  <i class="fas fa-users"></i>
                  {{ course.enrollments_count }} students
                </span>
                <span class="meta-item">
                  <i class="fas fa-clock"></i>
                  {{ formatDuration(course.duration_hours) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Course Content Preview -->
        <div class="preview-content">
          <h4>
            <i class="fas fa-list-ul"></i>
            Course Content
          </h4>
          
          <div v-if="course.modules && course.modules.length > 0" class="modules-preview">
            <div v-for="(module, moduleIndex) in course.modules" :key="module.id" class="module-preview">
              <div class="module-header">
                <h5 class="module-title">
                  <span class="module-number">{{ moduleIndex + 1 }}</span>
                  {{ module.title }}
                </h5>
                <span class="lesson-count">{{ module.lessons?.length || 0 }} lessons</span>
              </div>
              
              <div v-if="module.lessons && module.lessons.length > 0" class="lessons-preview">
                <div 
                  v-for="(lesson, lessonIndex) in module.lessons" 
                  :key="lesson.id" 
                  class="lesson-preview"
                  @click="selectedLesson = lesson"
                  :class="{ active: selectedLesson?.id === lesson.id }"
                >
                  <div class="lesson-icon">
                    <i class="fas fa-play" v-if="lesson.content_type === 'video'"></i>
                    <i class="fas fa-file-alt" v-else-if="lesson.content_type === 'text'"></i>
                    <i class="fas fa-question-circle" v-else-if="lesson.content_type === 'quiz'"></i>
                    <i class="fas fa-tasks" v-else></i>
                  </div>
                  
                  <div class="lesson-details">
                    <div class="lesson-title">
                      <span class="lesson-number">{{ lessonIndex + 1 }}.</span>
                      {{ lesson.title }}
                    </div>
                    <div class="lesson-meta">
                      <span v-if="lesson.duration_minutes" class="duration">
                        {{ lesson.duration_minutes }} min
                      </span>
                      <span class="type">{{ formatContentType(lesson.content_type) }}</span>
                      <span v-if="lesson.video_protected" class="protected">Protected</span>
                    </div>
                    <p v-if="lesson.description" class="lesson-description">{{ lesson.description }}</p>
                  </div>
                  
                  <div class="lesson-actions">
                    <button class="preview-btn" @click.stop="previewLesson(lesson)">
                      <i class="fas fa-eye"></i>
                      Preview
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="empty-content">
            <i class="fas fa-folder-open"></i>
            <p>No content available for preview.</p>
          </div>
        </div>

        <!-- Course Description -->
        <div v-if="course.description" class="preview-description">
          <h4>
            <i class="fas fa-align-left"></i>
            About This Course
          </h4>
          <p>{{ course.description }}</p>
        </div>
      </div>
      
      <div v-else class="modal-body error-state">
        <i class="fas fa-exclamation-triangle"></i>
        <p>Failed to load course preview.</p>
      </div>
    </div>

    <!-- Video Player Modal -->
    <div v-if="showVideoModal" class="video-modal-overlay" @click="closeVideoModal">
      <div class="video-modal-content" @click.stop>
        <div class="video-modal-header">
          <h3>
            <i class="fas fa-play"></i>
            {{ selectedVideoLesson?.title }}
          </h3>
          <button class="close-button" @click="closeVideoModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="video-modal-body">
          <SecureVideoPlayer 
            v-if="selectedVideoLesson?.video_url"
            :video-url="selectedVideoLesson.video_url"
            :lesson="selectedVideoLesson"
            :show-overlay="false"
            :autoplay="true"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import SecureVideoPlayer from './SecureVideoPlayer.vue';

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  courseId: {
    type: [String, Number],
    required: true
  }
});

const emit = defineEmits(['close']);

const loading = ref(false);
const course = ref(null);
const selectedLesson = ref(null);

async function loadCoursePreview() {
  if (!props.courseId) return;
  
  loading.value = true;
  try {
    const response = await axios.get(`/api/teacher/courses/${props.courseId}/preview`);
    course.value = response.data.course;
  } catch (error) {
    console.error('Error loading course preview:', error);
    course.value = null;
  } finally {
    loading.value = false;
  }
}

function formatDuration(hours) {
  if (!hours) return '0h';
  if (hours < 1) {
    return `${Math.round(hours * 60)}m`;
  }
  return `${Math.round(hours)}h`;
}

function formatContentType(type) {
  const types = {
    video: 'Video',
    text: 'Reading',
    quiz: 'Quiz',
    assignment: 'Assignment'
  };
  return types[type] || 'Content';
}

const showVideoModal = ref(false);
const selectedVideoLesson = ref(null);

function previewLesson(lesson) {
  if (lesson.content_type === 'video' && lesson.video_url) {
    selectedVideoLesson.value = lesson;
    showVideoModal.value = true;
  } else {
    // For other content types, show in modal or handle accordingly
    
  }
}

function closeVideoModal() {
  showVideoModal.value = false;
  selectedVideoLesson.value = null;
}

// Watch for visibility changes to load data
watch(() => props.visible, (newValue) => {
  if (newValue) {
    loadCoursePreview();
  } else {
    course.value = null;
    selectedLesson.value = null;
  }
});
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

.course-preview-modal {
  width: 90%;
  max-width: 900px;
  max-height: 90vh;
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 600;
  color: #1f2937;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.2rem;
  color: #9ca3af;
  cursor: pointer;
  transition: color 0.2s;
  padding: 0.5rem;
  border-radius: 6px;
}

.close-button:hover {
  color: #6b7280;
  background: #f3f4f6;
}

.modal-body {
  padding: 1.5rem;
  max-height: 70vh;
  overflow-y: auto;
}

.loading-state, .error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  text-align: center;
  color: #6b7280;
}

.loading-spinner {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.preview-badge {
  background: #dbeafe;
  color: #1e40af;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.course-header {
  display: grid;
  grid-template-columns: 150px 1fr;
  gap: 1.5rem;
  align-items: start;
  margin-bottom: 2rem;
}

.course-thumbnail {
  width: 150px;
  height: 112px;
  object-fit: cover;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.course-placeholder {
  width: 150px;
  height: 112px;
  background: #f3f4f6;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #9ca3af;
}

.course-title {
  margin: 0 0 0.5rem 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.course-description {
  margin: 0 0 1rem 0;
  color: #4b5563;
  line-height: 1.5;
}

.course-meta {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: #6b7280;
  font-size: 0.9rem;
}

.preview-content,
.preview-description {
  margin-bottom: 2rem;
}

.preview-content h4,
.preview-description h4 {
  margin: 0 0 1rem 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border-bottom: 2px solid #e5e7eb;
  padding-bottom: 0.5rem;
}

.module-preview {
  margin-bottom: 1.5rem;
}

.module-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #f3f4f6;
}

.module-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.module-number {
  background: #0077b6;
  color: white;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 600;
}

.lesson-count {
  background: #f3f4f6;
  color: #6b7280;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
}

.lesson-preview {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 6px;
  margin-bottom: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
}

.lesson-preview:hover {
  background: #f3f4f6;
  transform: translateY(-1px);
}

.lesson-preview.active {
  background: #dbeafe;
  border: 1px solid #0077b6;
}

.lesson-icon {
  width: 32px;
  height: 32px;
  background: #0077b6;
  color: white;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 0.9rem;
}

.lesson-details {
  flex: 1;
}

.lesson-title {
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.lesson-number {
  color: #0077b6;
  font-weight: 600;
  margin-right: 0.5rem;
}

.lesson-meta {
  display: flex;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.lesson-meta span {
  font-size: 0.8rem;
  color: #6b7280;
}

.duration {
  color: #0077b6;
}

.type {
  color: #059669;
}

.protected {
  color: #dc2626;
}

.lesson-description {
  margin: 0;
  font-size: 0.9rem;
  color: #6b7280;
  line-height: 1.4;
}

.lesson-actions {
  display: flex;
  align-items: center;
}

.preview-btn {
  background: #0077b6;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-size: 0.8rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: background-color 0.2s;
}

.preview-btn:hover {
  background: #2563eb;
}

.empty-content {
  text-align: center;
  padding: 3rem 1rem;
  color: #6b7280;
}

.empty-content i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

  .preview-description p {
    line-height: 1.6;
    color: #4b5563;
    margin: 0;
  }

  /* Video Modal Styles */
  .video-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1001;
  }

  .video-modal-content {
    width: 90%;
    max-width: 1000px;
    max-height: 90vh;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  }

  .video-modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
  }

  .video-modal-header h3 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .video-modal-body {
    padding: 0;
  }
  
  /* Responsive Design */
@media (max-width: 768px) {
  .course-preview-modal {
    width: 95%;
    margin: 1rem;
  }
  
  .modal-body {
    padding: 1rem;
  }
  
  .course-header {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .course-thumbnail,
  .course-placeholder {
    width: 100%;
    height: 200px;
  }
  
  .lesson-preview {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .lesson-actions {
    justify-content: flex-start;
  }
}
</style> 