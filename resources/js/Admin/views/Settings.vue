<template>
  <div class="settings">
    <!-- Settings Header -->
    <div class="page-header">
      <div class="header-title">
        <h2>System Settings</h2>
        <p>Configure system-wide settings and preferences</p>
      </div>
      <button @click="saveAllSettings" class="btn-primary">
        <i class="fas fa-save"></i> Save All Changes
      </button>
    </div>
    
    <!-- Settings Navigation Tabs -->
    <div class="settings-tabs">
      <button 
        v-for="tab in tabs" 
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="{ active: activeTab === tab.id }"
        class="tab-button"
      >
        <i :class="tab.icon"></i>
        <span>{{ tab.name }}</span>
      </button>
    </div>
    
    <!-- Settings Content -->
    <div class="settings-content">
      <!-- General Settings -->
      <div v-if="activeTab === 'general'" class="settings-section">
        <h3 class="section-title">General Settings</h3>
        
        <div class="settings-card">
          <div class="form-group">
            <label for="site_name">Site Name</label>
            <input 
              type="text" 
              id="site_name" 
              v-model="settings.general.siteName"
              placeholder="Aidel Learning Management System"
            >
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="site_logo">Site Logo</label>
              <div class="file-upload-container">
                <div class="file-input">
                  <input 
                    type="file" 
                    id="site_logo" 
                    ref="logoInput"
                    @change="handleLogoUpload"
                    accept="image/*"
                    class="hidden-input"
                  >
                  <button 
                    @click="$refs.logoInput.click()" 
                    class="file-button"
                    :disabled="isUploadingLogo"
                  >
                    <i :class="isUploadingLogo ? 'fas fa-spinner fa-spin' : 'fas fa-cloud-upload-alt'"></i>
                    {{ isUploadingLogo ? 'Uploading...' : 'Upload Logo' }}
                  </button>
                                    <span class="file-name">{{ settings.general.logoPath ? 'Logo uploaded' : 'No file chosen' }}</span>
                </div>
                <div v-if="settings.general.logoPath" class="file-actions">
                  <button @click="removeLogo" class="btn-remove" type="button">
                    <i class="fas fa-trash"></i> Remove Logo
                  </button>
                </div>
              </div>
            </div>
            
            <div class="form-group">
              <label for="favicon">Favicon</label>
              <div class="file-upload-container">
                <div class="file-input">
                  <input 
                    type="file" 
                    id="favicon" 
                    ref="faviconInput"
                    @change="handleFaviconUpload"
                    accept="image/*"
                    class="hidden-input"
                  >
                  <button 
                    @click="$refs.faviconInput.click()" 
                    class="file-button"
                    :disabled="isUploadingFavicon"
                  >
                    <i :class="isUploadingFavicon ? 'fas fa-spinner fa-spin' : 'fas fa-cloud-upload-alt'"></i>
                    {{ isUploadingFavicon ? 'Uploading...' : 'Upload Favicon' }}
                  </button>
                                    <span class="file-name">{{ settings.general.faviconPath ? 'Favicon uploaded' : 'No file chosen' }}</span>
                </div>
                <div v-if="settings.general.faviconPath" class="file-actions">
                  <button @click="removeFavicon" class="btn-remove" type="button">
                    <i class="fas fa-trash"></i> Remove Favicon
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="date_format">Date Format</label>
              <select id="date_format" v-model="settings.general.dateFormat">
                <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                <option value="MMM D, YYYY">MMM D, YYYY</option>
                <option value="D MMM, YYYY">D MMM, YYYY</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="maintenance_mode">Maintenance Mode</label>
              <div class="toggle-switch">
                <input 
                  type="checkbox" 
                  id="maintenance_mode" 
                  v-model="settings.general.maintenanceMode"
                  @change="onMaintenanceModeChange"
                >
                <label for="maintenance_mode"></label>
                <span>{{ settings.general.maintenanceMode ? 'Enabled' : 'Disabled' }}</span>
              </div>
              <div class="help-text">
                When enabled, only super admins can access the system. Students and teachers will see a maintenance message.
              </div>
            </div>
          </div>
        </div>
      </div>
      

      
      <!-- Email Settings -->
      <div v-if="activeTab === 'email'" class="settings-section">
        <h3 class="section-title">Email Configuration</h3>
        
        <div class="settings-card future-feature">
          <div class="form-group">
            <label for="mail_driver">Mail Driver</label>
            <select id="mail_driver" v-model="settings.email.driver">
              <option value="smtp">SMTP</option>
              <option value="sendmail">Sendmail</option>
              <option value="mailgun">Mailgun</option>
              <option value="ses">Amazon SES</option>
              <option value="postmark">Postmark</option>
            </select>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="mail_host">SMTP Host</label>
              <input 
                type="text" 
                id="mail_host" 
                v-model="settings.email.host"
                placeholder="smtp.example.com"
              >
            </div>
            
            <div class="form-group">
              <label for="mail_port">SMTP Port</label>
              <input 
                type="number" 
                id="mail_port" 
                v-model="settings.email.port"
                placeholder="587"
              >
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="mail_username">SMTP Username</label>
              <input 
                type="text" 
                id="mail_username" 
                v-model="settings.email.username"
                placeholder="username@example.com"
              >
            </div>
            
            <div class="form-group">
              <label for="mail_password">SMTP Password</label>
              <input 
                type="password" 
                id="mail_password" 
                v-model="settings.email.password"
                placeholder="••••••••"
              >
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="mail_encryption">Encryption</label>
              <select id="mail_encryption" v-model="settings.email.encryption">
                <option value="tls">TLS</option>
                <option value="ssl">SSL</option>
                <option value="none">None</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="mail_from_address">From Address</label>
              <input 
                type="email" 
                id="mail_from_address" 
                v-model="settings.email.fromAddress"
                placeholder="noreply@aidellms.com"
              >
            </div>
          </div>
          
          <div class="form-actions">
            <button @click="testEmailConnection" class="btn-outline">
              <i class="fas fa-paper-plane"></i> Test Connection
            </button>
          </div>
          
          <div class="future-overlay">
            <div class="future-text">
              <i class="fas fa-cog fa-spin"></i>
              <span>Future Development</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Payment Settings -->
      <div v-if="activeTab === 'payment'" class="settings-section">
        <h3 class="section-title">Payment Configuration</h3>
        
        <div class="settings-card future-feature">
          <div class="form-group">
            <label>Payment Gateways</label>
            <div class="payment-gateways">
              <div class="gateway-card">
                <div class="gateway-header">
                  <img src="/assets/images/stripe-logo.svg" alt="Stripe" class="gateway-logo">
                  <div class="toggle-switch small">
                    <input 
                      type="checkbox" 
                      id="stripe_enabled" 
                      v-model="settings.payment.stripe.enabled"
                    >
                    <label for="stripe_enabled"></label>
                  </div>
                </div>
                
                <div v-if="settings.payment.stripe.enabled" class="gateway-settings">
                  <div class="form-group">
                    <label for="stripe_key">Publishable Key</label>
                    <input 
                      type="text" 
                      id="stripe_key" 
                      v-model="settings.payment.stripe.publishableKey"
                      placeholder="pk_test_..."
                    >
                  </div>
                  
                  <div class="form-group">
                    <label for="stripe_secret">Secret Key</label>
                    <input 
                      type="password" 
                      id="stripe_secret" 
                      v-model="settings.payment.stripe.secretKey"
                      placeholder="sk_test_..."
                    >
                  </div>
                  
                  <div class="form-group">
                    <label for="stripe_webhook">Webhook Secret</label>
                    <input 
                      type="password" 
                      id="stripe_webhook" 
                      v-model="settings.payment.stripe.webhookSecret"
                      placeholder="whsec_..."
                    >
                  </div>
                </div>
              </div>
              
              <div class="gateway-card">
                <div class="gateway-header">
                  <img src="/assets/images/paypal-logo.svg" alt="PayPal" class="gateway-logo">
                  <div class="toggle-switch small">
                    <input 
                      type="checkbox" 
                      id="paypal_enabled" 
                      v-model="settings.payment.paypal.enabled"
                    >
                    <label for="paypal_enabled"></label>
                  </div>
                </div>
                
                <div v-if="settings.payment.paypal.enabled" class="gateway-settings">
                  <div class="form-group">
                    <label for="paypal_client_id">Client ID</label>
                    <input 
                      type="text" 
                      id="paypal_client_id" 
                      v-model="settings.payment.paypal.clientId"
                      placeholder="Client ID"
                    >
                  </div>
                  
                  <div class="form-group">
                    <label for="paypal_secret">Secret</label>
                    <input 
                      type="password" 
                      id="paypal_secret" 
                      v-model="settings.payment.paypal.secret"
                      placeholder="Secret"
                    >
                  </div>
                  
                  <div class="form-group">
                    <label for="paypal_mode">Mode</label>
                    <select id="paypal_mode" v-model="settings.payment.paypal.mode">
                      <option value="sandbox">Sandbox</option>
                      <option value="live">Live</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label>Currency Settings</label>
            <div class="form-row">
              <div class="form-group">
                <label for="currency">Default Currency</label>
                <select id="currency" v-model="settings.payment.currency">
                  <option value="USD">US Dollar ($)</option>
                  <option value="EUR">Euro (€)</option>
                  <option value="GBP">British Pound (£)</option>
                  <option value="JPY">Japanese Yen (¥)</option>
                  <option value="CAD">Canadian Dollar (CA$)</option>
                  <option value="AUD">Australian Dollar (A$)</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="currency_position">Currency Position</label>
                <select id="currency_position" v-model="settings.payment.currencyPosition">
                  <option value="before">Before Amount ($100)</option>
                  <option value="after">After Amount (100$)</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="future-overlay">
            <div class="future-text">
              <i class="fas fa-cog fa-spin"></i>
              <span>Future Development</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Security Settings -->
      <div v-if="activeTab === 'security'" class="settings-section">
        <h3 class="section-title">Security Settings</h3>
        
        <div class="settings-card">
          <div class="form-group">
            <label>Password Policy</label>
            <div class="checkbox-list">
              <div class="checkbox-item">
                <input 
                  type="checkbox" 
                  id="require_uppercase" 
                  v-model="settings.security.passwordPolicy.requireUppercase"
                >
                <label for="require_uppercase">Require at least one uppercase letter</label>
              </div>
              
              <div class="checkbox-item">
                <input 
                  type="checkbox" 
                  id="require_number" 
                  v-model="settings.security.passwordPolicy.requireNumber"
                >
                <label for="require_number">Require at least one number</label>
              </div>
              
              <div class="checkbox-item">
                <input 
                  type="checkbox" 
                  id="require_special" 
                  v-model="settings.security.passwordPolicy.requireSpecialChar"
                >
                <label for="require_special">Require at least one special character</label>
              </div>
            </div>
            
            <div class="form-group">
              <label for="min_password_length">Minimum Password Length</label>
              <input 
                type="number" 
                id="min_password_length" 
                v-model="settings.security.passwordPolicy.minLength"
                min="6"
                max="32"
              >
              <span class="help-text">Minimum number of characters required for passwords</span>
            </div>
          </div>
          
          <div class="form-group">
            <label>Login Security</label>
            <div class="form-row">
              <div class="form-group">
                <label for="max_login_attempts">Max Login Attempts</label>
                <input 
                  type="number" 
                  id="max_login_attempts" 
                  v-model="settings.security.maxLoginAttempts"
                  min="3"
                  max="10"
                >
                <span class="help-text">Number of failed attempts before lockout</span>
              </div>
              
              <div class="form-group">
                <label for="lockout_time">Lockout Time (minutes)</label>
                <input 
                  type="number" 
                  id="lockout_time" 
                  v-model="settings.security.lockoutTime"
                  min="5"
                  max="1440"
                >
                <span class="help-text">How long to lock account after max attempts</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Maintenance Mode Confirmation Modal -->
    <div v-if="showMaintenanceConfirmModal" 
         class="modal-overlay-purple"
         @click="cancelMaintenanceMode"
         @keydown.esc="cancelMaintenanceMode">
      <div class="maintenance-modal-content"
           @click.stop>
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Enable Maintenance Mode</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              Are you sure you want to enable maintenance mode? This will prevent all students and teachers from accessing the system.
            </p>
          </div>
          <div class="items-center px-4 py-3 flex gap-3">
            <button @click="cancelMaintenanceMode" 
                    class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
              Cancel
            </button>
            <button @click="confirmMaintenanceMode" 
                    class="flex-1 px-4 py-2 bg-indigo-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-300">
              OK
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import { useSiteSettings } from '../../composables/useSiteSettings.js';
import axios from 'axios';

// Settings tabs
const tabs = [
  { id: 'general', name: 'General', icon: 'fas fa-cog' },
  { id: 'email', name: 'Email', icon: 'fas fa-envelope' },
  { id: 'payment', name: 'Payment', icon: 'fas fa-credit-card' },
  { id: 'security', name: 'Security', icon: 'fas fa-shield-alt' }
];

const activeTab = ref('general');
const isLoading = ref(false);
const isFetching = ref(true);
const successMessage = ref('');
const errorMessage = ref('');
const showMaintenanceConfirmModal = ref(false);

// File upload references
const isUploadingLogo = ref(false);
const isUploadingFavicon = ref(false);

// Site settings for global logo updates
const { updateSetting } = useSiteSettings();

// Settings data structure - will be populated from API
const settings = reactive({
  general: {
    siteName: '',
    dateFormat: 'MM/DD/YYYY',
    maintenanceMode: false,
    logoPath: '',
    faviconPath: ''
  },
  email: {
    driver: 'smtp',
    host: '',
    port: 587,
    username: '',
    password: '',
    encryption: 'tls',
    fromAddress: ''
  },
  payment: {
    currency: 'USD',
    currencyPosition: 'before',
    stripe: {
      enabled: false,
      publishableKey: '',
      secretKey: '',
      webhookSecret: ''
    },
    paypal: {
      enabled: false,
      clientId: '',
      secret: '',
      mode: 'sandbox'
    }
  },
  security: {
    passwordPolicy: {
      requireUppercase: true,
      requireNumber: true,
      requireSpecialChar: false,
      minLength: 8
    },
    maxLoginAttempts: 5,
    lockoutTime: 30
  }
});

// Fetch settings from API on component mount
onMounted(async () => {
  await fetchSettings();
  document.addEventListener('keydown', handleEscKey);
});

// Methods to interact with API
const fetchSettings = async () => {
  isFetching.value = true;
  errorMessage.value = '';
  
  try {
    const response = await axios.get('/api/admin/settings');
    console.log('📥 Fetched settings from API:', response.data);
    
    if (response.data) {
      // If response.data is already the settings object (not wrapped in 'settings')
      if (response.data.general || response.data.email || response.data.payment || response.data.security) {
        // Merge received settings with default structure to handle missing properties
        for (const group in response.data) {
          if (settings[group]) {
            settings[group] = { ...settings[group], ...response.data[group] };
          }
        }
      } else if (response.data.settings) {
        // If wrapped in 'settings' key
        for (const group in response.data.settings) {
          if (settings[group]) {
            settings[group] = { ...settings[group], ...response.data.settings[group] };
          }
        }
      }
    }
    
    // Set initial previews after settings are loaded
    if (settings.general.logoPath) {
      logoPreview.value = settings.general.logoPath;
      console.log('🖼️ Logo preview set to:', logoPreview.value);
    }
    if (settings.general.faviconPath) {
      faviconPreview.value = settings.general.faviconPath;
      console.log('🎯 Favicon preview set to:', faviconPreview.value);
    }
    
  } catch (error) {
    console.error('Error fetching settings:', error);
    errorMessage.value = 'Failed to load settings from database. Using default values.';
  } finally {
    isFetching.value = false;
  }
};

const saveAllSettings = async () => {
  isLoading.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  
  try {
    console.log('💾 Saving all settings:', settings);
    
    // Send settings in the format expected by the backend
    const response = await axios.post('/api/admin/settings', { 
      settings: {
        general: settings.general,
        email: settings.email,
        payment: settings.payment,
        security: settings.security
      }
    });
    
    console.log('✅ Settings saved successfully:', response.data);
    
    // Update global site settings for live reflection
    updateSetting('general', 'siteName', settings.general.siteName);
    updateSetting('general', 'logoPath', settings.general.logoPath);
    updateSetting('general', 'faviconPath', settings.general.faviconPath);
    
    successMessage.value = 'All settings saved successfully!';
    
    // Clear success message after 3 seconds
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
  } catch (error) {
    console.error('❌ Error saving settings:', error);
    errorMessage.value = error.response?.data?.message || 'Failed to save settings. Please try again.';
  } finally {
    isLoading.value = false;
  }
};

const testEmailConnection = () => {
  // Future development - email functionality not implemented yet
  console.log('Email functionality will be implemented in future development');
};

// Handle maintenance mode toggle - with confirmation
const onMaintenanceModeChange = () => {
  if (settings.general.maintenanceMode) {
    showMaintenanceConfirmModal.value = true;
    // Temporarily revert the toggle until user confirms
    settings.general.maintenanceMode = false;
  } else {
    // Disabling maintenance mode - no confirmation needed
    saveMaintenanceModeSetting();
  }
};

// Save maintenance mode setting immediately
const saveMaintenanceModeSetting = async () => {
  try {
    await axios.post('/api/admin/settings', { 
      settings: {
        general: {
          maintenanceMode: settings.general.maintenanceMode
        }
      }
    });
    
    successMessage.value = `Maintenance mode ${settings.general.maintenanceMode ? 'enabled' : 'disabled'} successfully!`;
    
    // Clear success message after 3 seconds
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
  } catch (error) {
    console.error('Error saving maintenance mode:', error);
    errorMessage.value = 'Failed to save maintenance mode setting. Please try again.';
    
    // Revert the change on error
    settings.general.maintenanceMode = !settings.general.maintenanceMode;
  }
};

// Confirm maintenance mode enable
const confirmMaintenanceMode = () => {
  settings.general.maintenanceMode = true;
  showMaintenanceConfirmModal.value = false;
  saveMaintenanceModeSetting();
};

// Cancel maintenance mode enable
const cancelMaintenanceMode = () => {
  settings.general.maintenanceMode = false;
  showMaintenanceConfirmModal.value = false;
};

// Handle ESC key press
const handleEscKey = (event) => {
  if (event.key === 'Escape' && showMaintenanceConfirmModal.value) {
    cancelMaintenanceMode();
  }
};

// File upload functions
const handleLogoUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
    errorMessage.value = 'Please select a valid image file for the logo.';
    return;
  }
  
  // Validate file size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    errorMessage.value = 'Logo file size must be less than 5MB.';
    return;
  }
  
  isUploadingLogo.value = true;
  errorMessage.value = '';
  
  try {
    // Upload file
    const formData = new FormData();
    formData.append('logo', file);
    
    const response = await axios.post('/api/admin/upload-logo', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    console.log('🖼️ Logo upload response:', response.data);
    
    // Update settings with new logo path
    settings.general.logoPath = response.data.logoPath;
    console.log('📁 Logo path set to:', settings.general.logoPath);
    
    // Update global site settings for live reflection
    updateSetting('general', 'logoPath', response.data.logoPath);
    
    successMessage.value = 'Logo uploaded successfully!';
    
    // Clear success message after 3 seconds
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
    
  } catch (error) {
    console.error('Error uploading logo:', error);
    errorMessage.value = error.response?.data?.message || 'Failed to upload logo. Please try again.';
  } finally {
    isUploadingLogo.value = false;
    // Clear the input
    event.target.value = '';
  }
};

const handleFaviconUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
    errorMessage.value = 'Please select a valid image file for the favicon.';
    return;
  }
  
  // Validate file size (max 1MB)
  if (file.size > 1 * 1024 * 1024) {
    errorMessage.value = 'Favicon file size must be less than 1MB.';
    return;
  }
  
  isUploadingFavicon.value = true;
  errorMessage.value = '';
  
  try {
    // Upload file
    const formData = new FormData();
    formData.append('favicon', file);
    
    const response = await axios.post('/api/admin/upload-favicon', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    console.log('🎯 Favicon upload response:', response.data);
    
    // Update settings with new favicon path
    settings.general.faviconPath = response.data.faviconPath;
    console.log('📁 Favicon path set to:', settings.general.faviconPath);
    
    // Update global site settings for live reflection
    updateSetting('general', 'faviconPath', response.data.faviconPath);
    
    successMessage.value = 'Favicon uploaded successfully!';
    
    // Clear success message after 3 seconds
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
    
  } catch (error) {
    console.error('Error uploading favicon:', error);
    errorMessage.value = error.response?.data?.message || 'Failed to upload favicon. Please try again.';
  } finally {
    isUploadingFavicon.value = false;
    // Clear the input
    event.target.value = '';
  }
};

const removeLogo = async () => {
  try {
    await axios.delete('/api/admin/remove-logo');
    settings.general.logoPath = '';
    
    // Update global site settings
    updateSetting('general', 'logoPath', '');
    
    successMessage.value = 'Logo removed successfully!';
    
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
  } catch (error) {
    console.error('Error removing logo:', error);
    errorMessage.value = 'Failed to remove logo. Please try again.';
  }
};

const removeFavicon = async () => {
  try {
    await axios.delete('/api/admin/remove-favicon');
    settings.general.faviconPath = '';
    
    // Update global site settings
    updateSetting('general', 'faviconPath', '');
    
    successMessage.value = 'Favicon removed successfully!';
    
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
  } catch (error) {
    console.error('Error removing favicon:', error);
    errorMessage.value = 'Failed to remove favicon. Please try again.';
  }
};





// Remove event listener when component unmounts
onUnmounted(() => {
  document.removeEventListener('keydown', handleEscKey);
});

// Helper functions for handling different setting types
const parseJsonSetting = (value, defaultValue = []) => {
  if (Array.isArray(value)) return value;
  if (typeof value === 'string') {
    try {
      return JSON.parse(value);
    } catch (e) {
      return defaultValue;
    }
  }
  return defaultValue;
};

const isBooleanSetting = (value) => {
  if (typeof value === 'boolean') return value;
  return value === '1' || value === 1 || value === 'true';
};
</script>

<style scoped>
/* Settings Page Styles */
.settings {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
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

/* Settings Tabs */
.settings-tabs {
  display: flex;
  background-color: white;
  border-radius: 12px;
  padding: 5px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  margin-bottom: 20px;
  overflow-x: auto;
}

.tab-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border: none;
  background: none;
  color: #6b7280;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.tab-button:hover {
  color: #8b5cf6;
}

.tab-button.active {
  color: #8b5cf6;
  position: relative;
}

.tab-button.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 20px;
  height: 3px;
  background-color: #8b5cf6;
  border-radius: 1.5px;
}

.tab-button i {
  font-size: 1rem;
}

/* Settings Content */
.settings-content {
  flex: 1;
}

.settings-section {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.section-title {
  font-size: 1.2rem;
  font-weight: 600;
  color: #374151;
  margin: 0 0 16px 0;
}

.settings-card {
  background-color: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  position: relative;
}

/* Form Styles */
.form-group {
  margin-bottom: 20px;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-row {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.form-row .form-group {
  flex: 1;
  margin-bottom: 0;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  color: #4b5563;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="email"],
.form-group input[type="password"],
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.95rem;
  color: #1f2937;
  background-color: white;
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
  outline: none;
}

.help-text {
  display: block;
  font-size: 0.8rem;
  color: #6b7280;
  margin-top: 4px;
}

/* File Upload */
.file-upload-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.file-input {
  display: flex;
  align-items: center;
  gap: 10px;
}

.hidden-input {
  display: none;
}

.file-button {
  background-color: #f3f4f6;
  border: 1px solid #e5e7eb;
  padding: 8px 14px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s ease;
}

.file-button:hover:not(:disabled) {
  background-color: #e5e7eb;
}

.file-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.file-name {
  font-size: 0.9rem;
  color: #6b7280;
}

.file-actions {
  margin-top: 8px;
}

.btn-remove {
  background-color: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
  padding: 6px 12px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-remove:hover {
  background-color: #fee2e2;
  border-color: #f87171;
}



/* Toggle Switch */
.toggle-switch {
  display: inline-flex;
  align-items: center;
  gap: 10px;
}

.toggle-switch input {
  display: none;
}

.toggle-switch label {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 26px;
  background-color: #e5e7eb;
  border-radius: 13px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.toggle-switch label::after {
  content: '';
  position: absolute;
  top: 3px;
  left: 3px;
  width: 20px;
  height: 20px;
  background-color: white;
  border-radius: 50%;
  transition: all 0.3s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.toggle-switch input:checked + label {
  background-color: #8b5cf6;
}

.toggle-switch input:checked + label::after {
  transform: translateX(24px);
}

.toggle-switch span {
  font-size: 0.9rem;
  color: #4b5563;
}

/* Small Toggle Switch */
.toggle-switch.small label {
  width: 40px;
  height: 22px;
  border-radius: 11px;
}

.toggle-switch.small label::after {
  width: 16px;
  height: 16px;
}

.toggle-switch.small input:checked + label::after {
  transform: translateX(18px);
}



/* Code Editor */
.code-editor {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
}

.code-editor textarea {
  width: 100%;
  padding: 12px;
  font-family: monospace;
  font-size: 0.9rem;
  color: #1f2937;
  background-color: #f9fafb;
  border: none;
  resize: vertical;
}

/* Payment Gateways */
.payment-gateways {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.gateway-card {
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  overflow: hidden;
}

.gateway-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  background-color: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.gateway-logo {
  height: 30px;
}

.gateway-settings {
  padding: 20px;
}

/* Checkbox List */
.checkbox-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 16px;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 10px;
}

.checkbox-item input[type="checkbox"] {
  width: 16px;
  height: 16px;
  cursor: pointer;
}

.checkbox-item label {
  margin-bottom: 0;
  cursor: pointer;
}

/* Form Actions */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 20px;
}

/* Future Development Overlay */
.future-feature {
  position: relative;
}

.future-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  z-index: 10;
}

.future-text {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 24px;
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.95rem;
  box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
  animation: pulse 2s infinite;
}

.future-text i {
  font-size: 1.1rem;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

/* Responsive styles */
@media (max-width: 991px) {
  .form-row {
    flex-direction: column;
    gap: 16px;
  }
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .btn-primary {
    width: 100%;
    justify-content: center;
  }
  
  .settings-tabs {
    padding: 0;
    background-color: transparent;
    box-shadow: none;
  }
  
  .tab-button {
    padding: 10px 15px;
    font-size: 0.9rem;
    flex: 1;
    justify-content: center;
    background-color: white;
    border-radius: 8px;
  }
  

  
  /* Mobile optimized overlay for settings */
  .future-text {
    padding: 12px 16px;
    font-size: 0.875rem;
    gap: 8px;
  }
  
  .future-text i {
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .settings-card {
    padding: 16px;
  }
  
  .future-text {
    padding: 10px 14px;
    font-size: 0.8rem;
    gap: 6px;
  }
  
  .future-text i {
    font-size: 0.9rem;
  }
  
  .future-text span {
    display: none;
  }
  
  .future-text::after {
    content: 'Future';
  }
}

/* Modal overlay with purple gradient background like login modal */
.modal-overlay-purple {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.7), rgba(91, 33, 182, 0.6));
  backdrop-filter: blur(4px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
  0% { opacity: 0; }
  100% { opacity: 1; }
}

/* Modal content styling to match login modal */
.maintenance-modal-content {
  background: linear-gradient(to bottom, #ffffff, #f8f7ff);
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(91, 33, 182, 0.25), 0 2px 10px rgba(0, 0, 0, 0.05), 0 0 0 1px rgba(255, 255, 255, 0.8) inset;
  width: 450px;
  max-width: 90vw;
  padding: 20px;
  animation: slideUp 0.4s cubic-bezier(0.19, 1, 0.22, 1) forwards;
}

@keyframes slideUp {
  0% { opacity: 0; transform: translateY(20px); }
  100% { opacity: 1; transform: translateY(0); }
}
</style>