<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h3>Change Password</h3>
        <button class="close-button" @click="$emit('close')">&times;</button>
      </div>
      
      <div class="modal-body">
        <form @submit.prevent="changePassword">
          <div class="form-group">
            <label for="current-password">Current Password</label>
            <input 
              type="password" 
              id="current-password"
              v-model="passwords.current"
              class="form-input"
              required
            >
          </div>
          
          <div class="form-group">
            <label for="new-password">New Password</label>
            <div class="password-input-wrapper">
              <input 
                :type="showNewPassword ? 'text' : 'password'" 
                id="new-password"
                v-model="passwords.new"
                class="form-input"
                required
                minlength="8"
              >
              <button 
                type="button" 
                class="toggle-password" 
                @click="showNewPassword = !showNewPassword"
              >
                <i class="fas" :class="showNewPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
              </button>
            </div>
            <div class="password-strength" :class="passwordStrengthClass">
              <div class="strength-bar"></div>
            </div>
            <small class="password-requirements">
              Password requirements are based on system security policy
            </small>
          </div>
          
          <div class="form-group">
            <label for="confirm-password">Confirm New Password</label>
            <div class="password-input-wrapper">
              <input 
                :type="showConfirmPassword ? 'text' : 'password'" 
                id="confirm-password"
                v-model="passwords.confirm"
                class="form-input"
                required
              >
              <button 
                type="button" 
                class="toggle-password" 
                @click="showConfirmPassword = !showConfirmPassword"
              >
                <i class="fas" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
              </button>
            </div>
            <small 
              v-if="passwords.new && passwords.confirm && passwords.new !== passwords.confirm" 
              class="password-mismatch"
            >
              Passwords do not match
            </small>
          </div>
          
          <div class="form-actions">
            <button type="button" class="btn btn-cancel" @click="$emit('close')">Cancel</button>
            <button 
              type="submit" 
              class="btn btn-save" 
              :disabled="!isFormValid || isSubmitting"
            >
              <i v-if="isSubmitting" class="fas fa-spinner fa-spin"></i>
              {{ isSubmitting ? 'Changing...' : 'Change Password' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import axios from 'axios';

const emit = defineEmits(['close', 'password-changed', 'show-toast']);

// Password visibility toggles
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Form state
const passwords = reactive({
  current: '',
  new: '',
  confirm: ''
});

const isSubmitting = ref(false);

// Password strength calculation
const passwordStrength = computed(() => {
  if (!passwords.new) return 0;
  
  let strength = 0;
  
  // Length check
  if (passwords.new.length >= 8) strength += 25;
  
  // Complexity checks
  if (/[A-Z]/.test(passwords.new)) strength += 25; // Has uppercase
  if (/[a-z]/.test(passwords.new)) strength += 25; // Has lowercase
  if (/[0-9]/.test(passwords.new)) strength += 12.5; // Has number
  if (/[^A-Za-z0-9]/.test(passwords.new)) strength += 12.5; // Has special char
  
  return Math.min(100, strength);
});

const passwordStrengthClass = computed(() => {
  const strength = passwordStrength.value;
  if (strength < 25) return 'very-weak';
  if (strength < 50) return 'weak';
  if (strength < 75) return 'medium';
  return 'strong';
});

// Form validation
const isFormValid = computed(() => {
  return passwords.current && 
         passwords.new && 
         passwords.new.length >= 8 && 
         passwords.confirm && 
         passwords.new === passwords.confirm;
});

// Submit handler
const changePassword = async () => {
  if (!isFormValid.value) return;
  
  isSubmitting.value = true;
  
  try {
    const response = await axios.post('/api/teacher/change-password', {
        current_password: passwords.current,
        new_password: passwords.new,
        new_password_confirmation: passwords.confirm
    });

      emit('password-changed');
      emit('close');
      
      // Reset form
      passwords.current = '';
      passwords.new = '';
      passwords.confirm = '';
  } catch (error) {
    console.error('Error changing password:', error);
    
    // Emit toast event instead of alert
    let errorMessage = 'An error occurred while changing your password. Please try again.';
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage = error.response.data.message;
    }
    
    emit('show-toast', {
      type: 'error',
      message: errorMessage,
      title: 'Password Change Failed'
    });
  } finally {
    isSubmitting.value = false;
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
  max-width: 450px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  overflow: hidden;
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

/* Form styles */
.form-group {
  margin-bottom: 20px;
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
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.password-input-wrapper {
  position: relative;
}

.toggle-password {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 0;
  font-size: 1rem;
}

.password-requirements {
  display: block;
  margin-top: 6px;
  font-size: 0.8rem;
  color: #6b7280;
}

.password-mismatch {
  display: block;
  margin-top: 6px;
  font-size: 0.8rem;
  color: #ef4444;
}

.password-strength {
  height: 4px;
  background-color: #e5e7eb;
  border-radius: 2px;
  margin-top: 8px;
  overflow: hidden;
}

.password-strength .strength-bar {
  height: 100%;
  width: 0;
  transition: width 0.3s ease;
}

.very-weak .strength-bar {
  width: 25%;
  background-color: #ef4444;
}

.weak .strength-bar {
  width: 50%;
  background-color: #f59e0b;
}

.medium .strength-bar {
  width: 75%;
  background-color: #10b981;
}

.strong .strength-bar {
  width: 100%;
  background-color: #059669;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 30px;
}

/* Button styles */
.btn {
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
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
  background-color: #3b82f6;
  color: white;
  border: 1px solid #3b82f6;
}

.btn-save:hover:not(:disabled) {
  background-color: #2563eb;
}

.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>