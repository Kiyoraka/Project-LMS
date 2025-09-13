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
              <span class="info-label">{{ currentMessage.sender_name ? 'From:' : 'To:' }}</span>
              <span class="info-value">{{ currentMessage.sender_name || currentMessage.student_name }}</span>
            </div>
            <div class="info-row" v-if="currentMessage.course_name && currentMessage.course_name !== 'General'">
              <span class="info-label">Course:</span>
              <span class="info-value">{{ currentMessage.course_name }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Date:</span>
              <span class="info-value">{{ formatDate(currentMessage.created_at) }}</span>
            </div>
            <div class="info-row" v-if="currentMessage.is_important">
              <span class="important-badge">
                <i class="fas fa-star"></i> Important
              </span>
            </div>
          </div>
          
          <div class="message-content">
            <p>{{ currentMessage.message || currentMessage.preview }}</p>
          </div>
        </div>
        
        <div class="modal-footer" v-if="currentMessage.sender_name">
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
        <h1>Message Center</h1>
        <p>Communicate with your students and manage course discussions</p>
      </div>
    </div>

    <!-- Stats Grid -->
        <div class="stats-grid">
      <!-- Received Messages Card -->
      <div class="stat-card bg-blue">
            <div class="stat-icon">
          <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-value">{{ statsLoading ? '...' : stats.received_messages }}</h3>
              <p class="stat-label">Received</p>
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
          
      <!-- Sent Messages Card -->
      <div class="stat-card bg-orange">
            <div class="stat-icon">
              <i class="fas fa-paper-plane"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-value">{{ statsLoading ? '...' : stats.sent_messages }}</h3>
              <p class="stat-label">Sent</p>
            </div>
          </div>
          
      <!-- This Week Card -->
      <div class="stat-card bg-purple">
            <div class="stat-icon">
          <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-value">{{ statsLoading ? '...' : stats.messages_this_week }}</h3>
              <p class="stat-label">This Week</p>
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
              
            <select v-model="inboxSelectedStudent" class="filter-select">
                  <option value="">All Students</option>
                  <option v-for="student in inboxStudents" :key="student" :value="student">
                    {{ student }}
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
        <div v-else-if="filteredInboxMessages.length > 0" class="messages-grid">
          <div 
            v-for="message in filteredInboxMessages" 
            :key="message.id"
            class="message-card"
            @click="openReceivedMessage(message)"
          >
            <!-- Message Header -->
            <div class="message-header">
              <div class="message-avatar">
                {{ (message.sender_name || 'U').charAt(0) }}
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
              <p class="message-preview">{{ truncateText(message.message || message.preview || '', 80) }}</p>
              
              <div class="message-meta">
                <div class="meta-item">
                  <i class="fas fa-user me-1"></i>
                  <span>{{ message.sender_name || 'Unknown User' }}</span>
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
        <div v-else-if="!inboxLoading" class="empty-state">
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
            <select v-model="sentSelectedStudent" class="filter-select">
              <option value="">All Students</option>
              <option v-for="student in sentStudents" :key="student" :value="student">
                {{ student }}
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
        <div v-else-if="filteredSentMessages.length > 0" class="messages-grid">
          <div 
            v-for="message in filteredSentMessages" 
            :key="message.id"
            class="message-card"
            @click="openSentMessage(message)"
          >
            <!-- Message Header -->
            <div class="message-header">
              <div class="message-avatar">
                {{ (message.student_name || message.recipient_name || 'U').charAt(0) }}
        </div>
              <div class="message-badges">
                <span class="message-badge sent">Sent</span>
      </div>
    </div>

            <!-- Message Body -->
            <div class="message-body">
              <h5 class="message-subject">{{ message.subject }}</h5>
              <p class="message-preview">{{ truncateText(message.message || message.preview || '', 80) }}</p>
              
              <div class="message-meta">
                <div class="meta-item">
                  <i class="fas fa-user me-1"></i>
                  <span>{{ message.student_name || message.recipient_name || 'Unknown User' }}</span>
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
        <div v-else-if="!sentLoading" class="empty-state">
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
              <label for="recipient">Recipient</label>
              <select v-model="composeData.recipient_id" id="recipient" class="form-select" required>
                <option value="">Select a student</option>
                <option v-if="composeData.course_id" value="all_course_students">
                  All students in selected course
                </option>
                <option v-for="student in availableStudents" :key="student.id" :value="student.id">
                  {{ student.name }} ({{ student.email }})
                </option>
              </select>
              <div v-if="composeData.course_id && composeData.recipient_id === 'all_course_students'" class="bulk-message-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Bulk Message:</strong> This message will be sent to <strong>{{ courseStudentsCount }} student(s)</strong> in the selected course.
              </div>
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
  name: 'TeacherMessages',
  data() {
    return {
      activeTab: 'inbox',
      searchQuery: '',
      stats: {
        received_messages: 0,
        unread_messages: 0,
        sent_messages: 0,
        messages_this_week: 0
      },
      statsLoading: false,

      // Inbox data
      receivedMessages: [],
      inboxLoading: false,
      inboxSelectedStatus: '',
      inboxSelectedStudent: '',
      inboxSortBy: 'newest',
      inboxStudents: [],
      
      // Sent data
      sentMessages: [],
      sentLoading: false,
      sentSelectedStudent: '',
      sentSortBy: 'newest',
      sentStudents: [],
      
      // Compose data
      composeData: {
        recipient_id: '',
        subject: '',
        course_id: '',
        message: ''
      },
      composing: false,
      students: [],
      courses: [],
      courseStudents: {}, // Object to store students by course ID

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
      return this.searchQuery || this.inboxSelectedStatus || this.inboxSelectedStudent;
    },
    
    hasSentFilters() {
      return this.searchQuery || this.sentSelectedStudent;
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
    },
    
    filteredInboxMessages() {
      let filtered = [...this.receivedMessages];
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(message => 
          (message.subject || '').toLowerCase().includes(query) ||
          (message.message || message.preview || '').toLowerCase().includes(query) ||
          (message.sender_name || '').toLowerCase().includes(query)
        );
      }
      
      // Apply status filter
      if (this.inboxSelectedStatus) {
        filtered = filtered.filter(message => {
          if (this.inboxSelectedStatus === 'unread') return !message.is_read;
          if (this.inboxSelectedStatus === 'read') return message.is_read;
          if (this.inboxSelectedStatus === 'important') return message.is_important;
          return true;
        });
      }
      
      // Apply student filter
      if (this.inboxSelectedStudent) {
        filtered = filtered.filter(message => 
          (message.sender_name || '') === this.inboxSelectedStudent
        );
      }
      
      // Apply sorting
      filtered.sort((a, b) => {
        if (this.inboxSortBy === 'newest') {
          return new Date(b.created_at) - new Date(a.created_at);
        } else if (this.inboxSortBy === 'oldest') {
          return new Date(a.created_at) - new Date(b.created_at);
        } else if (this.inboxSortBy === 'priority') {
          if (a.is_important && !b.is_important) return -1;
          if (!a.is_important && b.is_important) return 1;
          return new Date(b.created_at) - new Date(a.created_at);
        }
        return 0;
      });
      
      return filtered;
    },
    
    filteredSentMessages() {
      let filtered = [...this.sentMessages];
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(message => 
          message.subject.toLowerCase().includes(query) ||
          (message.message || message.preview || '').toLowerCase().includes(query) ||
          (message.student_name || message.recipient_name || '').toLowerCase().includes(query)
        );
      }
      
      // Apply student filter
      if (this.sentSelectedStudent) {
        filtered = filtered.filter(message => 
          (message.student_name || message.recipient_name) === this.sentSelectedStudent
        );
      }
      
      // Apply sorting
      filtered.sort((a, b) => {
        if (this.sentSortBy === 'newest') {
          return new Date(b.created_at) - new Date(a.created_at);
        } else if (this.sentSortBy === 'oldest') {
          return new Date(a.created_at) - new Date(b.created_at);
        }
        return 0;
      });
      
      return filtered;
    },

    availableStudents() {
      // If a course is selected, show only students enrolled in that course
      if (this.composeData.course_id && this.courseStudents[this.composeData.course_id]) {
        return this.courseStudents[this.composeData.course_id];
      }
      // Otherwise show all students
      return this.students;
    },

    courseStudentsCount() {
      if (this.composeData.course_id && this.courseStudents[this.composeData.course_id]) {
        return this.courseStudents[this.composeData.course_id].length;
      }
      return 0;
    }
  },
  
  mounted() {
    this.loadStats();
    this.loadReceivedMessages();
    this.loadStudents();
    this.loadCourses();
  },

  watch: {
    activeTab(newTab) {
      if (newTab === 'sent' && this.sentMessages.length === 0) {
        this.loadSentMessages();
      }
    },

    'composeData.course_id'(newCourseId, oldCourseId) {
      // Reset recipient when course changes
      if (newCourseId !== oldCourseId) {
        this.composeData.recipient_id = '';
        
        // Load students for the selected course
        if (newCourseId && !this.courseStudents[newCourseId]) {
          this.loadCourseStudents(newCourseId);
        }
      }
    }
  },
  
  methods: {
    async loadStats() {
        this.statsLoading = true;
      try {
        const response = await fetch('/api/teacher/message-stats');
        if (response.ok) {
          this.stats = await response.json();
        }
      } catch (error) {
        // Handle error silently
      } finally {
        this.statsLoading = false;
      }
    },

    async loadReceivedMessages() {
        this.inboxLoading = true;
      try {
        const response = await fetch('/api/teacher/received-messages');
        if (response.ok) {
          const result = await response.json();
          this.receivedMessages = result.data || result;
          
          // Extract unique students for filter
          if (this.receivedMessages.length > 0) {
            const studentsSet = new Set(this.receivedMessages.map(msg => msg.sender_name).filter(name => name));
            this.inboxStudents = [...studentsSet];
          }
        }
      } catch (error) {
        // Handle error silently
      } finally {
        this.inboxLoading = false;
      }
    },
    
    async loadStudents() {
      try {
        const response = await fetch('/api/teacher/message-students');
        if (response.ok) {
          this.students = await response.json();
        }
      } catch (error) {
        // Handle error silently
      }
    },
    
    async loadCourses() {
      try {
        const response = await fetch('/api/teacher/message-courses');
        if (response.ok) {
          this.courses = await response.json();
        }
      } catch (error) {
        // Handle error silently
      }
    },

    async loadCourseStudents(courseId) {
      try {
        const response = await fetch(`/api/teacher/courses/${courseId}/students`);
        if (response.ok) {
          const data = await response.json();
          // Store students for this course (API returns {course, students})
          this.courseStudents[courseId] = data.students || data;
        }
      } catch (error) {
        console.error('Error loading course students:', error);
        this.showToast('Failed to load course students', 'error');
      }
    },

    async loadSentMessages() {
      this.sentLoading = true;
      try {
        const response = await fetch('/api/teacher/sent-messages');
        if (response.ok) {
          const result = await response.json();
          this.sentMessages = result.data || result;
          
          // Extract unique students for filter
          if (this.sentMessages.length > 0) {
            const studentsSet = new Set(this.sentMessages.map(msg => msg.student_name || msg.recipient_name).filter(name => name));
            this.sentStudents = [...studentsSet];
          }
        }
      } catch (error) {
        // Handle error silently
      } finally {
        this.sentLoading = false;
      }
    },
    
    async openReceivedMessage(message) {
      // Auto-mark as read when opening
      if (!message.is_read) {
        try {
          const response = await fetch(`/api/teacher/received-messages/${message.id}/mark-as-read`, {
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
      
      // Pre-populate the compose form - reply to the student who sent the message
      this.composeData.recipient_id = message.student_id;
      
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
        const response = await fetch(`/api/teacher/messages/${message.id}`, {
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
      if (!this.composeData.recipient_id || !this.composeData.subject || !this.composeData.message) {
        this.showToast('Please fill in all required fields', 'warning');
        return;
      }
      
      // Handle bulk messaging to all course students
      if (this.composeData.recipient_id === 'all_course_students') {
        if (!this.composeData.course_id) {
          this.showToast('Please select a course to send to all students', 'warning');
          return;
        }
        
        if (!this.courseStudents[this.composeData.course_id] || this.courseStudents[this.composeData.course_id].length === 0) {
          this.showToast('No students found in the selected course', 'warning');
          return;
        }
      }
      
      this.composing = true;
      try {
        // Determine recipients
        let recipients = [];
        let isBroadcast = false;
        
        if (this.composeData.recipient_id === 'all_course_students') {
          // Get all student IDs from the selected course
          recipients = this.courseStudents[this.composeData.course_id].map(student => student.id);
          isBroadcast = true;
        } else {
          // Single recipient
          recipients = [this.composeData.recipient_id];
        }

        // Transform data to match API format
        const messageData = {
          recipients: recipients,
          subject: this.composeData.subject,
          message: this.composeData.message,
          course_id: this.composeData.course_id || null,
          is_important: false,
          is_broadcast: isBroadcast
        };

        const response = await fetch('/api/teacher/send-message', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(messageData)
        });
        
        const responseData = await response.json();
        
        if (response.ok) {
          let successMessage = responseData.message || 'Message sent successfully!';
          
          // Show recipient count for bulk messages
          if (isBroadcast) {
            successMessage = `Message sent to ${recipients.length} student(s) successfully!`;
          }
          
          this.showToast(successMessage, 'success');
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
        recipient_id: '',
        subject: '',
        course_id: '',
        message: ''
      };
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
  background: linear-gradient(135deg, #3f66ff, #4d85f5);
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

/* Stats Grid - Exact same as courses */
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

.stat-card.bg-blue {
  background: linear-gradient(135deg, #3f66ff, #4d85f5);
}

.stat-card.bg-green {
  background: linear-gradient(135deg, #2cc069, #40d787);
}

.stat-card.bg-orange {
  background: linear-gradient(135deg, #f59f0b, #ffbc3e);
}

.stat-card.bg-purple {
  background: linear-gradient(135deg, #8a70d6, #9e86e8);
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  color: white !important;
  font-size: 1.8rem;
  background-color: rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  margin-right: 16px;
  flex-shrink: 0;
}

.stat-icon i {
  color: white !important;
  font-size: 1.8rem !important;
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

/* Messages Header - Exact same as courses */
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
  border-color: #4d85f5;
  box-shadow: 0 0 0 3px rgba(77, 133, 245, 0.1);
}

.search-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
}

/* Tab Navigation - Exact same as courses */
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
  color: #4d85f5;
  background-color: rgba(77, 133, 245, 0.05);
}

.tab-btn.active {
  color: #4d85f5;
  border-bottom: 3px solid #4d85f5;
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
  border-color: #4d85f5;
  box-shadow: 0 0 0 3px rgba(77, 133, 245, 0.1);
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
  background: linear-gradient(135deg, #3f66ff, #4d85f5);
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
  color: #4d85f5;
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

/* Buttons - Exact same as courses */
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
  background-color: #4d85f5;
  color: white;
  box-shadow: 0 2px 5px rgba(77, 133, 245, 0.3);
}

.btn-primary:hover {
  background-color: #3b66cc;
  box-shadow: 0 4px 8px rgba(77, 133, 245, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background-color: #f9fafb;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background-color: #f3f4f6;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 0.85rem;
}

/* Compose Container */
.compose-container {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.compose-container h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
  margin: 0 0 20px 0;
}

.form-row {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
}

.form-row .form-group {
  flex: 1;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
  color: #374151;
  font-size: 0.9rem;
}

.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #4d85f5;
  box-shadow: 0 0 0 3px rgba(77, 133, 245, 0.1);
}

.bulk-message-info {
  margin-top: 8px;
  padding: 12px 16px;
  background: linear-gradient(135deg, #e8f4fd, #f0f8ff);
  border: 1px solid #b3d9f2;
  border-radius: 8px;
  color: #1565c0;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 8px;
}

.bulk-message-info i {
  color: #1976d2;
  font-size: 1rem;
}

.form-select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.95rem;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
}

.form-select:focus {
  outline: none;
  border-color: #4d85f5;
  box-shadow: 0 0 0 3px rgba(77, 133, 245, 0.1);
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 20px;
}

/* Loading State */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.loading-spinner {
    width: 40px;
    height: 40px;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #4d85f5;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-container p {
  color: #666;
  font-size: 0.95rem;
  margin: 0;
}

/* Empty State */
.empty-state {
  background-color: white;
  border-radius: 12px;
  padding: 60px 20px;
    text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.empty-icon {
  font-size: 3rem;
  color: #ccc;
  margin-bottom: 20px;
}

.empty-state h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #333;
  margin: 0 0 10px 0;
}

.empty-state p {
  color: #666;
  margin: 0;
  max-width: 400px;
  line-height: 1.6;
  font-size: 0.95rem;
}

/* Responsive Design - Exact same as courses */
@media (max-width: 1200px) {
  .messages-container {
    max-width: 100%;
    padding: 0 16px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  
  .messages-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
  }
}

@media (max-width: 768px) {
  .messages-container {
    padding: 0 15px;
  }
  
  .messages-page-header {
    padding: 24px;
  }
  
  .messages-page-header .header-content h1 {
    font-size: 2rem;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }

  .stat-card {
    padding: 16px;
  }

  .stat-icon {
    width: 48px;
    height: 48px;
    font-size: 1.5rem;
    margin-right: 12px;
  }
  
  .stat-icon i {
    font-size: 1.5rem !important;
  }
  
  .stat-value {
    font-size: 1.5rem;
  }
  
  .messages-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .messages-header {
    padding: 16px;
  }
  
  .tab-navigation {
    flex-wrap: wrap;
  }
  
  .tab-btn {
    padding: 8px 16px;
    font-size: 0.9rem;
  }
  
  .message-card {
    padding: 12px;
  }
  
  .message-header {
    padding: 12px;
  }
  
  .message-body {
    padding: 12px;
  }
  
  .message-footer {
    padding: 12px;
    flex-direction: column;
    gap: 8px;
  }
  
  .message-avatar {
    width: 32px;
    height: 32px;
    font-size: 0.8rem;
  }
  
  .btn-action {
    width: 100%;
    justify-content: center;
  }
  
  .header-actions {
    flex-direction: column;
    gap: 12px;
    width: 100%;
  }
  
  .search-container {
    width: 100%;
  }
  
  .filter-controls {
    flex-direction: column;
    gap: 12px;
  }
  
  .filter-select {
    width: 100%;
  }
  
  .form-row {
    flex-direction: column;
    gap: 12px;
  }
  
  .btn {
    padding: 8px 16px;
    font-size: 0.9rem;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .stat-card {
    padding: 12px;
  }
  
  .stat-icon {
    width: 40px;
    height: 40px;
    font-size: 1.2rem;
    margin-right: 8px;
  }
  
  .stat-icon i {
    font-size: 1.2rem !important;
  }
  
  .stat-value {
    font-size: 1.2rem;
  }
  
  .stat-label {
    font-size: 0.8rem;
  }
  
  .messages-header {
    padding: 12px;
  }
  
  .header-content h1 {
    font-size: 1.25rem;
  }
  
  .tab-btn {
    padding: 6px 12px;
    font-size: 0.8rem;
  }
  
  .message-card {
    padding: 12px;
  }
  
  .message-avatar {
    width: 32px;
    height: 32px;
    font-size: 0.8rem;
  }
  
  .btn-action {
    width: 100%;
    justify-content: center;
  }
}

/* Utility classes */
.d-flex {
  display: flex;
}

.justify-content-between {
  justify-content: space-between;
}

.align-items-center {
  align-items: center;
}

.mb-1 {
  margin-bottom: 0.25rem;
}

.me-1 {
  margin-right: 0.25rem;
}

.me-2 {
  margin-right: 0.5rem;
}

.ms-3 {
  margin-left: 1rem;
}

.mt-3 {
  margin-top: 1rem;
}

.py-5 {
  padding-top: 3rem;
  padding-bottom: 3rem;
}

.text-center {
  text-align: center;
}

.text-muted {
  color: #6c757d;
}

.text-primary {
  color: #4d85f5;
}

/* Animation for spinner */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
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
  background-color: #0077b6;
}

.toast-notification.success {
  background-color: #10b981;
}

.toast-notification.error {
  background-color: #ef4444;
}

.toast-notification.warning {
  background-color: #f59e0b;
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
  background: linear-gradient(135deg, #3f66ff, #4d85f5);
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
  border-left: 4px solid #4d85f5;
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
  background: linear-gradient(135deg, #f59e0b, #d97706);
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

.btn-outline-primary {
  background-color: transparent;
  color: #4d85f5;
  border: 1px solid #4d85f5;
}

.btn-outline-primary:hover {
  background-color: #4d85f5;
  color: white;
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