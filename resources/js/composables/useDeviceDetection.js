import { ref, onMounted, onUnmounted } from 'vue';

export function useDeviceDetection() {
  const isMobile = ref(false);
  const isTablet = ref(false);
  const isDesktop = ref(false);
  const screenWidth = ref(0);

  const checkDevice = () => {
    screenWidth.value = window.innerWidth;
    
    // Define breakpoints
    const mobileBreakpoint = 768;
    const tabletBreakpoint = 1024;
    
    isMobile.value = screenWidth.value < mobileBreakpoint;
    isTablet.value = screenWidth.value >= mobileBreakpoint && screenWidth.value < tabletBreakpoint;
    isDesktop.value = screenWidth.value >= tabletBreakpoint;
  };

  const handleResize = () => {
    checkDevice();
  };

  onMounted(() => {
    checkDevice();
    window.addEventListener('resize', handleResize);
  });

  onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
  });

  return {
    isMobile,
    isTablet,
    isDesktop,
    screenWidth
  };
} 