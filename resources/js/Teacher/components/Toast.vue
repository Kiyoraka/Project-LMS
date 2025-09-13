<template>
  <div v-if="visible" class="toast-container" :class="typeClass" @click="hide">
    <div class="toast-content">
      <div class="toast-icon">
        <i :class="iconClass"></i>
      </div>
      <div class="toast-message">
        <h4 v-if="title" class="toast-title">{{ title }}</h4>
        <p class="toast-text">{{ message }}</p>
      </div>
      <button v-if="!autoHide" class="toast-close" @click.stop="hide">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div v-if="autoHide" class="toast-progress">
      <div class="toast-progress-bar" :style="{ animationDuration: duration + 'ms' }"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
  message: {
    type: String,
    required: true
  },
  title: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  duration: {
    type: Number,
    default: 5000
  },
  autoHide: {
    type: Boolean,
    default: true
  },
  show: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['hide', 'shown']);

const visible = ref(false);
let timeoutId = null;

const typeClass = computed(() => {
  return `toast-${props.type}`;
});

const iconClass = computed(() => {
  const icons = {
    success: 'fas fa-check-circle',
    error: 'fas fa-times-circle',
    warning: 'fas fa-exclamation-triangle',
    info: 'fas fa-info-circle'
  };
  return icons[props.type] || icons.info;
});

const show = () => {
  visible.value = true;
  emit('shown');
  
  if (props.autoHide) {
    timeoutId = setTimeout(() => {
      hide();
    }, props.duration);
  }
};

const hide = () => {
  visible.value = false;
  if (timeoutId) {
    clearTimeout(timeoutId);
    timeoutId = null;
  }
  emit('hide');
};

// Watch for show prop changes
watch(() => props.show, (newValue) => {
  if (newValue) {
    show();
  } else {
    hide();
  }
}, { immediate: true });

// Auto-show on mount if show prop is true
onMounted(() => {
  if (props.show) {
    show();
  }
});

// Expose methods for programmatic control
defineExpose({
  show,
  hide
});
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  min-width: 320px;
  max-width: 500px;
  z-index: 9999;
  cursor: pointer;
  animation: slideInRight 0.3s ease-out;
}

.toast-content {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  padding: 16px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  border-left: 4px solid;
  position: relative;
  overflow: hidden;
}

.toast-icon {
  flex-shrink: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.toast-message {
  flex: 1;
  min-width: 0;
}

.toast-title {
  margin: 0 0 4px 0;
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
}

.toast-text {
  margin: 0;
  font-size: 0.9rem;
  color: #6b7280;
  line-height: 1.4;
}

.toast-close {
  flex-shrink: 0;
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  font-size: 1rem;
  padding: 0;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.toast-close:hover {
  color: #6b7280;
}

.toast-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 3px;
  background-color: rgba(0, 0, 0, 0.1);
}

.toast-progress-bar {
  height: 100%;
  width: 100%;
  transform-origin: left;
  animation: shrinkWidth linear;
}

/* Type-specific styles */
.toast-success .toast-content {
  border-left-color: #10b981;
}

.toast-success .toast-icon {
  color: #10b981;
}

.toast-success .toast-progress-bar {
  background-color: #10b981;
}

.toast-error .toast-content {
  border-left-color: #ef4444;
}

.toast-error .toast-icon {
  color: #ef4444;
}

.toast-error .toast-progress-bar {
  background-color: #ef4444;
}

.toast-warning .toast-content {
  border-left-color: #f59e0b;
}

.toast-warning .toast-icon {
  color: #f59e0b;
}

.toast-warning .toast-progress-bar {
  background-color: #f59e0b;
}

.toast-info .toast-content {
  border-left-color: #0077b6;
}

.toast-info .toast-icon {
  color: #0077b6;
}

.toast-info .toast-progress-bar {
  background-color: #0077b6;
}

/* Animations */
@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes shrinkWidth {
  from {
    transform: scaleX(1);
  }
  to {
    transform: scaleX(0);
  }
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .toast-container {
    top: 10px;
    right: 10px;
    left: 10px;
    min-width: auto;
    max-width: none;
  }
}

/* Mobile specific - ensure toast appears above mobile navigation */
@media (max-width: 768px) {
  .toast-container {
    top: 70px; /* Account for mobile header height */
    z-index: 10000; /* Higher than mobile menus */
    animation: slideInFromTop 0.3s ease-out; /* Mobile-friendly animation */
  }
  
  /* Mobile-specific animation */
  @keyframes slideInFromTop {
    from {
      transform: translateY(-100%);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }
}
</style> 