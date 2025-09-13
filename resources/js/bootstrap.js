import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

// Get CSRF token from meta tag and set in Axios headers
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Add auth token from localStorage if available
const authToken = localStorage.getItem('auth_token');
if (authToken) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
}

// Add interceptor to handle authentication and maintenance mode responses
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            console.warn('Authentication error: User not authenticated');
            // Redirect to login page or refresh token as needed
            // window.location.href = '/login';
        }
        
        // Handle maintenance mode (503 Service Unavailable)
        if (error.response && error.response.status === 503 && 
            error.response.data && error.response.data.error === 'maintenance') {
            console.warn('Maintenance mode is active');
            // Show maintenance mode popup
            showMaintenanceModal(error.response.data.message);
        }
        
        return Promise.reject(error);
    }
);

// Global maintenance mode modal function
function showMaintenanceModal(message) {
    // Remove any existing maintenance modal
    const existingModal = document.getElementById('maintenance-modal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Create maintenance modal
    const modal = document.createElement('div');
    modal.id = 'maintenance-modal';
    modal.innerHTML = `
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
            <div style="background: white; border-radius: 12px; padding: 32px; max-width: 400px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                <div style="text-align: center;">
                    <div style="margin: 0 auto 16px; width: 48px; height: 48px; background: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <svg style="width: 24px; height: 24px; color: #f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0 0 12px 0;">System Under Maintenance</h3>
                    <p style="color: #6b7280; margin: 0 0 24px 0; font-size: 14px;">${message || 'System is under maintenance, please wait'}</p>
                    <div style="display: flex; gap: 12px;">
                        <button onclick="closeMaintenanceModal()" style="flex: 1; background: #6b7280; color: white; border: none; padding: 12px 16px; border-radius: 6px; font-weight: 500; cursor: pointer;">
                            OK
                        </button>
                        <button onclick="redirectToLogin()" style="flex: 1; background: #3b82f6; color: white; border: none; padding: 12px 16px; border-radius: 6px; font-weight: 500; cursor: pointer;">
                            Go to Login
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Global functions for maintenance modal
window.closeMaintenanceModal = function() {
    const modal = document.getElementById('maintenance-modal');
    if (modal) {
        modal.remove();
    }
};

window.redirectToLogin = function() {
    window.location.href = '/login';
};
