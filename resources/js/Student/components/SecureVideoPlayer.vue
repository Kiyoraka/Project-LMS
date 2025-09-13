<template>
  <div class="simple-video-container">
    <!-- Clean video player -->
    <div class="video-wrapper">
      <!-- YouTube iframe -->
      <div v-if="videoUrl" class="video-iframe-container">
        <iframe 
          ref="videoIframe"
          :src="videoUrl"
          title="Lesson Video"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
          @load="onVideoLoaded"
        ></iframe>
      </div>
      
      <!-- Simple loading -->
      <div v-else class="loading-state">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading video...</span>
        </div>
        <p class="mt-2">Loading video...</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SecureVideoPlayer',
  props: {
    encryptedUrl: {
      type: String,
      required: true
    },
    lessonId: {
      type: Number,
      required: true
    },
    duration: {
      type: Number,
      default: 300
    }
  },
  data() {
    return {
      videoUrl: null,
      progressInterval: null,
      watchedSeconds: 0,
      isCompleted: false
    };
  },
  watch: {
    lessonId(newLessonId, oldLessonId) {
      if (newLessonId !== oldLessonId) {
        // Reset component state for new lesson
        this.cleanup();
        this.watchedSeconds = 0;
        this.isCompleted = false;
        this.videoUrl = null;
        
        // Reinitialize for new lesson
        this.setupVideo();
        this.loadExistingProgress();
      }
    }
  },
  mounted() {
    this.setupVideo();
    this.loadExistingProgress();
  },
  beforeUnmount() {
    this.cleanup();
  },
  methods: {
    setupVideo() {
      // Simple URL processing
      setTimeout(() => {
        let url = this.encryptedUrl;
        
        // Handle encrypted URLs
        if (url && url.startsWith('enc:')) {
          try {
            const base64Url = url.substring(4);
            url = atob(base64Url);
          } catch (error) {
            // Fallback to original if decryption fails
          }
        }
        
        // Convert to YouTube embed format if needed
        this.videoUrl = this.convertToEmbedUrl(url);
      }, 100);
    },
    
    convertToEmbedUrl(url) {
      if (!url) return null;
      
      let videoId = null;
      
      // Extract video ID from various YouTube URL formats
      if (url.includes('youtu.be/')) {
        videoId = url.split('youtu.be/')[1].split('?')[0].split('&')[0];
      } else if (url.includes('youtube.com/watch?v=')) {
        videoId = url.split('v=')[1].split('&')[0];
      } else if (url.includes('youtube.com/embed/')) {
        return url; // Already embed format
      }
      
      // Convert to embed format
      if (videoId) {
        return `https://www.youtube.com/embed/${videoId}?modestbranding=1&rel=0`;
      }
      
      return url; // Return as-is if not YouTube
    },
    
    onVideoLoaded() {
      // Start background progress tracking after 3 seconds
      setTimeout(() => {
        this.startBackgroundTracking();
      }, 3000);
    },
    
    startBackgroundTracking() {
      if (this.progressInterval || this.isCompleted) {
        return;
      }
      
      // Save progress every 30 seconds
      this.progressInterval = setInterval(() => {
        this.watchedSeconds += 30;
        this.saveProgress();
        
        // Emit progress update to parent component
        const progressPercentage = Math.min(100, Math.round((this.watchedSeconds / this.duration) * 100));
        this.$emit('progress-update', {
          lessonId: this.lessonId,
          progressPercentage: progressPercentage,
          watchedSeconds: this.watchedSeconds
        });
      }, 30000); // 30 seconds
    },
    
    async saveProgress() {
      if (this.isCompleted) return;
      
      try {
        const response = await axios.post(`/api/student/lessons/${this.lessonId}/progress`, {
          time_spent_seconds: this.watchedSeconds,
          progress_percentage: Math.min(100, Math.round((this.watchedSeconds / this.duration) * 100))
        });
      } catch (error) {
        // Silent error handling for progress saves
      }
    },
    
    async loadExistingProgress() {
      try {
        const response = await axios.get(`/api/student/lessons/${this.lessonId}/progress`);
        
        if (response.data) {
          this.watchedSeconds = response.data.time_spent_seconds || 0;
          this.isCompleted = response.data.is_completed || false;
          
          // Emit initial progress to parent component
          const progressPercentage = Math.min(100, Math.round((this.watchedSeconds / this.duration) * 100));
          this.$emit('progress-update', {
            lessonId: this.lessonId,
            progressPercentage: progressPercentage,
            watchedSeconds: this.watchedSeconds,
            isCompleted: this.isCompleted
          });
        }
      } catch (error) {
        // Silent error handling
      }
    },
    
    async markLessonCompleted() {
      if (this.isCompleted) return;
      
      try {
        const response = await axios.post(`/api/student/lessons/${this.lessonId}/complete`, {
          time_spent_seconds: this.watchedSeconds,
          completed: true
        });
        
        this.isCompleted = true;
        this.cleanup();
        this.$emit('lesson-completed', this.lessonId);
      } catch (error) {
        let errorMessage = 'Failed to mark lesson as complete. Please try again.';
        
        if (error.response?.status === 403) {
          errorMessage = 'You are not enrolled in this course.';
        } else if (error.response?.status === 404) {
          errorMessage = 'Lesson not found.';
        } else if (error.response?.status === 422) {
          errorMessage = 'Invalid data. Please try again.';
        } else if (error.response?.data?.message) {
          errorMessage = error.response.data.message;
        }
        
        this.$emit('show-toast', errorMessage, 'Error', 'error');
      }
    },
    
    cleanup() {
      if (this.progressInterval) {
        clearInterval(this.progressInterval);
        this.progressInterval = null;
      }
      // Final progress save
      this.saveProgress();
    }
  }
};
</script>

<style scoped>
.simple-video-container {
  border-radius: 8px;
  overflow: hidden;
  background: #000;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.video-wrapper {
  position: relative;
  padding-top: 56.25%; /* 16:9 aspect ratio */
  background-color: #000;
}

.video-iframe-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.video-iframe-container iframe {
  width: 100%;
  height: 100%;
  border: none;
}

.loading-state {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: white;
  text-align: center;
}
</style>