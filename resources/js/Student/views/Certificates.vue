<template>
  <div class="certificates-container">
    <!-- Header Section -->
    <div class="certificates-header">
      <div class="header-content">
        <h1>My Certificates</h1>
        <p>View and download your course completion certificates</p>
      </div>
    </div>

    <!-- Stats Section -->
    <div class="stats-grid">
      <div class="stat-card bg-primary">
        <div class="stat-icon">
          <i class="bi bi-award"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ statsLoading ? '...' : stats.total_certificates }}</h3>
          <p class="stat-label">Total Certificates</p>
        </div>
      </div>
      
      <div class="stat-card bg-success">
        <div class="stat-icon">
          <i class="bi bi-calendar-check"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ statsLoading ? '...' : stats.certificates_this_month }}</h3>
          <p class="stat-label">Earned This Month</p>
        </div>
      </div>
      
      <div class="stat-card bg-warning">
        <div class="stat-icon">
          <i class="bi bi-star-fill"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ statsLoading ? '...' : stats.average_score + '%' }}</h3>
          <p class="stat-label">Average Score</p>
        </div>
      </div>
      
      <div class="stat-card bg-info">
        <div class="stat-icon">
          <i class="bi bi-clock"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ statsLoading ? '...' : stats.total_hours }}</h3>
          <p class="stat-label">Hours Completed</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
      <div class="search-box">
        <input 
          type="text" 
          class="search-input" 
          placeholder="Search certificates..." 
          v-model="searchQuery"
        >
        <i class="bi bi-search"></i>
      </div>
      
      <div class="filter-controls">
        <select v-model="selectedCategory" class="filter-select">
          <option value="">All Categories</option>
          <option v-for="category in categories" :key="category" :value="category">
            {{ category }}
          </option>
        </select>
        
        <select v-model="sortBy" class="filter-select">
          <option value="newest">Newest First</option>
          <option value="oldest">Oldest First</option>
          <option value="course">Course Name</option>
          <option value="score">Highest Score</option>
        </select>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="alert alert-danger" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i>
      {{ error }}
      <button @click="fetchCertificates" class="btn btn-sm btn-outline-danger ms-3">
        Try Again
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !error" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">Loading certificates...</p>
    </div>

    <!-- Certificates Grid -->
    <div v-if="!loading && !error && certificates.length > 0">
      <div class="certificates-grid">
        <div 
          v-for="certificate in paginatedCertificates" 
          :key="certificate.id"
          class="certificate-card"
        >
          <!-- Card Header with Achievement Badge -->
          <div class="card-header">
            <div class="achievement-section">
              <div class="achievement-badge">
                <i class="bi bi-trophy-fill"></i>
              </div>
              <div class="achievement-info">
                <h3 class="course-title">{{ certificate.course.title }}</h3>
                <p class="completion-date">
                  <i class="bi bi-calendar-check"></i>
                  Completed {{ formatDate(certificate.completed_at) }}
                </p>
              </div>
            </div>
            <div class="score-badge">
              <span class="score-value">{{ certificate.final_score }}%</span>
              <span class="score-label">Score</span>
            </div>
          </div>

          <!-- Card Body -->
          <div class="card-body">
            <!-- Course Meta Information -->
            <div class="course-meta">
              <div class="meta-tags">
                <span class="category-tag">
                  <i class="bi bi-tag"></i>
                  {{ certificate.course.category }}
                </span>
                <span class="level-badge" :class="getLevelClass(certificate.course.level)">
                  <i class="bi bi-signal"></i>
                  {{ certificate.course.level }}
                </span>
              </div>
            </div>

            <!-- Course Details -->
            <div class="course-details">
              <div class="detail-row">
                <div class="detail-item">
                  <i class="bi bi-person-fill"></i>
                  <span class="detail-label">Instructor</span>
                  <span class="detail-value">{{ certificate.course.instructor }}</span>
                </div>
              </div>
              <div class="detail-row">
                <div class="detail-item">
                  <i class="bi bi-clock-fill"></i>
                  <span class="detail-label">Duration</span>
                  <span class="detail-value">{{ certificate.course.duration_hours }} hours</span>
                </div>
              </div>
            </div>

            <!-- Certificate Preview -->
            <div class="certificate-preview">
              <div class="preview-container">
                <div class="preview-icon">
                  <i class="bi bi-file-earmark-pdf-fill"></i>
                </div>
                <div class="preview-text">
                  <span class="preview-title">Certificate of Completion</span>
                  <span class="preview-subtitle">Ready for download</span>
                </div>
                <div class="preview-verified">
                  <i class="bi bi-patch-check-fill"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Actions -->
          <div class="card-actions">
            <button 
              @click="viewCertificate(certificate)" 
              class="action-btn btn-view"
              title="View Certificate"
            >
              <i class="bi bi-eye-fill"></i>
              <span>View</span>
            </button>
            <button 
              @click="downloadCertificate(certificate)" 
              class="action-btn btn-download"
              :disabled="downloading === certificate.id"
              :title="downloading === certificate.id ? 'Downloading...' : 'Download Certificate'"
            >
              <i v-if="downloading === certificate.id" class="bi bi-arrow-clockwise spin"></i>
              <i v-else class="bi bi-download"></i>
              <span>{{ downloading === certificate.id ? 'Downloading...' : 'Download' }}</span>
            </button>
            <button 
              @click="shareCertificate(certificate)" 
              class="action-btn btn-share"
              title="Share Certificate"
            >
              <i class="bi bi-share-fill"></i>
              <span>Share</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="pagination" v-if="totalPages > 1">
        <button 
          class="pagination-btn" 
          :disabled="currentPage === 1" 
          @click="goToPage(currentPage - 1)"
        >
          <i class="bi bi-chevron-left"></i>
          Previous
        </button>
        
        <div class="page-numbers">
          <button 
            v-for="page in visiblePages" 
            :key="page"
            class="page-btn" 
            :class="{ active: page === currentPage }"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>
        </div>
        
        <button 
          class="pagination-btn" 
          :disabled="currentPage === totalPages" 
          @click="goToPage(currentPage + 1)"
        >
          Next
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && !error && certificates.length === 0" class="empty-state">
      <div class="empty-icon">
        <i class="bi bi-award"></i>
      </div>
      <h3 v-if="searchQuery || selectedCategory">No certificates found</h3>
      <h3 v-else>No certificates yet</h3>
      <p v-if="searchQuery || selectedCategory">
        Try adjusting your search criteria or filters.
      </p>
      <p v-else>
        Complete courses to earn certificates and showcase your achievements!
      </p>
      <router-link to="/student/browse" class="btn btn-primary" v-if="!searchQuery && !selectedCategory">
        <i class="bi bi-search me-1"></i>
        Browse Courses
      </router-link>
      <button @click="clearFilters" class="btn btn-outline-primary" v-else>
        <i class="bi bi-x-circle me-1"></i>
        Clear Filters
      </button>
    </div>

    <!-- Certificate Modal -->
    <div v-if="selectedCertificate" class="modal-overlay" @click="closeCertificateModal">
      <div class="modal-content certificate-modal" @click.stop>
        <div class="modal-header">
          <h4>Certificate of Completion</h4>
          <button @click="closeCertificateModal" class="close-btn">
            <i class="bi bi-x"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="certificate-display">
            <div class="certificate-frame">
              <div class="certificate-border">
                <div class="certificate-inner">
                  <div class="certificate-logo">
                    <i class="bi bi-mortarboard"></i>
                  </div>
                  <h2>Certificate of Completion</h2>
                  <p class="certificate-text">This is to certify that</p>
                  <h3 class="student-name">Student Name</h3>
                  <p class="certificate-text">has successfully completed the course</p>
                  <h3 class="course-name">{{ selectedCertificate.course.title }}</h3>
                  <div class="certificate-details-modal">
                    <div class="detail-row">
                      <span>Duration:</span>
                      <span>{{ selectedCertificate.course.duration_hours }} hours</span>
                    </div>
                    <div class="detail-row">
                      <span>Final Score:</span>
                      <span>{{ selectedCertificate.final_score }}%</span>
                    </div>
                    <div class="detail-row">
                      <span>Completion Date:</span>
                      <span>{{ formatDate(selectedCertificate.completed_at) }}</span>
                    </div>
                  </div>
                  <div class="certificate-footer">
                    <div class="signature-section">
                      <div class="signature-line"></div>
                      <p>{{ selectedCertificate.course.instructor }}</p>
                      <p class="signature-title">Course Instructor</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-actions">
          <button 
            @click="downloadCertificate(selectedCertificate)" 
            class="btn btn-primary"
            :disabled="downloading === selectedCertificate.id"
          >
            <i v-if="downloading === selectedCertificate.id" class="bi bi-hourglass me-1"></i>
            <i v-else class="bi bi-download me-1"></i>
            {{ downloading === selectedCertificate.id ? 'Downloading...' : 'Download PDF' }}
          </button>
          <button @click="shareCertificate(selectedCertificate)" class="btn btn-outline-primary">
            <i class="bi bi-share me-1"></i>
            Share
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StudentCertificates',
  data() {
    return {
      searchQuery: '',
      selectedCategory: '',
      sortBy: 'newest',
      currentPage: 1,
      certificatesPerPage: 10,
      selectedCertificate: null,
      
      // Real data from API
      certificates: [],
      totalPages: 1,
      totalCertificates: 0,
      stats: {
        total_certificates: 0,
        certificates_this_month: 0,
        average_score: 0,
        total_hours: 0
      },
      
      // State management
      loading: true,
      statsLoading: true,
      error: null,
      downloading: null, // ID of certificate being downloaded
      
      // Available filter options
      categories: [],
      levels: ['Beginner', 'Intermediate', 'Advanced']
    }
  },
  computed: {
    paginatedCertificates() {
      return this.certificates; // API already handles pagination
    },
    
    visiblePages() {
      const pages = [];
      const start = Math.max(1, this.currentPage - 2);
      const end = Math.min(this.totalPages, this.currentPage + 2);
      
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      
      return pages;
    }
  },
  async mounted() {
    await Promise.all([
      this.fetchCertificateStats(),
      this.fetchCertificates()
    ]);
  },
  methods: {
    async fetchCertificateStats() {
      try {
        this.statsLoading = true;
        
        const response = await fetch('/api/student/certificate-stats');
        
        if (response.ok) {
          const data = await response.json();
          this.stats = data;
        }
      } catch (error) {
        // Silent error handling for production
      } finally {
        this.statsLoading = false;
      }
    },

    async fetchCertificates() {
      try {
        this.loading = true;
        this.error = null;
        
        const params = new URLSearchParams({
          page: this.currentPage,
          sort_by: this.sortBy,
          per_page: this.certificatesPerPage
        });
        
        if (this.searchQuery) params.append('search', this.searchQuery);
        if (this.selectedCategory) params.append('category', this.selectedCategory);
        
        const response = await fetch(`/api/student/certificates?${params}`);
        
        if (response.ok) {
          const data = await response.json();
          
          this.certificates = data.data;
          this.totalPages = data.last_page;
          this.totalCertificates = data.total;
          
          // Extract unique categories
          const categoriesSet = new Set(data.data.map(cert => cert.course.category));
          this.categories = [...categoriesSet];
        } else {
          this.error = 'Failed to load certificates. Please try again.';
        }
      } catch (error) {
        this.error = 'Failed to load certificates. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    
    clearFilters() {
      this.searchQuery = '';
      this.selectedCategory = '';
      this.sortBy = 'newest';
      this.currentPage = 1;
      this.fetchCertificates();
    },
    
    goToPage(page) {
      this.currentPage = page;
      this.fetchCertificates();
    },
    
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    
    getLevelClass(level) {
      switch (level) {
        case 'Beginner': return 'level-beginner';
        case 'Intermediate': return 'level-intermediate';
        case 'Advanced': return 'level-advanced';
        default: return '';
      }
    },
    
    viewCertificate(certificate) {
      // Open certificate in new window
      const certificateUrl = `/api/student/certificates/${certificate.id}/view`;
      const certificateWindow = window.open(certificateUrl, '_blank');
      
      if (!certificateWindow) {
        alert('Please allow pop-ups to view certificates.');
      }
    },
    
    closeCertificateModal() {
      this.selectedCertificate = null;
    },
    
    async downloadCertificate(certificate) {
      if (this.downloading === certificate.id) {
        return;
      }
      
      try {
        this.downloading = certificate.id;
        
        const response = await fetch(`/api/student/certificates/${certificate.id}/download`);
        
        if (response.ok) {
          const data = await response.json();
          
          // Open certificate in new window for PDF generation
          const certificateWindow = window.open(data.certificate_url, '_blank');
          
          if (!certificateWindow) {
            alert('Please allow pop-ups to view and download certificates.');
          }
        } else {
          alert('Failed to download certificate. Please try again.');
        }
      } catch (error) {
        alert('Failed to download certificate. Please try again.');
      } finally {
        this.downloading = null;
      }
    },
    
    shareCertificate(certificate) {
      // Mock share functionality
      if (navigator.share) {
        navigator.share({
          title: `${certificate.course.title} - Certificate of Completion`,
          text: `I've completed the "${certificate.course.title}" course with a score of ${certificate.final_score}%!`,
          url: window.location.href
        });
      } else {
        // Fallback: copy to clipboard
        const shareText = `I've completed the "${certificate.course.title}" course with a score of ${certificate.final_score}%!`;
        navigator.clipboard.writeText(shareText).then(() => {
          alert('Certificate details copied to clipboard!');
        });
      }
    }
  },

  watch: {
    searchQuery() {
      // Debounce search
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.currentPage = 1;
        this.fetchCertificates();
      }, 500);
    },
    
    selectedCategory() {
      this.currentPage = 1;
      this.fetchCertificates();
    },
    
    sortBy() {
      this.currentPage = 1;
      this.fetchCertificates();
    }
  }
}
</script>

<style scoped>
.certificates-container {
  width: 100%;
  padding: 0 20px;
}

/* Loading State */
.spinner-border {
  width: 3rem;
  height: 3rem;
}

.alert {
  border-radius: 12px;
  margin-bottom: 32px;
}

/* Header Section */
.certificates-header {
  background: linear-gradient(135deg, #001d3d, #0077b6);
  color: white;
  padding: 40px;
  border-radius: 12px;
  margin-bottom: 32px;
  text-align: center;
}

.header-content h1 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 8px;
}

.header-content p {
  font-size: 1.1rem;
  opacity: 0.9;
  margin: 0;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 32px;
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

.bg-primary {
  background: linear-gradient(135deg, #001d3d, #0077b6);
}

.bg-success {
  background: linear-gradient(135deg, #0077b6, #003d5c);
}

.bg-warning {
  background: linear-gradient(135deg, #0099cc, #004d66);
}

.bg-info {
  background: linear-gradient(135deg, #4a90a4, #001d3d);
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  color: white;
  font-size: 1.8rem;
  background-color: rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  margin-right: 16px;
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

/* Filters Section */
.filters-section {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  margin-bottom: 32px;
  display: flex;
  gap: 20px;
  align-items: center;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 300px;
}

.search-input {
  width: 100%;
  padding: 12px 20px 12px 45px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #0077b6;
  box-shadow: 0 0 0 3px rgba(0, 119, 182, 0.1);
}

.search-box i {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #666;
}

.filter-controls {
  display: flex;
  gap: 12px;
}

.filter-select {
  padding: 8px 16px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background: white;
  color: #333;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-select:focus {
  outline: none;
  border-color: #0077b6;
}

/* Modern Certificates Grid */
.certificates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.certificate-card {
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(106, 90, 205, 0.1);
  position: relative;
}

.certificate-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(106, 90, 205, 0.15);
  border-color: rgba(106, 90, 205, 0.2);
}

/* Card Header */
.card-header {
  background: linear-gradient(135deg, #001d3d 0%, #0077b6 50%, #4a90a4 100%);
  padding: 20px;
  color: white;
  position: relative;
  overflow: hidden;
}

.card-header::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 100px;
  height: 100px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  transform: translate(30px, -30px);
}

.achievement-section {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 0;
}

.achievement-badge {
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  flex-shrink: 0;
}

.achievement-info {
  flex: 1;
  min-width: 0;
}

.course-title {
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0 0 8px 0;
  line-height: 1.3;
  color: white;
}

.completion-date {
  font-size: 0.875rem;
  opacity: 0.9;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
}

.score-badge {
  position: absolute;
  top: 24px;
  right: 24px;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  padding: 8px 12px;
  text-align: center;
  min-width: 70px;
}

.score-value {
  display: block;
  font-size: 1.25rem;
  font-weight: 700;
  line-height: 1;
}

.score-label {
  display: block;
  font-size: 0.75rem;
  opacity: 0.8;
  margin-top: 2px;
}

/* Card Body */
.card-body {
  padding: 20px;
}

.course-meta {
  margin-bottom: 20px;
}

.meta-tags {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.category-tag {
  background: linear-gradient(135deg, #f0f8ff, #e6f3ff);
  color: #0077b6;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  border: 1px solid rgba(0, 119, 182, 0.2);
  display: flex;
  align-items: center;
  gap: 4px;
}

.level-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

.level-beginner {
  background: linear-gradient(135deg, #ecfdf5, #d1fae5);
  color: #059669;
  border: 1px solid rgba(5, 150, 105, 0.2);
}

.level-intermediate {
  background: linear-gradient(135deg, #fffbeb, #fef3c7);
  color: #d97706;
  border: 1px solid rgba(217, 119, 6, 0.2);
}

.level-advanced {
  background: linear-gradient(135deg, #fef2f2, #fecaca);
  color: #dc2626;
  border: 1px solid rgba(220, 38, 38, 0.2);
}

/* Course Details */
.course-details {
  margin-bottom: 20px;
}

.detail-row {
  margin-bottom: 12px;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: #f8f9fa;
  border-radius: 12px;
  border: 1px solid #e9ecef;
}

.detail-item i {
  color: #0077b6;
  font-size: 1.1rem;
  width: 20px;
  text-align: center;
}

.detail-label {
  font-size: 0.8rem;
  color: #6b7280;
  font-weight: 500;
  min-width: 70px;
}

.detail-value {
  font-size: 0.9rem;
  color: #374151;
  font-weight: 600;
  flex: 1;
}

/* Certificate Preview */
.certificate-preview {
  background: linear-gradient(135deg, #f8f9ff, #eef2ff);
  border: 2px solid rgba(106, 90, 205, 0.1);
  border-radius: 14px;
  padding: 16px;
  margin-bottom: 0;
}

.preview-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.preview-icon {
  width: 40px;
  height: 40px;
  background: #0077b6;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.3rem;
  flex-shrink: 0;
}

.preview-text {
  flex: 1;
  min-width: 0;
}

.preview-title {
  display: block;
  font-weight: 600;
  color: #374151;
  font-size: 0.95rem;
  margin-bottom: 4px;
}

.preview-subtitle {
  display: block;
  color: #6b7280;
  font-size: 0.8rem;
}

.preview-verified {
  color: #059669;
  font-size: 1.5rem;
}

/* Card Actions */
.card-actions {
  padding: 16px 20px;
  background: #f8f9fa;
  border-top: 1px solid #e9ecef;
  display: flex;
  gap: 8px;
}

.action-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 16px;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  position: relative;
  overflow: hidden;
}

.action-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.action-btn span {
  position: relative;
  z-index: 1;
}

.action-btn i {
  position: relative;
  z-index: 1;
  font-size: 1rem;
}

.btn-view {
  background: white;
  color: #0077b6;
  border: 2px solid #0077b6;
}

.btn-view:hover:not(:disabled) {
  background: #0077b6;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 119, 182, 0.3);
}

.btn-download {
  background: #0077b6;
  color: white;
  border: 2px solid #0077b6;
}

.btn-download:hover:not(:disabled) {
  background: #001d3d;
  border-color: #001d3d;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 119, 182, 0.4);
}

.btn-share {
  background: white;
  color: #6b7280;
  border: 2px solid #e5e7eb;
}

.btn-share:hover:not(:disabled) {
  background: #6b7280;
  color: white;
  border-color: #6b7280;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
}

/* Spinning animation for download */
.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Bootstrap-compatible button styles */
.btn {
  display: inline-block;
  font-weight: 400;
  line-height: 1.5;
  color: #212529;
  text-align: center;
  text-decoration: none;
  vertical-align: middle;
  cursor: pointer;
  user-select: none;
  background-color: transparent;
  border: 1px solid transparent;
  padding: 0.375rem 0.75rem;
  font-size: 1rem;
  border-radius: 0.375rem;
  transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.btn:hover {
  color: #212529;
  text-decoration: none;
}

.btn:focus {
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn:disabled {
  pointer-events: none;
  opacity: 0.65;
}

.btn-primary {
  color: #fff;
  background-color: #0077b6;
  border-color: #0077b6;
}

.btn-primary:hover {
  color: #fff;
  background-color: #001d3d;
  border-color: #001d3d;
}

.btn-primary:focus {
  color: #fff;
  background-color: #001d3d;
  border-color: #001d3d;
  box-shadow: 0 0 0 0.25rem rgba(0, 119, 182, 0.5);
}

.btn-outline-primary {
  color: #0077b6;
  border-color: #0077b6;
}

.btn-outline-primary:hover {
  color: #fff;
  background-color: #0077b6;
  border-color: #0077b6;
}

.btn-outline-primary:focus {
  color: #fff;
  background-color: #0077b6;
  border-color: #0077b6;
  box-shadow: 0 0 0 0.25rem rgba(0, 119, 182, 0.25);
}

.btn-outline-secondary {
  color: #6c757d;
  border-color: #6c757d;
}

.btn-outline-secondary:hover {
  color: #fff;
  background-color: #6c757d;
  border-color: #6c757d;
}

.btn-outline-secondary:focus {
  color: #fff;
  background-color: #6c757d;
  border-color: #6c757d;
  box-shadow: 0 0 0 0.25rem rgba(108, 117, 125, 0.25);
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
  border-radius: 0.25rem;
}

/* Bootstrap spacing utility */
.me-1 {
  margin-right: 0.25rem !important;
}

/* Certificate Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.certificate-modal {
  background: white;
  border-radius: 16px;
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e0e0e0;
}

.modal-header h4 {
  margin: 0;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
  padding: 4px;
  border-radius: 4px;
}

.close-btn:hover {
  background: #f0f0f0;
}

.modal-body {
  padding: 20px;
}

.certificate-display {
  display: flex;
  justify-content: center;
}

.certificate-frame {
  width: 100%;
  max-width: 600px;
}

.certificate-border {
  border: 8px solid #0077b6;
  border-radius: 12px;
  padding: 8px;
  background: white;
}

.certificate-inner {
  border: 2px solid #0077b6;
  padding: 40px;
  text-align: center;
  background: linear-gradient(135deg, #fafafa, #ffffff);
}

.certificate-logo {
  font-size: 3rem;
  color: #0077b6;
  margin-bottom: 20px;
}

.certificate-inner h2 {
  color: #0077b6;
  font-size: 2rem;
  margin-bottom: 20px;
  font-weight: 700;
}

.certificate-text {
  color: #666;
  font-size: 1.1rem;
  margin-bottom: 10px;
}

.student-name,
.course-name {
  color: #333;
  font-size: 1.5rem;
  font-weight: 600;
  margin: 10px 0;
  padding: 10px 0;
  border-bottom: 1px solid #ddd;
}

.certificate-details-modal {
  margin: 20px 0;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-size: 0.95rem;
}

.certificate-footer {
  margin-top: 30px;
}

.signature-section {
  text-align: center;
}

.signature-line {
  width: 200px;
  height: 1px;
  background: #333;
  margin: 0 auto 10px;
}

.signature-title {
  font-size: 0.9rem;
  color: #666;
  margin: 5px 0 0;
}

.modal-actions {
  display: flex;
  gap: 12px;
  padding: 20px;
  border-top: 1px solid #e0e0e0;
  justify-content: center;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  margin-top: 32px;
}

.pagination-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border: 1px solid #e5e7eb;
  background: white;
  color: #374151;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #0077b6;
  color: #0077b6;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 4px;
}

.page-btn {
  width: 40px;
  height: 40px;
  border: 1px solid #e5e7eb;
  background: white;
  color: #374151;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.page-btn:hover {
  background: #f9fafb;
  border-color: #0077b6;
  color: #0077b6;
}

.page-btn.active {
  background: #0077b6;
  border-color: #0077b6;
  color: white;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.empty-icon {
  font-size: 4rem;
  color: #d1d5db;
  margin-bottom: 16px;
}

.empty-state h3 {
  color: #374151;
  margin-bottom: 8px;
}

.empty-state p {
  color: #6b7280;
  margin-bottom: 24px;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .certificates-container {
    padding: 0 16px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 1024px) {
  .certificates-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
  }
}

@media (max-width: 768px) {
  .certificates-container {
    padding: 0 15px;
  }
  
  .certificates-header {
    padding: 24px;
  }
  
  .header-content h1 {
    font-size: 2rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .filters-section {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-box {
    min-width: auto;
  }
  
  .filter-controls {
    justify-content: stretch;
  }
  
  .filter-select {
    flex: 1;
  }
  
  .certificates-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .certificate-card {
    border-radius: 16px;
  }
  
  .card-header {
    padding: 18px;
    flex-direction: column;
    text-align: center;
    gap: 16px;
  }
  
  .achievement-section {
    width: 100%;
  }
  
  .score-badge {
    position: static;
    align-self: center;
    margin-top: 0;
  }
  
  .card-body {
    padding: 18px;
  }
  
  .card-actions {
    padding: 14px 18px;
    flex-direction: column;
    gap: 12px;
  }
  
  .action-btn {
    width: 100%;
    justify-content: center;
  }
  
  .modal-overlay {
    padding: 10px;
  }
  
  .certificate-inner {
    padding: 20px;
  }
  
  .certificate-inner h2 {
    font-size: 1.5rem;
  }
  
  .student-name,
  .course-name {
    font-size: 1.2rem;
  }
}

@media (max-width: 480px) {
  .certificates-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .course-title {
    font-size: 1.05rem;
  }
  
  .meta-tags {
    justify-content: center;
  }
  
  .preview-container {
    flex-direction: column;
    text-align: center;
    gap: 10px;
  }
  
  .detail-item {
    padding: 8px 12px;
  }
  
  .action-btn {
    padding: 12px 16px;
    font-size: 0.85rem;
  }
  
  .achievement-badge {
    width: 42px;
    height: 42px;
    font-size: 1.1rem;
  }
  
  .card-header {
    padding: 16px;
  }
  
  .card-body {
    padding: 16px;
  }
  
  .card-actions {
    padding: 12px 16px;
  }
}
</style>