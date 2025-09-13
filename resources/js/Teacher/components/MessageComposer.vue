<template>
  <div class="composer-card">
    <div class="composer-header">
      <h2 class="composer-title">
        <i class="fas fa-envelope"></i>
        Send Message to Students
      </h2>
      <button 
        @click="showSentMessages = !showSentMessages"
        class="toggle-btn"
      >
        <i class="fas fa-paper-plane"></i>
        {{ showSentMessages ? 'Compose Message' : 'View Sent Messages' }}
      </button>
    </div>

    <!-- Compose Message Form -->
    <div v-if="!showSentMessages" class="compose-form">
      <!-- Course Selection -->
      <div class="form-group">
        <label class="form-label">
          Course (Optional)
        </label>
        <select 
          v-model="selectedCourse" 
          @change="onCourseChange"
          class="form-select"
        >
          <option value="">Select a course (or leave blank for general message)</option>
          <option v-for="course in courses" :key="course.id" :value="course.id">
            {{ course.title }}
          </option>
        </select>
      </div>

      <!-- Recipients Selection -->
      <div class="form-group">
        <label class="form-label">
          Recipients
        </label>
        <div class="recipients-options">
          <!-- Broadcast Option -->
          <div v-if="selectedCourse" class="radio-option">
            <input 
              type="radio" 
              id="broadcast" 
              v-model="messageType" 
              value="broadcast"
              class="radio-input"
            >
            <label for="broadcast" class="radio-label">
              Send to all students in this course ({{ courseStudents.length }} students)
            </label>
          </div>
          
          <!-- Individual Selection -->
          <div class="radio-option">
            <input 
              type="radio" 
              id="individual" 
              v-model="messageType" 
              value="individual"
              class="radio-input"
            >
            <label for="individual" class="radio-label">
              Select specific students
            </label>
          </div>
        </div>

        <!-- Student Selection (Individual Mode) -->
        <div v-if="messageType === 'individual'" class="student-selection">
          <div class="students-list">
            <div v-for="student in availableStudents" :key="student.id" class="student-item">
              <label class="student-label">
                <input 
                  type="checkbox" 
                  :value="student.id" 
                  v-model="selectedStudents"
                  class="student-checkbox"
                >
                <div class="student-info">
                  <div class="student-name">{{ student.name }}</div>
                  <div class="student-email">{{ student.email }}</div>
                </div>
              </label>
            </div>
          </div>
          <p v-if="selectedStudents.length > 0" class="selection-count">
            {{ selectedStudents.length }} student(s) selected
          </p>
        </div>
      </div>

      <!-- Subject -->
      <div class="form-group">
        <label class="form-label">
          Subject *
        </label>
        <input 
          type="text" 
          v-model="subject" 
          placeholder="Enter message subject"
          class="form-input"
          required
        >
      </div>

      <!-- Message Content -->
      <div class="form-group">
        <label class="form-label">
          Message *
        </label>
        <textarea 
          v-model="messageContent" 
          rows="6"
          placeholder="Type your message here..."
          class="form-textarea"
          required
        ></textarea>
      </div>

      <!-- Message Options -->
      <div class="form-options">
        <label class="checkbox-label">
          <input 
            type="checkbox" 
            v-model="isImportant"
            class="checkbox-input"
          >
          <span class="checkbox-text">Mark as important</span>
        </label>
      </div>

      <!-- Action Buttons -->
      <div class="form-actions">
        <button 
          @click="clearForm"
          type="button"
          class="btn btn-secondary"
        >
          Clear
        </button>
        <button 
          @click="sendMessage"
          :disabled="!canSend || sending"
          class="btn btn-primary"
        >
          <i v-if="sending" class="fas fa-spinner fa-spin"></i>
          <i v-else class="fas fa-paper-plane"></i>
          {{ sending ? 'Sending...' : 'Send Message' }}
        </button>
      </div>
    </div>

    <!-- Sent Messages List -->
    <div v-else class="sent-messages">
      <div class="messages-container">
        <div v-if="sentLoading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Loading sent messages...</p>
        </div>

        <div v-else-if="sentMessages.length === 0" class="empty-state">
          <i class="fas fa-inbox"></i>
          <p>No messages sent yet</p>
        </div>

        <div v-else class="sent-messages-list">
          <div 
            v-for="message in sentMessages" 
            :key="message.id"
            class="sent-message-card"
          >
            <div class="message-header-sent">
              <div class="message-content-sent">
                <div class="message-title-sent">
                  <span class="subject">{{ message.subject }}</span>
                  <span v-if="message.is_important" class="important-badge">
                    <i class="fas fa-exclamation-circle"></i>
                  </span>
                  <span v-if="message.is_broadcast" class="broadcast-badge">
                    Broadcast
                  </span>
                </div>
                <div class="message-recipient">
                  To: {{ message.student_name }} 
                  <span v-if="message.course_name !== 'General'">
                    ({{ message.course_name }})
                  </span>
                </div>
                <div class="message-preview-sent">{{ message.preview }}</div>
              </div>
              <div class="message-date-sent">
                {{ message.formatted_date }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="successMessage" class="alert alert-success">
      <i class="fas fa-check-circle"></i>
      {{ successMessage }}
    </div>

    <div v-if="errorMessage" class="alert alert-error">
      <i class="fas fa-exclamation-triangle"></i>
      {{ errorMessage }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'MessageComposer',
  data() {
    return {
      // UI State
      showSentMessages: false,
      sending: false,
      sentLoading: false,
      
      // Form Data
      selectedCourse: '',
      messageType: 'individual',
      selectedStudents: [],
      subject: '',
      messageContent: '',
      isImportant: false,
      
      // Data
      courses: [],
      allStudents: [],
      courseStudents: [],
      sentMessages: [],
      
      // Messages
      successMessage: '',
      errorMessage: ''
    };
  },
  
  computed: {
    availableStudents() {
      return this.selectedCourse ? this.courseStudents : this.allStudents;
    },
    
    canSend() {
      if (!this.subject.trim() || !this.messageContent.trim()) {
        return false;
      }
      
      if (this.messageType === 'broadcast') {
        return this.selectedCourse && this.courseStudents.length > 0;
      } else {
        return this.selectedStudents.length > 0;
      }
    }
  },
  
  async mounted() {
    await this.loadInitialData();
  },
  
  methods: {
    async loadInitialData() {
      try {
        await Promise.all([
          this.loadCourses(),
          this.loadStudents()
        ]);
      } catch (error) {
        console.error('Error loading initial data:', error);
      }
    },
    
    async loadCourses() {
      try {
        const response = await fetch('/api/teacher/message-courses');
        if (response.ok) {
          this.courses = await response.json();
        }
      } catch (error) {
        console.error('Error loading courses:', error);
      }
    },
    
    async loadStudents() {
      try {
        const response = await fetch('/api/teacher/message-students');
        if (response.ok) {
          this.allStudents = await response.json();
        }
      } catch (error) {
        console.error('Error loading students:', error);
      }
    },
    
    async onCourseChange() {
      if (this.selectedCourse) {
        await this.loadCourseStudents();
        this.messageType = 'broadcast'; // Default to broadcast for course messages
      } else {
        this.courseStudents = [];
        this.messageType = 'individual';
      }
      this.selectedStudents = [];
    },
    
    async loadCourseStudents() {
      try {
        const response = await fetch(`/api/teacher/courses/${this.selectedCourse}/students`);
        if (response.ok) {
          const data = await response.json();
          this.courseStudents = data.students;
        }
      } catch (error) {
        console.error('Error loading course students:', error);
      }
    },
    
    async sendMessage() {
      try {
        this.sending = true;
        this.clearMessages();
        
        const endpoint = this.messageType === 'broadcast' 
          ? '/api/teacher/send-broadcast'
          : '/api/teacher/send-message';
        
        const payload = {
          subject: this.subject,
          message: this.messageContent,
          is_important: this.isImportant
        };
        
        if (this.messageType === 'broadcast') {
          payload.course_id = this.selectedCourse;
        } else {
          payload.recipients = this.selectedStudents;
          payload.course_id = this.selectedCourse || null;
          payload.is_broadcast = false;
        }
        
        const response = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
          },
          body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.successMessage = data.message;
          this.clearForm();
          setTimeout(() => this.clearMessages(), 5000);
        } else {
          this.errorMessage = data.error || 'Failed to send message';
        }
        
      } catch (error) {
        console.error('Error sending message:', error);
        this.errorMessage = 'Failed to send message. Please try again.';
      } finally {
        this.sending = false;
      }
    },
    
    async loadSentMessages() {
      try {
        this.sentLoading = true;
        const response = await fetch('/api/teacher/sent-messages');
        if (response.ok) {
          const data = await response.json();
          this.sentMessages = data.data;
        }
      } catch (error) {
        console.error('Error loading sent messages:', error);
      } finally {
        this.sentLoading = false;
      }
    },
    
    clearForm() {
      this.selectedCourse = '';
      this.messageType = 'individual';
      this.selectedStudents = [];
      this.subject = '';
      this.messageContent = '';
      this.isImportant = false;
      this.courseStudents = [];
    },
    
    clearMessages() {
      this.successMessage = '';
      this.errorMessage = '';
    }
  },
  
  watch: {
    showSentMessages(newVal) {
      if (newVal) {
        this.loadSentMessages();
      }
    }
  }
};
</script>

<style scoped>
/* Message Composer Styles */
.composer-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.composer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  background-color: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.composer-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #374151;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.composer-title i {
  color: #0077b6;
}

.toggle-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: linear-gradient(135deg, #0077b6, #001d3d);
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

.toggle-btn:hover {
  background: linear-gradient(135deg, #2563eb, #1e40af);
}

/* Form Styles */
.compose-form {
  padding: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
  color: #374151;
  font-size: 0.9rem;
}

.form-select,
.form-input,
.form-textarea {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  background: white;
  color: #374151;
}

.form-select:focus,
.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #0077b6;
  box-shadow: 0 0 0 1px #0077b6;
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
}

/* Recipients Options */
.recipients-options {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.radio-option {
  display: flex;
  align-items: center;
  gap: 8px;
}

.radio-input {
  margin: 0;
}

.radio-label {
  color: #374151;
  font-size: 0.9rem;
  cursor: pointer;
}

/* Student Selection */
.student-selection {
  margin-top: 16px;
}

.students-list {
  max-height: 180px;
  overflow-y: auto;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: white;
}

.student-item {
  padding: 10px 12px;
  border-bottom: 1px solid #f3f4f6;
}

.student-item:last-child {
  border-bottom: none;
}

.student-label {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.student-checkbox {
  margin: 0;
}

.student-info {
  flex: 1;
}

.student-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 0.9rem;
}

.student-email {
  font-size: 0.8rem;
  color: #6b7280;
}

.selection-count {
  margin-top: 8px;
  font-size: 0.85rem;
  color: #6b7280;
}

/* Form Options */
.form-options {
  display: flex;
  align-items: center;
  gap: 16px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.checkbox-input {
  margin: 0;
}

.checkbox-text {
  color: #374151;
  font-size: 0.9rem;
}

/* Form Actions */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

.btn-primary {
  background: linear-gradient(135deg, #0077b6, #001d3d);
  color: white;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #2563eb, #1e40af);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: white;
  color: #6b7280;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background: #f9fafb;
  color: #374151;
  border-color: #9ca3af;
}

/* Sent Messages */
.sent-messages {
  padding: 20px;
}

.messages-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  color: #6b7280;
}

.loading-state i,
.empty-state i {
  font-size: 2rem;
  margin-bottom: 12px;
}

.sent-messages-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.sent-message-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 12px;
  transition: all 0.3s ease;
}

.sent-message-card:hover {
  border-color: #0077b6;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.message-header-sent {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.message-content-sent {
  flex: 1;
}

.message-title-sent {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.subject {
  font-weight: 600;
  color: #1f2937;
  font-size: 1rem;
}

.important-badge {
  color: #dc2626;
  font-size: 0.9rem;
}

.broadcast-badge {
  background: #dbeafe;
  color: #0077b6;
  padding: 2px 6px;
  border-radius: 10px;
  font-size: 0.75rem;
  font-weight: 500;
}

.message-recipient {
  font-size: 0.85rem;
  color: #6b7280;
  margin-bottom: 8px;
}

.message-preview-sent {
  font-size: 0.85rem;
  color: #9ca3af;
}

.message-date-sent {
  font-size: 0.8rem;
  color: #9ca3af;
  margin-left: 16px;
  white-space: nowrap;
}

/* Alerts */
.alert {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  border-radius: 6px;
  margin-top: 12px;
  font-size: 0.9rem;
}

.alert-success {
  background: #d1fae5;
  color: #059669;
  border: 1px solid #a7f3d0;
}

.alert-error {
  background: #fee2e2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

/* Responsive Design */
@media (max-width: 768px) {
  .composer-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
  
  .toggle-btn {
    justify-content: center;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .message-header-sent {
    flex-direction: column;
    gap: 8px;
  }
  
  .message-date-sent {
    margin-left: 0;
  }
}
</style> 