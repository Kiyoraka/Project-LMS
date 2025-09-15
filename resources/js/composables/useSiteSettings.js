import { ref, computed } from 'vue';
import axios from 'axios';

// Global reactive state for site settings
const siteSettings = ref({
  general: {
    siteName: 'LMS Prototype',
    logoPath: '',
    faviconPath: ''
  }
});

const isLoading = ref(false);

export function useSiteSettings() {
  // Computed properties for easy access
  const siteName = computed(() => siteSettings.value.general?.siteName || 'LMS Prototype');
  const logoUrl = computed(() => {
    const path = siteSettings.value.general?.logoPath;
    if (!path) return '/logo.png'; // Use new logo as fallback
    if (path.startsWith('data:')) return path; // Base64
    if (path.startsWith('http')) return path; // Full URL (including our public-logo endpoint)
    return window.location.origin + path; // Relative path
  });
  const faviconUrl = computed(() => {
    const path = siteSettings.value.general?.faviconPath;
    if (!path) return '/favicon.png'; // Use new favicon as fallback
    if (path.startsWith('data:')) return path; // Base64
    if (path.startsWith('http')) return path; // Full URL
    return window.location.origin + path; // Relative path
  });

  // Fetch settings from API
  const fetchSettings = async () => {
    if (isLoading.value) return; // Prevent multiple calls
    
    isLoading.value = true;
    try {
      // Use public site-settings endpoint (works for all users)
      const response = await axios.get('/api/site-settings');
      
      if (response.data) {
        // Merge with existing settings
        siteSettings.value = {
          ...siteSettings.value,
          ...response.data
        };
        
        // Update favicon in DOM if changed
        updateFavicon();
      }
    } catch (error) {
      console.error('Failed to fetch site settings:', error);
    } finally {
      isLoading.value = false;
    }
  };

  // Update favicon in the DOM
  const updateFavicon = () => {
    const favicon = faviconUrl.value;
    const links = document.querySelectorAll('link[rel*="icon"]');
    
    // Remove existing favicons
    links.forEach(link => link.remove());
    
    // Add new favicon
    const link = document.createElement('link');
    link.rel = 'icon';
    link.href = favicon;
    document.head.appendChild(link);
  };

  // Update specific setting
  const updateSetting = (group, key, value) => {
    if (!siteSettings.value[group]) {
      siteSettings.value[group] = {};
    }
    siteSettings.value[group][key] = value;
    
    // Update favicon if it was changed
    if (group === 'general' && key === 'faviconPath') {
      updateFavicon();
    }
  };

  // Initialize settings on first use
  const initializeSettings = async () => {
    if (!isLoading.value) {
      await fetchSettings();
    }
  };

  return {
    // State
    siteSettings,
    isLoading,
    
    // Computed
    siteName,
    logoUrl,
    faviconUrl,
    
    // Methods
    fetchSettings,
    updateSetting,
    initializeSettings,
    updateFavicon
  };
} 