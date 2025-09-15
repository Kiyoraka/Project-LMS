<template>
  <div class="secure-video-player">
    <div v-if="!videoReady && !isLocalVideo" class="video-loading">
      <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
      </div>
      <p>Loading secure video...</p>
    </div>

    <div v-if="error" class="video-error">
      <i class="fas fa-exclamation-triangle"></i>
      <p>Unable to load video. Please try again later.</p>
    </div>

    <!-- YouTube iframe for external videos -->
    <div
      v-if="!isLocalVideo && embedUrl && !error"
      class="video-container"
      :class="{ 'video-loading': !videoReady }"
    >
      <iframe
        ref="videoFrame"
        :src="embedUrl"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        allowfullscreen
        @load="onVideoLoad"
        @error="onVideoError"
        class="video-iframe"
      ></iframe>
      
      <!-- Video overlay for additional protection -->
      <div v-if="showOverlay" class="video-overlay" @click="hideOverlay">
        <div class="overlay-content">
          <div class="play-button">
            <i class="fas fa-play"></i>
          </div>
          <p class="overlay-text">Click to play video</p>
          <div class="security-notice">
            <i class="fas fa-shield-alt"></i>
            Secure Learning Content
          </div>
        </div>
      </div>
    </div>

    <!-- HTML5 video player for local videos -->
    <div
      v-if="isLocalVideo && localVideoUrl && !error"
      class="video-container"
    >
      <video
        ref="videoPlayer"
        :src="localVideoUrl"
        controls
        controlsList="nodownload"
        @loadedmetadata="onVideoLoad"
        @error="onVideoError"
        class="video-player"
        :data-lesson-id="lessonId || lesson?.id"
      >
        Your browser does not support the video tag.
      </video>
    </div>
    
    <!-- Video Information -->
    <div v-if="lesson" class="video-info">
      <h4 class="video-title">{{ lesson.title }}</h4>
      <div class="video-meta">
        <span v-if="lesson.duration_minutes" class="duration">
          <i class="fas fa-clock"></i>
          {{ lesson.duration_minutes }} minutes
        </span>
        <span class="security-badge">
          <i class="fas fa-lock"></i>
          Protected Content
        </span>
      </div>
      <p v-if="lesson.description" class="video-description">{{ lesson.description }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  videoUrl: {
    type: String,
    required: true
  },
  lesson: {
    type: Object,
    default: null
  },
  lessonId: {
    type: Number,
    default: null
  },
  duration: {
    type: Number,
    default: 600 // Default 10 minutes in seconds
  },
  autoplay: {
    type: Boolean,
    default: false
  },
  showOverlay: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['ready', 'error', 'play', 'pause', 'progress-update', 'lesson-completed']);

const videoFrame = ref(null);
const videoPlayer = ref(null);
const videoReady = ref(false);
const error = ref(false);
const showOverlay = ref(props.showOverlay);

// Progress tracking variables
const currentTime = ref(0);
const totalDuration = ref(0);
const progressPercentage = ref(0);
const watchedSegments = ref(new Set());
const hasCompleted = ref(false);
const progressCheckInterval = ref(null);

// Check if it's a local video or YouTube URL
const isLocalVideo = computed(() => {
  if (!props.videoUrl) return false;

  // Check if it's a relative path or storage path
  if (!props.videoUrl.startsWith('http://') && !props.videoUrl.startsWith('https://')) {
    return true;
  }

  // Check if it's a local storage URL
  try {
    const url = new URL(props.videoUrl, window.location.origin);
    return url.pathname.includes('/storage/');
  } catch {
    return true; // If URL parsing fails, assume it's a local path
  }
});

// Get the proper URL for local videos
const localVideoUrl = computed(() => {
  if (!isLocalVideo.value || !props.videoUrl) return null;

  // If it's already a full URL, use it
  if (props.videoUrl.startsWith('http://') || props.videoUrl.startsWith('https://')) {
    return props.videoUrl;
  }

  // If it's a storage path without /storage/ prefix, add it
  if (!props.videoUrl.startsWith('/storage/')) {
    return `/storage/${props.videoUrl}`;
  }

  // Otherwise, use as is
  return props.videoUrl;
});

// Convert YouTube URL to secure embed URL
const embedUrl = computed(() => {
  if (!props.videoUrl || isLocalVideo.value) return null;

  try {
    const url = new URL(props.videoUrl);
    let videoId = null;

    // Extract video ID from different YouTube URL formats
    if (url.hostname === 'www.youtube.com' || url.hostname === 'youtube.com') {
      videoId = url.searchParams.get('v');
    } else if (url.hostname === 'youtu.be') {
      videoId = url.pathname.slice(1);
    } else if (url.hostname === 'www.youtube-nocookie.com') {
      // Already an embed URL
      return props.videoUrl;
    }

    if (!videoId) {
      throw new Error('Invalid YouTube URL');
    }

    // Use youtube-nocookie.com for better privacy
    const embedParams = new URLSearchParams({
      rel: '0',           // Don't show related videos
      modestbranding: '1', // Minimal YouTube branding
      controls: '1',       // Show player controls
      disablekb: '1',     // Disable keyboard controls
      fs: '1',            // Allow fullscreen
      iv_load_policy: '3', // Hide video annotations
      start: '0'          // Start from beginning
    });

    if (props.autoplay) {
      embedParams.set('autoplay', '1');
    }

    return `https://www.youtube-nocookie.com/embed/${videoId}?${embedParams.toString()}`;

  } catch (err) {
    console.error('Error processing video URL:', err);
    error.value = true;
    return null;
  }
});

function onVideoLoad() {
  videoReady.value = true;
  emit('ready');

  // Set total duration if video element exists
  if (videoPlayer.value) {
    totalDuration.value = videoPlayer.value.duration || props.duration;
    // Start progress tracking for local videos
    startProgressTracking();
  } else if (!isLocalVideo.value) {
    // For YouTube videos, use time-based tracking
    totalDuration.value = props.duration;
    startYouTubeProgressTracking();
  }
}

function onVideoError() {
  error.value = true;
  emit('error');
}

function hideOverlay() {
  showOverlay.value = false;
  emit('play');
}

// Track video progress
function startProgressTracking() {
  if (!videoPlayer.value) return;

  // Clear any existing interval
  if (progressCheckInterval.value) {
    clearInterval(progressCheckInterval.value);
  }

  // Track progress every second
  progressCheckInterval.value = setInterval(() => {
    if (videoPlayer.value && !videoPlayer.value.paused) {
      updateProgress();
    }
  }, 1000);

  // Add event listeners for better tracking
  videoPlayer.value.addEventListener('timeupdate', updateProgress);
  videoPlayer.value.addEventListener('ended', onVideoEnded);
  videoPlayer.value.addEventListener('play', () => emit('play'));
  videoPlayer.value.addEventListener('pause', () => emit('pause'));
}

function updateProgress() {
  if (!videoPlayer.value) return;

  currentTime.value = videoPlayer.value.currentTime;
  const duration = videoPlayer.value.duration || totalDuration.value || props.duration;

  if (duration > 0) {
    // Calculate percentage based on actual time watched
    const rawPercentage = (currentTime.value / duration) * 100;
    progressPercentage.value = Math.min(100, Math.floor(rawPercentage));

    // Track watched segments (divide video into 10-second segments)
    const segmentSize = 10; // seconds
    const currentSegment = Math.floor(currentTime.value / segmentSize);
    watchedSegments.value.add(currentSegment);

    // Calculate actual watched percentage based on segments
    const totalSegments = Math.ceil(duration / segmentSize);
    const watchedPercentage = (watchedSegments.value.size / totalSegments) * 100;

    // Emit progress update
    emit('progress-update', {
      lessonId: props.lessonId || props.lesson?.id,
      currentTime: currentTime.value,
      duration: duration,
      progressPercentage: Math.floor(watchedPercentage),
      rawProgressPercentage: progressPercentage.value,
      isCompleted: watchedPercentage >= 90
    });

    // Check for completion (90% watched)
    if (watchedPercentage >= 90 && !hasCompleted.value) {
      hasCompleted.value = true;
      emit('lesson-completed', {
        lessonId: props.lessonId || props.lesson?.id,
        completedAt: new Date().toISOString()
      });
    }
  }
}

function onVideoEnded() {
  // Mark as 100% when video ends naturally
  progressPercentage.value = 100;

  emit('progress-update', {
    lessonId: props.lessonId || props.lesson?.id,
    currentTime: totalDuration.value,
    duration: totalDuration.value,
    progressPercentage: 100,
    rawProgressPercentage: 100,
    isCompleted: true
  });

  if (!hasCompleted.value) {
    hasCompleted.value = true;
    emit('lesson-completed', {
      lessonId: props.lessonId || props.lesson?.id,
      completedAt: new Date().toISOString()
    });
  }
}

// Time-based progress tracking for YouTube videos
let youtubeStartTime = null;
let youtubeWatchedTime = 0;
let youtubePausedTime = 0;
let isYouTubePlaying = false;

function startYouTubeProgressTracking() {
  // Clear any existing interval
  if (progressCheckInterval.value) {
    clearInterval(progressCheckInterval.value);
  }

  // Start tracking when overlay is hidden (video starts playing)
  youtubeStartTime = Date.now();
  isYouTubePlaying = false;

  // Track progress every second
  progressCheckInterval.value = setInterval(() => {
    updateYouTubeProgress();
  }, 1000);
}

function updateYouTubeProgress() {
  if (!isLocalVideo.value && !showOverlay.value) {
    // Calculate time watched (assuming video is playing when overlay is hidden)
    if (!isYouTubePlaying) {
      isYouTubePlaying = true;
      youtubeStartTime = Date.now();
    }

    const currentWatchTime = (Date.now() - youtubeStartTime) / 1000; // Convert to seconds
    youtubeWatchedTime = Math.min(currentWatchTime, totalDuration.value);

    // Calculate percentage based on time watched vs total duration
    const watchPercentage = Math.min(100, Math.floor((youtubeWatchedTime / totalDuration.value) * 100));
    progressPercentage.value = watchPercentage;

    // Emit progress update
    emit('progress-update', {
      lessonId: props.lessonId || props.lesson?.id,
      currentTime: youtubeWatchedTime,
      duration: totalDuration.value,
      progressPercentage: watchPercentage,
      rawProgressPercentage: watchPercentage,
      isCompleted: watchPercentage >= 90
    });

    // Check for completion (90% watched)
    if (watchPercentage >= 90 && !hasCompleted.value) {
      hasCompleted.value = true;
      emit('lesson-completed', {
        lessonId: props.lessonId || props.lesson?.id,
        completedAt: new Date().toISOString()
      });
    }

    // Auto-complete at 100% duration time
    if (youtubeWatchedTime >= totalDuration.value && !hasCompleted.value) {
      progressPercentage.value = 100;
      hasCompleted.value = true;
      emit('lesson-completed', {
        lessonId: props.lessonId || props.lesson?.id,
        completedAt: new Date().toISOString()
      });

      // Stop tracking
      if (progressCheckInterval.value) {
        clearInterval(progressCheckInterval.value);
      }
    }
  }
}

// Prevent right-click on video
function preventContextMenu(event) {
  event.preventDefault();
  return false;
}

// Prevent video download attempts
function preventVideoSave(event) {
  if (event.key === 's' && (event.ctrlKey || event.metaKey)) {
    event.preventDefault();
    return false;
  }
}

// Security measures
onMounted(() => {
  // Add event listeners for security
  document.addEventListener('contextmenu', preventContextMenu);
  document.addEventListener('keydown', preventVideoSave);
  
  // Prevent iframe manipulation
  if (videoFrame.value) {
    videoFrame.value.addEventListener('load', () => {
      try {
        // Additional security could be implemented here
        // Note: Due to CORS, we can't access iframe content directly
      } catch (err) {
        // Expected for cross-origin iframes
      }
    });
  }
});

onUnmounted(() => {
  // Clean up event listeners
  document.removeEventListener('contextmenu', preventContextMenu);
  document.removeEventListener('keydown', preventVideoSave);

  // Clear progress tracking interval
  if (progressCheckInterval.value) {
    clearInterval(progressCheckInterval.value);
  }

  // Remove video event listeners if video element exists
  if (videoPlayer.value) {
    videoPlayer.value.removeEventListener('timeupdate', updateProgress);
    videoPlayer.value.removeEventListener('ended', onVideoEnded);
  }
});

// Watch for URL changes
watch(() => props.videoUrl, () => {
  videoReady.value = false;
  error.value = false;
  showOverlay.value = props.showOverlay;
});
</script>

<style scoped>
.secure-video-player {
  width: 100%;
  background: #000;
  border-radius: 8px;
  overflow: hidden;
  position: relative;
}

.video-container {
  position: relative;
  width: 100%;
  padding-bottom: 56.25%; /* 16:9 aspect ratio */
  height: 0;
  background: #000;
}

.video-iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border: none;
  transition: opacity 0.3s ease;
}

.video-container.video-loading .video-iframe {
  opacity: 0;
}

.video-loading,
.video-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  background: #1a1a1a;
  color: white;
  text-align: center;
}

.loading-spinner {
  font-size: 2rem;
  margin-bottom: 1rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.video-error {
  color: #ef4444;
}

.video-error i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.7;
}

.video-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(
    135deg,
    rgba(0, 0, 0, 0.7) 0%,
    rgba(0, 0, 0, 0.5) 50%,
    rgba(0, 0, 0, 0.7) 100%
  );
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: opacity 0.3s ease;
  z-index: 10;
}

.video-overlay:hover {
  background: linear-gradient(
    135deg,
    rgba(0, 0, 0, 0.8) 0%,
    rgba(0, 0, 0, 0.6) 50%,
    rgba(0, 0, 0, 0.8) 100%
  );
}

.overlay-content {
  text-align: center;
  color: white;
}

.play-button {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  border: 3px solid rgba(255, 255, 255, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  font-size: 2rem;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.video-overlay:hover .play-button {
  background: rgba(255, 255, 255, 0.3);
  border-color: white;
  transform: scale(1.1);
}

.overlay-text {
  margin: 0 0 1rem 0;
  font-size: 1.1rem;
  font-weight: 500;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

.security-notice {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  opacity: 0.8;
  background: rgba(0, 0, 0, 0.3);
  padding: 0.5rem 1rem;
  border-radius: 20px;
  backdrop-filter: blur(5px);
}

.video-info {
  padding: 1.5rem;
  background: white;
}

.video-title {
  margin: 0 0 1rem 0;
  font-size: 1.3rem;
  font-weight: 600;
  color: #1f2937;
}

.video-meta {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.duration {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6b7280;
  font-size: 0.9rem;
}

.security-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #fef3c7;
  color: #92400e;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
}

.video-description {
  margin: 0;
  color: #4b5563;
  line-height: 1.6;
}

/* Responsive Design */
@media (max-width: 768px) {
  .play-button {
    width: 60px;
    height: 60px;
    font-size: 1.5rem;
  }
  
  .overlay-text {
    font-size: 1rem;
  }
  
  .video-info {
    padding: 1rem;
  }
  
  .video-title {
    font-size: 1.1rem;
  }
}

/* Prevent text selection on video overlay */
.video-overlay {
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

/* Disable drag on video */
.video-iframe {
  pointer-events: auto;
  -webkit-user-drag: none;
  -khtml-user-drag: none;
  -moz-user-drag: none;
  -o-user-drag: none;
  user-drag: none;
}

/* HTML5 Video Player Styles */
.video-player {
  width: 100%;
  height: auto;
  max-height: 500px;
  background-color: #000;
  border-radius: 8px;
}

</style> 