/**
 * Notification Utility Module
 * Handles the creation and management of notification popups
 * DISABLED: All notifications have been disabled as requested
 */

/**
 * Shows a notification message with specified type
 * @param {string} message - The message to display
 * @param {string} type - The type of notification (success, error, warning, info)
 */
export function showNotification(message, type = 'info') {
    // Notifications have been disabled
    // This function now does nothing but maintains its signature
    // to avoid breaking existing code
    return null;
}

/**
 * Gets the appropriate icon for the notification type
 * @param {string} type - The type of notification
 * @returns {string} - The Font Awesome icon name
 */
function getNotificationIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}