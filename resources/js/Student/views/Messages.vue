<template>
  <div class="messages-container">
    <!-- Message View Modal -->
    <div v-if="showMessageModal" class="modal-overlay" @click="closeMessageModal">
      <div class="modal-container" @click.stop>
        <div class="modal-header">
          <h3>{{ currentMessage.subject }}</h3>
          <button class="modal-close" @click="closeMessageModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <div class="message-info">
            <div class="info-row">
              <span class="info-label">From:</span>
              <span class="info-value">{{ currentMessage.teacher_name }}</span>
            </div>
            <div class="info-row" v-if="currentMessage.course_name && currentMessage.course_name !== 'General'">
              <span class="info-label">Course:</span>
              <span class="info-value">{{ currentMessage.course_name }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Date:</span>
              <span class="info-value">{{ currentMessage.formatted_date }}</span>
            </div>
            <div class="info-row" v-if="currentMessage.is_important">
              <span class="important-badge">
                <i class="fas fa-star"></i> Important
              </span>
            </div>
          </div>
          
          <div class="message-content">
            <p>{{ currentMessage.message }}</p>
          </div>
        </div>
        
        <div class="modal-footer" v-if="currentMessage.teacher_id">
          <button class="btn btn-outline-primary" @click="replyToCurrentMessage">
            <i class="fas fa-reply me-1"></i>
            Reply
          </button>
          <button class="btn btn-secondary" @click="closeMessageModal">
            <i class="fas fa-times me-1"></i>
            Close
          </button>
        </div>
        <div class="modal-footer" v-else>
          <button class="btn btn-secondary" @click="closeMessageModal">
            <i class="fas fa-times me-1"></i>
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div v-if="toast.show" :class="['toast-notification', toast.type]" @click="hideToast">
      <div class="toast-content">
        <i :class="toastIcon"></i>
        <span>{{ toast.message }}</span>
      </div>
      <button class="toast-close" @click.stop="hideToast">×</button>
    </div>

    <!-- Header Section -->
    <div class="messages-page-header">
      <div class="header-content">
        <h1>Messages</h1>
        <p>View messages and announcements from your teachers</p>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
      <!-- Total Messages Card -->
      <div class="stat-card bg-purple">
        <div class="stat-icon">
          <i class="fas fa-envelope"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ statsLoading ? '...' : stats.total_messages }}</h3>
          <p class="stat-label">Total Messages</p>
        </div>
      </div>
      
      <!-- Unread Messages Card -->
      <div class="stat-card bg-green">
        <div class="stat-icon">
          <i class="fas fa-envelope-open-text"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ statsLoading ? '...' : stats.unread_messages }}</h3>
          <p class="stat-label">Unread</p>
        </div>
      </div>
      
      <!-- This Week Card -->
      <div class="stat-card bg-blue">
        <div class="stat-icon">
          <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ statsLoading ? '...' : stats.messages_this_week }}</h3>
          <p class="stat-label">This Week</p>
        </div>
      </div>
      
      <!-- Important Messages Card -->
      <div class="stat-card bg-orange">
        <div class="stat-icon">
          <i class="fas fa-star"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ statsLoading ? '...' : stats.important_messages }}</h3>
          <p class="stat-label">Important</p>
        </div>
      </div>
    </div>

    <!-- Messages Header -->
    <div class="messages-header">
      <div class="header-content">
        <h1>Messages</h1>
        <div class="header-actions">
          <div class="search-container">
            <input 
              type="text" 
              placeholder="Search messages..." 
              class="search-input"
              v-model="searchQuery"
            >
            <i class="fas fa-search search-icon"></i>
          </div>
        </div>
      </div>
      
      <!-- Tab Navigation -->
      <div class="tab-navigation">
        <button 
          @click="activeTab = 'inbox'"
          class="tab-btn"
          :class="{ active: activeTab === 'inbox' }"
        >
          <i class="fas fa-inbox"></i>
          Inbox
          <span v-if="stats.unread_messages > 0" class="notification-badge">
            {{ stats.unread_messages }}
          </span>
        </button>
        <button 
          @click="activeTab = 'sent'"
          class="tab-btn"
          :class="{ active: activeTab === 'sent' }"
        >
          <i class="fas fa-paper-plane"></i>
          Sent
        </button>
        <button 
          @click="activeTab = 'compose'"
          class="tab-btn"
          :class="{ active: activeTab === 'compose' }"
        >
          <i class="fas fa-edit"></i>
          Compose
        </button>
      </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
      <!-- Inbox Tab -->
      <div v-if="activeTab === 'inbox'" class="tab-pane">
        <!-- Filters Section -->
        <div class="filters-section">
          <div class="filter-controls">
            <select v-model="inboxSelectedStatus" class="filter-select">
              <option value="">All Messages</option>
              <option value="unread">Unread</option>
              <option value="read">Read</option>
              <option value="important">Important</option>
            </select>
            
            <select v-model="inboxSelectedTeacher" class="filter-select">
              <option value="">All Teachers</option>
              <option v-for="teacher in inboxTeachers" :key="teacher" :value="teacher">
                {{ teacher }}
              </option>
            </select>
            
            <select v-model="inboxSortBy" class="filter-select">
              <option value="newest">Newest First</option>
              <option value="oldest">Oldest First</option>
              <option value="priority">By Priority</option>
            </select>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="inboxLoading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading your messages...</p>
        </div>

        <!-- Messages List -->
        <div v-else-if="receivedMessages.length > 0" class="messages-grid">
          <div 
            v-for="message in receivedMessages" 
            :key="message.id"
            class="message-card"
            @click="openReceivedMessage(message)"
          >
            <!-- Message Header -->
            <div class="message-header">
              <div class="message-avatar">
                {{ message.teacher_name.charAt(0) }}
              </div>
              <div class="message-badges">
                <span v-if="!message.is_read" class="message-badge unread">New</span>
                <span v-if="message.is_important" class="message-badge important">Priority</span>
                <span v-else class="message-badge read">Read</span>
              </div>
            </div>

            <!-- Message Body -->
            <div class="message-body">
              <h5 class="message-subject">{{ message.subject }}</h5>
              <p class="message-preview">{{ truncateText(message.message, 80) }}</p>
              
              <div class="message-meta">
                <div class="meta-item">
                  <i class="fas fa-user me-1"></i>
                  <span>{{ message.teacher_name }}</span>
                </div>
                <div class="meta-item">
                  <i class="fas fa-clock me-1"></i>
                  <span>{{ formatTimeAgo(message.created_at) }}</span>
                </div>
                <div class="meta-item" v-if="message.course_name">
                  <i class="fas fa-book me-1"></i>
                  <span>{{ message.course_name }}</span>
                </div>
              </div>
            </div>

            <!-- Message Footer -->
            <div class="message-footer">
              <button class="btn btn-primary btn-action" @click.stop="openReceivedMessage(message)">
                <i class="fas fa-eye me-1"></i>
                View
              </button>
              <button class="btn btn-outline-primary btn-action" @click.stop="replyToMessage(message)">
                <i class="fas fa-reply me-1"></i>
                Reply
              </button>
              <button class="btn btn-danger btn-action" @click.stop="deleteMessage(message, 'inbox')">
                <i class="fas fa-trash me-1"></i>
                Delete
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="empty-state">
          <div class="empty-icon">
            <i class="fas fa-inbox"></i>
          </div>
          <h3>No Messages Found</h3>
          <p v-if="hasInboxFilters">No messages match your current filters. Try adjusting your search criteria.</p>
          <p v-else>You haven't received any messages yet.</p>
        </div>
      </div>

      <!-- Sent Tab -->
      <div v-if="activeTab === 'sent'" class="tab-pane">
        <!-- Filters Section -->
        <div class="filters-section">
          <div class="filter-controls">
            <select v-model="sentSelectedTeacher" class="filter-select">
              <option value="">All Teachers</option>
              <option v-for="teacher in sentTeachers" :key="teacher" :value="teacher">
                {{ teacher }}
              </option>
            </select>
            
            <select v-model="sentSortBy" class="filter-select">
              <option value="newest">Newest First</option>
              <option value="oldest">Oldest First</option>
            </select>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="sentLoading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading sent messages...</p>
        </div>

        <!-- Sent Messages List -->
        <div v-else-if="sentMessages.length > 0" class="messages-grid">
          <div 
            v-for="message in sentMessages" 
            :key="message.id"
            class="message-card"
            @click="openSentMessage(message)"
          >
            <!-- Message Header -->
            <div class="message-header">
              <div class="message-avatar">
                {{ message.teacher_name.charAt(0) }}
              </div>
              <div class="message-badges">
                <span class="message-badge sent">Sent</span>
              </div>
            </div>

            <!-- Message Body -->
            <div class="message-body">
              <h5 class="message-subject">{{ message.subject }}</h5>
              <p class="message-preview">{{ truncateText(message.message, 80) }}</p>
              
              <div class="message-meta">
                <div class="meta-item">
                  <i class="fas fa-user me-1"></i>
                  <span>{{ message.teacher_name }}</span>
                </div>
                <div class="meta-item">
                  <i class="fas fa-clock me-1"></i>
                  <span>{{ formatTimeAgo(message.created_at) }}</span>
                </div>
                <div class="meta-item" v-if="message.course_name">
                  <i class="fas fa-book me-1"></i>
                  <span>{{ message.course_name }}</span>
                </div>
              </div>
            </div>

            <!-- Message Footer -->
            <div class="message-footer">
              <button class="btn btn-primary btn-action" @click.stop="openSentMessage(message)">
                <i class="fas fa-eye me-1"></i>
                View
              </button>
              <button class="btn btn-danger btn-action" @click.stop="deleteMessage(message, 'sent')">
                <i class="fas fa-trash me-1"></i>
                Delete
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="empty-state">
          <div class="empty-icon">
            <i class="fas fa-paper-plane"></i>
          </div>
          <h3>No Sent Messages</h3>
          <p v-if="hasSentFilters">No sent messages match your current filters.</p>
          <p v-else>You haven't sent any messages yet.</p>
        </div>
      </div>

      <!-- Compose Tab -->
      <div v-if="activeTab === 'compose'" class="tab-pane">
        <div class="compose-container">
          <h3>Compose New Message</h3>
          
          <div class="form-row">
            <div class="form-group">
              <label for="teacher">Teacher</label>
              <div v-if="isReplying && replyingToTeacher" class="reply-teacher-info">
                <div class="teacher-display">
                  <i class="fas fa-user-circle me-2"></i>
                  <span>{{ replyingToTeacher.name }} ({{ replyingToTeacher.email }})</span>
                  <button @click="cancelReply" class="btn btn-sm btn-outline-secondary ms-auto">
                    <i class="fas fa-times me-1"></i>
                    Change
                  </button>
                </div>
              </div>
              <select v-else v-model="composeData.teacher_id" id="teacher" class="form-select" required>
                <option value="">Select a teacher</option>
                <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                  {{ teacher.name }} ({{ teacher.email }})
                </option>
              </select>
            </div>

            <div class="form-group">
              <label for="course">Course (Optional)</label>
              <select v-model="composeData.course_id" id="course" class="form-select">
                <option value="">Select a course</option>
                <option v-for="course in courses" :key="course.id" :value="course.id">
                  {{ course.title }}
                </option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" v-model="composeData.subject" id="subject" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="message">Message</label>
            <textarea v-model="composeData.message" id="message" class="form-control" rows="8" required></textarea>
          </div>

          <div class="form-actions">
            <button @click="sendMessage" class="btn btn-primary" :disabled="composing">
              <i class="fas fa-paper-plane"></i>
              {{ composing ? 'Sending...' : 'Send Message' }}
            </button>
            <button @click="clearCompose" class="btn btn-secondary">
              <i class="fas fa-times"></i>
              Clear
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StudentMessages',
  data() {
    return {
      activeTab: 'inbox',
      searchQuery: '',
      stats: {
        total_messages: 0,
        unread_messages: 0,
        messages_this_week: 0,
        important_messages: 0
      },
      statsLoading: false,
      
      // Inbox data
      receivedMessages: [],
      inboxLoading: false,
      inboxSelectedStatus: '',
      inboxSelectedTeacher: '',
      inboxSortBy: 'newest',
      inboxTeachers: [],
      
      // Sent data
      sentMessages: [],
      sentLoading: false,
      sentSelectedTeacher: '',
      sentSortBy: 'newest',
      sentTeachers: [],
      
      // Compose data
      composeData: {
        teacher_id: '',
        subject: '',
        course_id: '',
        message: ''
      },
      composing: false,
      teachers: [],
      courses: [],
      isReplying: false,
      replyingToTeacher: null,

      // Toast Notification
      toast: {
        show: false,
        message: '',
        type: 'info' // 'info', 'success', 'error', 'warning'
      },

      // Message Modal
      showMessageModal: false,
      currentMessage: {}
    }
  },
  
  computed: {
    hasInboxFilters() {
      return this.searchQuery || this.inboxSelectedStatus || this.inboxSelectedTeacher;
    },
    
    hasSentFilters() {
      return this.searchQuery || this.sentSelectedTeacher;
    },

    toastIcon() {
      switch (this.toast.type) {
        case 'success':
          return 'fas fa-check-circle';
        case 'error':
          return 'fas fa-times-circle';
        case 'warning':
          return 'fas fa-exclamation-triangle';
        default:
          return 'fas fa-info-circle';
      }
    }
  },
  
  mounted() {
    this.testDebugEndpoint();
    this.loadStats();
    this.loadReceivedMessages();
    this.loadTeachers();
    this.loadCourses();
  },

  watch: {
    activeTab(newTab) {
      if (newTab === 'sent' && this.sentMessages.length === 0) {
        this.loadSentMessages();
      }
    }
  },
  
  methods: {
    async testDebugEndpoint() {
      try {
        const response = await fetch('/api/student/test-debug');
        const result = await response.json();
        
        if (!response.ok) {
          // Handle error silently
        }
      } catch (error) {
        // Handle error silently
      }
    },

    async loadStats() {
      this.statsLoading = true;
      try {
        const response = await fetch('/api/student/message-stats');
        if (response.ok) {
          this.stats = await response.json();
        } else {
          // Set default stats on error
          this.stats = {
            total_messages: 0,
            unread_messages: 0,
            messages_this_week: 0,
            important_messages: 0
          };
        }
      } catch (error) {
        // Set default stats on error
        this.stats = {
          total_messages: 0,
          unread_messages: 0,
          messages_this_week: 0,
          important_messages: 0
        };
      } finally {
        this.statsLoading = false;
      }
    },
    
    async loadReceivedMessages() {
      this.inboxLoading = true;
      try {
        const response = await fetch('/api/student/messages');
        if (response.ok) {
          const result = await response.json();
          this.receivedMessages = result.data || result;
          
          // Extract unique teachers for filter
          if (this.receivedMessages.length > 0) {
            const teachersSet = new Set(this.receivedMessages.map(msg => msg.teacher_name));
            this.inboxTeachers = [...teachersSet];
          }
        } else {
          // Handle error silently
        }
      } catch (error) {
        // Handle error silently
      } finally {
        this.inboxLoading = false;
      }
    },

    async loadSentMessages() {
      this.sentLoading = true;
      try {
        const response = await fetch('/api/student/sent-messages');
        if (response.ok) {
          const result = await response.json();
          this.sentMessages = result.data || result;
          
          // Extract unique teachers for filter
          if (this.sentMessages.length > 0) {
            const teachersSet = new Set(this.sentMessages.map(msg => msg.teacher_name));
            this.sentTeachers = [...teachersSet];
          }
        } else {
          // Handle error silently
        }
      } catch (error) {
        // Handle error silently
      } finally {
        this.sentLoading = false;
      }
    },
    
    async loadTeachers() {
      try {
        const response = await fetch('/api/student/available-teachers');
        if (response.ok) {
          this.teachers = await response.json();
        }
      } catch (error) {
        // Handle error silently
      }
    },
    
    async loadCourses() {
      try {
        const response = await fetch('/api/student/enrolled-courses');
        if (response.ok) {
          this.courses = await response.json();
        }
      } catch (error) {
        // Handle error silently
      }
    },
    
    async openReceivedMessage(message) {
      // Automatically mark as read when opening
      if (!message.is_read) {
        try {
          const response = await fetch(`/api/student/messages/${message.id}/mark-as-read`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
          });
          
          if (response.ok) {
            // Update the message status locally
            message.is_read = true;
            
            // Refresh stats to update unread count
            this.loadStats();
          }
        } catch (error) {
          // Handle error silently
        }
      }
      
      // Show the message in modal
      this.currentMessage = message;
      this.showMessageModal = true;
    },
    
    openSentMessage(message) {
      // Show the message in modal (sent messages don't need mark as read)
      this.currentMessage = message;
      this.showMessageModal = true;
    },
    
    replyToMessage(message) {
      // Switch to compose tab
      this.activeTab = 'compose';

      // Set reply mode
      this.isReplying = true;

      // Find and store the teacher info
      const teacherId = message.teacher_id || message.sender_id;
      this.replyingToTeacher = this.teachers.find(t => t.id === teacherId);

      // Pre-populate the compose form
      this.composeData.teacher_id = teacherId;

      // Add "Re: " prefix to subject if not already present
      const subject = message.subject || '';
      if (!subject.toLowerCase().startsWith('re:')) {
        this.composeData.subject = 'Re: ' + subject;
      } else {
        this.composeData.subject = subject;
      }

      // Set course if available
      if (message.course_id) {
        this.composeData.course_id = message.course_id;
      }

      // Clear message field for new reply
      this.composeData.message = '';

      // Scroll to top so user can see the compose form
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    

    
    async deleteMessage(message, type) {
      // Show confirmation dialog
      const confirmed = confirm(
        `Are you sure you want to delete this message?\n\nSubject: ${message.subject}\n\nThis action cannot be undone.`
      );
      
      if (!confirmed) {
        return;
      }
      
      try {
        const response = await fetch(`/api/student/messages/${message.id}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        const responseData = await response.json();
        
        if (response.ok) {
          this.showToast('Message deleted successfully', 'success');
          
          // Remove message from the appropriate array
          if (type === 'inbox') {
            this.receivedMessages = this.receivedMessages.filter(msg => msg.id !== message.id);
          } else if (type === 'sent') {
            this.sentMessages = this.sentMessages.filter(msg => msg.id !== message.id);
          }
          
          // Refresh stats
          this.loadStats();
        } else {
          let errorMessage = 'Failed to delete message';
          
          if (responseData.error) {
            errorMessage = responseData.error;
          } else if (responseData.message) {
            errorMessage = responseData.message;
          }
          
          this.showToast('Error: ' + errorMessage, 'error');
        }
      } catch (error) {
        this.showToast('Network error deleting message', 'error');
      }
    },
    
    async sendMessage() {
      if (!this.composeData.teacher_id || !this.composeData.subject || !this.composeData.message) {
        this.showToast('Please fill in all required fields', 'warning');
        return;
      }
      
      this.composing = true;
      try {
        const response = await fetch('/api/student/send-message', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(this.composeData)
        });
        
        const responseData = await response.json();
        
        if (response.ok) {
          this.showToast(responseData.message || 'Message sent successfully!', 'success');
          this.clearCompose();
          this.activeTab = 'sent';
          // Refresh sent messages
          this.loadSentMessages();
        } else {
          let errorMessage = 'Unknown error';
          
          if (responseData.error) {
            errorMessage = responseData.error;
          } else if (responseData.errors) {
            // Handle validation errors
            const firstError = Object.values(responseData.errors)[0];
            errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
          } else if (responseData.message) {
            errorMessage = responseData.message;
          }
          
          this.showToast('Error sending message: ' + errorMessage, 'error');
        }
      } catch (error) {
        this.showToast('Network error sending message', 'error');
      } finally {
        this.composing = false;
      }
    },
    
    clearCompose() {
      this.composeData = {
        teacher_id: '',
        subject: '',
        course_id: '',
        message: ''
      };
      this.isReplying = false;
      this.replyingToTeacher = null;
    },

    cancelReply() {
      // Reset reply mode but keep the compose tab open
      this.isReplying = false;
      this.replyingToTeacher = null;
      // Optionally clear the teacher selection to let user choose
      this.composeData.teacher_id = '';
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString();
    },
    
    formatTimeAgo(dateString) {
      const now = new Date();
      const date = new Date(dateString);
      const diff = now - date;
      const days = Math.floor(diff / (1000 * 60 * 60 * 24));
      
      if (days === 0) return 'Today';
      if (days === 1) return 'Yesterday';
      if (days < 7) return `${days} days ago`;
      return `${Math.floor(days / 7)} weeks ago`;
    },
    
    truncateText(text, length) {
      if (text.length <= length) return text;
      return text.substring(0, length) + '...';
    },

    showToast(message, type = 'info') {
      this.toast.message = message;
      this.toast.type = type;
      this.toast.show = true;
      setTimeout(() => {
        this.toast.show = false;
      }, 3000); // Hide after 3 seconds
    },

    hideToast() {
      this.toast.show = false;
    },

    closeMessageModal() {
      this.showMessageModal = false;
      this.currentMessage = {};
    },

    replyToCurrentMessage() {
      // Store the message before closing the modal
      const messageToReply = { ...this.currentMessage };
      
      // Close the modal first
      this.closeMessageModal();
      
      // Use the existing replyToMessage function
      this.replyToMessage(messageToReply);
    }
  }
}
</script>

<style scoped>
/* Messages Container */
.messages-container {
  width: 100%;
  padding: 0 20px;
}

/* Header Section */
.messages-page-header {
  background: linear-gradient(135deg, #0077b6, #4a90a4);
  color: white;
  padding: 40px;
  border-radius: 12px;
  margin-bottom: 32px;
  text-align: center;
  position: relative;
  z-index: 1;
  display: block;
  width: 100%;
  box-sizing: border-box;
}

.messages-page-header .header-content {
  text-align: center;
  position: relative;
  z-index: 2;
}

.messages-page-header .header-content h1 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 8px;
  color: white !important;
  text-shadow: none;
  line-height: 1.2;
  font-family: inherit;
}

.messages-page-header .header-content p {
  font-size: 1.1rem;
  opacity: 0.9;
  margin: 0;
  color: white !important;
  text-shadow: none;
  line-height: 1.4;
  font-family: inherit;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 24px;
}

.stat-card {
  display: flex;
  align-items: center;
  padding: 20px;
  border-radius: 12px;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.stat-card.bg-purple {
  background: linear-gradient(135deg, #001d3d, #0077b6);
}

.stat-card.bg-green {
  background: linear-gradient(135deg, #0077b6, #003d5c);
}

.stat-card.bg-blue {
  background: linear-gradient(135deg, #0099cc, #004d66);
}

.stat-card.bg-orange {
  background: linear-gradient(135deg, #4a90a4, #001d3d);
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  color: white !important;
  font-size: 1.75rem;
  background-color: rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  margin-right: 16px;
  flex-shrink: 0;
}

.stat-icon i {
  color: white !important;
  font-size: 1.75rem !important;
  font-weight: bold;
  line-height: 1;
  display: block;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.9rem;
  margin: 5px 0 0 0;
  opacity: 0.9;
}

/* Messages Header */
.messages-header {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  margin-bottom: 24px;
  padding: 20px;
}

.messages-header .header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.messages-header .header-content h1 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 15px;
}

.search-container {
  position: relative;
  width: 300px;
}

.search-input {
  width: 100%;
  padding: 10px 15px 10px 40px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #0077b6;
  box-shadow: 0 0 0 3px rgba(106, 90, 205, 0.1);
}

.search-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
}

/* Tab Navigation */
.tab-navigation {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 0;
  border-bottom: 1px solid #e0e0e0;
}

.tab-btn {
  display: inline-block;
  padding: 10px 20px;
  color: #666;
  text-decoration: none;
  font-weight: 500;
  border-radius: 8px 8px 0 0;
  transition: all 0.2s;
  position: relative;
  background: none;
  border: none;
  cursor: pointer;
  margin-right: 5px;
}

.tab-btn:hover {
  color: #0077b6;
  background-color: rgba(106, 90, 205, 0.05);
}

.tab-btn.active {
  color: #0077b6;
  border-bottom: 3px solid #0077b6;
  font-weight: 600;
}

.tab-btn i {
  margin-right: 5px;
}

.notification-badge {
  background-color: #ef4444;
  color: white;
  border-radius: 50%;
  padding: 2px 6px;
  font-size: 0.75rem;
  margin-left: 4px;
  min-width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: absolute;
  right: 8px;
  top: 2px;
}

/* Filters Section */
.filters-section {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.filter-controls {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.filter-select {
  min-width: 180px;
  padding: 8px 12px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.95rem;
  background: white;
  color: #333;
  cursor: pointer;
  transition: all 0.2s;
}

.filter-select:focus {
  outline: none;
  border-color: #0077b6;
  box-shadow: 0 0 0 3px rgba(106, 90, 205, 0.1);
}

/* Messages Grid */
.messages-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.message-card {
  background-color: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
  position: relative;
}

.message-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.message-header {
  position: relative;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f0f0f0;
}

.message-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0077b6, #4a90a4);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.message-badges {
  display: flex;
  gap: 8px;
}

.message-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
}

.message-badge.unread {
  background-color: #dbeafe;
  color: #1e40af;
}

.message-badge.read {
  background-color: #d1fae5;
  color: #065f46;
}

.message-badge.important {
  background-color: #fef3c7;
  color: #92400e;
}

.message-badge.sent {
  background-color: #f3e8ff;
  color: #7c3aed;
}

.message-body {
  padding: 20px;
  flex-grow: 1;
}

.message-subject {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0 0 10px 0;
  color: #333;
  line-height: 1.3;
}

.message-preview {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 15px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.4;
}

.message-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 15px;
}

.meta-item {
  font-size: 0.85rem;
  color: #666;
  display: flex;
  align-items: center;
}

.meta-item i {
  color: #0077b6;
  margin-right: 5px;
}

.message-footer {
  padding: 15px 20px;
  border-top: 1px solid #f0f0f0;
  display: flex;
  gap: 10px;
}

.btn-action {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px 12px;
  font-size: 0.85rem;
  font-weight: 500;
  min-height: 36px;
  border-radius: 6px;
}

.btn-action i {
  font-size: 0.8rem;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.95rem;
  transition: all 0.2s;
  cursor: pointer;
  text-decoration: none;
  border: none;
}

.btn-primary {
  background-color: #0077b6;
  color: white;
  box-shadow: 0 2px 5px rgba(106, 90, 205, 0.3);
}

.btn-primary:hover {
  background-color: #5a4bb5;
  box-shadow: 0 4px 8px rgba(106, 90, 205, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-outline-primary {
  background-color: transparent;
  color: #0077b6;
  border: 1px solid #0077b6;
}

.btn-outline-primary:hover {
  background-color: #0077b6;
  color: white;
}

.btn-secondary {
  background-color: #6b7280;
  color: white;
}

.btn-secondary:hover {
  background-color: #5b6470;
}

.btn-danger {
  background-color: #ef4444;
  color: white;
  box-shadow: 0 2px 5px rgba(239, 68, 68, 0.3);
}

.btn-danger:hover {
  background-color: #dc2626;
  box-shadow: 0 4px 8px rgba(239, 68, 68, 0.4);
}

/* Loading */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #6b7280;
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f3f4f6;
  border-top: 3px solid #0077b6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 12px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-container p {
  margin: 0;
  font-size: 0.9rem;
}

/* Empty State */
.empty-state {
  padding: 60px 20px;
  text-align: center;
  color: #6b7280;
}

.empty-state .empty-icon {
  font-size: 4rem;
  margin-bottom: 20px;
  color: #d1d5db;
}

.empty-state h3 {
  font-size: 1.5rem;
  margin-bottom: 10px;
  color: #374151;
}

.empty-state p {
  font-size: 1rem;
  margin-bottom: 20px;
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
}

/* Compose Tab */
.compose-container {
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.compose-container h3 {
  margin-bottom: 25px;
  color: #333;
  font-size: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #374151;
}

.form-control,
.form-select {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s;
}

.form-control:focus,
.form-select:focus {
  outline: none;
  border-color: #0077b6;
  box-shadow: 0 0 0 3px rgba(106, 90, 205, 0.1);
}

/* Reply Teacher Info */
.reply-teacher-info {
  background: #f8f9fa;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 10px;
}

.teacher-display {
  display: flex;
  align-items: center;
  font-size: 0.95rem;
  color: #333;
}

.teacher-display i {
  color: #0077b6;
  font-size: 1.2rem;
}

.teacher-display button {
  padding: 4px 12px;
  font-size: 0.85rem;
}

.form-actions {
  display: flex;
  gap: 15px;
  margin-top: 30px;
}

.form-actions .btn {
  padding: 12px 24px;
}

  /* Toast Notification */
  .toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #333;
    color: white;
    padding: 15px 25px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 1000;
    opacity: 0.95;
    transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
    transform: translateX(0);
  }

.toast-notification.info {
  background-color: #3b82f6;
}

.toast-notification.success {
  background-color: #10b981;
}

.toast-notification.error {
  background-color: #ef4444;
}

.toast-notification.warning {
  background-color: #0077b6;
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 8px;
}

.toast-content i {
  font-size: 1.1rem;
}

.toast-close {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0 5px;
  line-height: 1;
}

/* Responsive */
@media (max-width: 1200px) {
  .messages-container {
    padding: 0 16px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .messages-container {
    padding: 0 15px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .search-container {
    width: 100%;
    max-width: 300px;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .messages-grid {
    grid-template-columns: 1fr;
  }
  
  .message-footer {
    flex-direction: column;
    gap: 8px;
  }
  
  .btn-action {
    flex: none;
  }
  
  .form-actions {
    flex-direction: column;
  }
}

/* Message Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  padding: 20px 25px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #0077b6, #4a90a4);
  color: white;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-close {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 5px;
  border-radius: 50%;
  width: 35px;
  height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
}

.modal-close:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.modal-body {
  padding: 25px;
  flex: 1;
  overflow-y: auto;
}

.message-info {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 20px;
  border-left: 4px solid #0077b6;
}

.info-row {
  display: flex;
  margin-bottom: 8px;
}

.info-row:last-child {
  margin-bottom: 0;
}

.info-label {
  font-weight: 600;
  color: #555;
  min-width: 60px;
  margin-right: 10px;
}

.info-value {
  color: #333;
  flex: 1;
}

.important-badge {
  background: linear-gradient(135deg, #0077b6, #001d3d);
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.message-content {
  line-height: 1.6;
}

.message-content p {
  margin: 0;
  color: #333;
  font-size: 1rem;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.modal-footer {
  padding: 20px 25px;
  border-top: 1px solid #e0e0e0;
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  background: #f8f9fa;
}

.modal-footer .btn {
  padding: 10px 20px;
  font-size: 0.9rem;
}

/* Modal Responsive */
@media (max-width: 768px) {
  .modal-container {
    width: 95%;
    max-height: 90vh;
  }
  
  .modal-header {
    padding: 15px 20px;
  }
  
  .modal-header h3 {
    font-size: 1.1rem;
  }
  
  .modal-body {
    padding: 20px;
  }
  
  .modal-footer {
    padding: 15px 20px;
    flex-direction: column;
  }
  
  .modal-footer .btn {
    width: 100%;
    margin-bottom: 8px;
  }
  
  .modal-footer .btn:last-child {
    margin-bottom: 0;
  }
}
</style> 