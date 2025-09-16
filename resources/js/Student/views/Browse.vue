<template>
  <div class="browse-container">
    <!-- Header Section -->
    <div class="browse-header">
      <div class="header-content">
        <h1>Browse Courses</h1>
        <p>Discover and enroll in courses to expand your knowledge</p>
      </div>
      
      <!-- Search and Filters -->
      <div class="search-filters">
        <div class="search-box">
          <input 
            type="text" 
            class="search-input" 
            placeholder="Search courses..." 
            v-model="searchQuery"
            @input="filterCourses"
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
          
          <select v-model="selectedLevel" class="filter-select">
            <option value="">All Levels</option>
            <option v-for="level in levels" :key="level" :value="level">
              {{ level }}
            </option>
          </select>
          
          <select v-model="sortBy" class="filter-select">
            <option value="newest">Newest</option>
            <option value="popular">Most Popular</option>
            <option value="rating">Highest Rated</option>
            <option value="duration">Duration</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Featured Courses -->
    <div class="featured-section" v-if="!searchQuery && !selectedCategory && !selectedLevel">
      <h2 class="section-title">
        <i class="bi bi-fire me-2"></i>
        Most Popular Courses
      </h2>
      
      <!-- Featured Loading State -->
      <div v-if="featuredLoading" class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Loading most popular courses...</p>
      </div>
      
      <div v-else-if="featuredCourses.length > 0" class="featured-grid">
        <div 
          v-for="course in featuredCourses" 
          :key="course.id"
          class="featured-card"
        >
          <div class="course-image">
            <img
              v-if="course.thumbnail"
              :src="course.thumbnail"
              :alt="course.title"
              @error="handleImageError($event, course)"
            >
            <div v-else class="no-image-placeholder">
              <i class="bi bi-image"></i>
            </div>
            <div class="course-badge featured-badge">
              <i class="bi bi-fire"></i>
              Most Popular
            </div>
          </div>
          <div class="course-content">
            <div class="course-meta-top">
              <span class="category-tag">{{ course.category }}</span>
              <span class="level-badge" :class="getLevelClass(course.level)">{{ course.level }}</span>
            </div>
            <h3 class="course-title">{{ course.title }}</h3>
            <p class="course-description">{{ course.short_description }}</p>
            <div class="course-stats">
              <div class="stat-item">
                <i class="bi bi-people"></i>
                <span>{{ course.students_count }}</span>
              </div>
              <div class="stat-item">
                <i class="bi bi-clock"></i>
                <span>{{ course.duration_hours }}h</span>
              </div>
              <div class="stat-item">
                <i class="bi bi-star-fill"></i>
                <span>{{ course.rating }}</span>
              </div>
            </div>
            <div class="course-footer">
              <button 
                @click="enrollInCourse(course)" 
                class="btn btn-primary btn-enroll"
                :disabled="isEnrolled(course) || enrolling === course.id"
              >
                <span v-if="enrolling === course.id">
                  <div class="spinner-border spinner-border-sm me-1" role="status"></div>
                  Enrolling...
                </span>
                <span v-else-if="isEnrolled(course)">
                  <i class="bi bi-check-circle me-1"></i>
                  Enrolled
                </span>
                <span v-else>Enroll Now</span>
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- No featured courses fallback -->
      <div v-else class="text-center py-4 text-muted">
        <p>No popular courses available at the moment.</p>
      </div>
    </div>

    <!-- All Courses -->
    <div class="courses-section">
      <div class="section-header">
        <h2 class="section-title">
          <span v-if="searchQuery || selectedCategory || selectedLevel">Search Results</span>
          <span v-else>All Courses</span>
        </h2>
        <div class="results-count">
          {{ loading ? 'Loading...' : `${totalCourses} course${totalCourses !== 1 ? 's' : ''} found` }}
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ error }}
        <button @click="fetchCourses" class="btn btn-sm btn-outline-danger ms-3">
          Try Again
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading && !error" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted">Loading courses...</p>
      </div>

      <div v-if="!loading && !error && courses.length > 0">
        <div class="courses-grid">
          <div 
            v-for="course in paginatedCourses" 
            :key="course.id"
            class="course-card"
          >
            <div class="course-image">
              <img
                v-if="course.thumbnail"
                :src="course.thumbnail"
                :alt="course.title"
                @error="handleImageError($event, course)"
              >
              <div v-else class="no-image-placeholder">
                <i class="bi bi-image"></i>
              </div>
              <div v-if="course.is_new" class="course-badge new-badge">
                <i class="bi bi-lightning-fill"></i>
                New
              </div>
            </div>
            <div class="course-content">
              <div class="course-meta-top">
                <span class="category-tag">{{ course.category }}</span>
                <span class="level-badge" :class="getLevelClass(course.level)">{{ course.level }}</span>
              </div>
              <h3 class="course-title">{{ course.title }}</h3>
              <p class="course-description">{{ course.short_description }}</p>
              <div class="instructor-info">
                <div class="instructor-avatar">
                  <i class="bi bi-person"></i>
                </div>
                <span class="instructor-name">{{ course.instructor }}</span>
              </div>
              <div class="course-stats">
                <div class="stat-item">
                  <i class="bi bi-people"></i>
                  <span>{{ course.students_count }}</span>
                </div>
                <div class="stat-item">
                  <i class="bi bi-clock"></i>
                  <span>{{ course.duration_hours }}h</span>
                </div>
                <div class="stat-item">
                  <i class="bi bi-star-fill"></i>
                  <span>{{ course.rating }}</span>
                </div>
              </div>
              <div class="course-footer">
                <button 
                  @click="enrollInCourse(course)" 
                  class="btn btn-primary btn-enroll"
                  :disabled="isEnrolled(course) || enrolling === course.id"
                >
                  <span v-if="enrolling === course.id">
                    <div class="spinner-border spinner-border-sm me-1" role="status"></div>
                    Enrolling...
                  </span>
                  <span v-else-if="isEnrolled(course)">
                    <i class="bi bi-check-circle me-1"></i>
                    Enrolled
                  </span>
                  <span v-else>
                    <i class="bi bi-plus-circle me-1"></i>
                    Enroll
                  </span>
                </button>
              </div>
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
      <div v-if="!loading && !error && courses.length === 0" class="empty-state">
        <div class="empty-icon">
          <i class="bi bi-search"></i>
        </div>
        <h3>No courses found</h3>
        <p v-if="searchQuery || selectedCategory || selectedLevel">
          Try adjusting your search criteria or filters to find more courses.
        </p>
        <p v-else>
          No courses are available at the moment. Check back later!
        </p>
        <button @click="clearFilters" class="btn btn-primary" v-if="searchQuery || selectedCategory || selectedLevel">
          Clear Filters
        </button>
      </div>
    </div>

    <!-- Enrollment Result Modal -->
    <div v-if="showEnrollmentModal" class="modal-overlay" @click="closeEnrollmentModal">
      <div class="modal-content enrollment-modal" @click.stop>
        <div class="enrollment-header" :class="enrollmentType">
          <div class="enrollment-icon">
            <i v-if="enrollmentType === 'success'" class="bi bi-check-circle-fill"></i>
            <i v-else class="bi bi-exclamation-triangle-fill"></i>
          </div>
          <h3>{{ enrollmentType === 'success' ? 'Enrollment Successful!' : 'Enrollment Failed' }}</h3>
        </div>
        
        <div class="enrollment-body">
          <p>{{ enrollmentMessage }}</p>
        </div>
        
        <div class="enrollment-actions">
          <button @click="closeEnrollmentModal" class="btn" :class="enrollmentType === 'success' ? 'btn-success' : 'btn-danger'">
            <i class="bi bi-check-circle"></i>
            OK
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BrowseCourses',
  data() {
    return {
      searchQuery: '',
      selectedCategory: '',
      selectedLevel: '',
      sortBy: 'newest',
      currentPage: 1,
      coursesPerPage: 15,
      
      // Real data from API
      courses: [],
      featuredCourses: [],
      filteredCourses: [],
      totalPages: 1,
      totalCourses: 0,
      loading: true,
      featuredLoading: true,
      error: null,
      enrolling: null, // ID of course being enrolled in
      
      // Enrollment modal
      showEnrollmentModal: false,
      enrollmentMessage: '',
      enrollmentType: 'success', // 'success' or 'error'
      
             // Available filter options
       categories: [],
       levels: ['Beginner', 'Intermediate', 'Advanced']
    }
  },
  computed: {
    paginatedCourses() {
      return this.courses; // API already handles pagination
    },
    
    visiblePages() {
      const pages = [];
      const isMobile = window.innerWidth <= 768;
      const range = isMobile ? 1 : 2; // Show fewer pages on mobile
      
      const start = Math.max(1, this.currentPage - range);
      const end = Math.min(this.totalPages, this.currentPage + range);
      
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      
      return pages;
    }
  },
  async mounted() {
    await Promise.all([
      this.fetchFeaturedCourses(), // Fetch top 5 most popular courses
      this.fetchCourses()
    ]);
    
    // Add window resize listener for responsive pagination
    window.addEventListener('resize', this.handleResize);
  },
  
  beforeUnmount() {
    // Clean up event listener
    window.removeEventListener('resize', this.handleResize);
  },
  methods: {
    async fetchFeaturedCourses() {
      try {
        this.featuredLoading = true;
        
        const response = await fetch('/api/student/featured-courses');
        
        if (response.ok) {
          const data = await response.json();
          this.featuredCourses = data;
          
          // Extract unique categories
          const categoriesSet = new Set(data.map(course => course.category));
          this.categories = [...categoriesSet];
        }
      } catch (error) {
        // Silent error handling for production
      } finally {
        this.featuredLoading = false;
      }
    },

    async fetchCourses() {
      try {
        this.loading = true;
        this.error = null;
        
        const params = new URLSearchParams({
          page: this.currentPage,
          sort_by: this.sortBy
        });
        
        if (this.searchQuery) params.append('search', this.searchQuery);
        if (this.selectedCategory) params.append('category', this.selectedCategory);
        if (this.selectedLevel) params.append('level', this.selectedLevel);
        
        const response = await fetch(`/api/student/courses?${params}`);
        
        if (response.ok) {
          const data = await response.json();
          
          this.courses = data.data;
          this.totalPages = data.last_page;
          this.totalCourses = data.total;
        } else {
          this.error = 'Failed to load courses. Please try again.';
        }
      } catch (error) {
        this.error = 'Failed to load courses. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    
    clearFilters() {
      this.searchQuery = '';
      this.selectedCategory = '';
      this.selectedLevel = '';
      this.sortBy = 'newest';
      this.currentPage = 1;
      this.fetchCourses();
    },
    
    goToPage(page) {
      this.currentPage = page;
      this.fetchCourses();
      
      // Scroll to top of courses section for better UX
      this.$nextTick(() => {
        const coursesSection = document.querySelector('.courses-section');
        if (coursesSection) {
          coursesSection.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
          });
        }
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
    
    isEnrolled(course) {
      return course.is_enrolled;
    },
    
    async enrollInCourse(course) {
      if (this.isEnrolled(course) || this.enrolling === course.id) {
        return;
      }
      
      try {
        this.enrolling = course.id;
        
        const response = await fetch(`/api/student/courses/${course.id}/enroll`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.showEnrollmentMessage('success', `Successfully enrolled in "${course.title}"!`);
          
          // Update the course's enrollment status
          course.is_enrolled = true;
          
          // Optionally redirect to the course
          // this.$router.push(`/student/learning/${course.id}`);
        } else {
          this.showEnrollmentMessage('error', data.message || 'Failed to enroll in course');
        }
      } catch (error) {
        this.showEnrollmentMessage('error', 'Failed to enroll in course. Please try again.');
      } finally {
        this.enrolling = null;
      }
    },
    
    handleResize() {
      // Force reactivity update for pagination on resize
      this.$forceUpdate();
    },
    
    showEnrollmentMessage(type, message) {
      this.enrollmentType = type;
      this.enrollmentMessage = message;
      this.showEnrollmentModal = true;
    },
    
    closeEnrollmentModal() {
      this.showEnrollmentModal = false;
      this.enrollmentMessage = '';
      this.enrollmentType = 'success';
    },

    handleImageError(event, course) {
      // If image fails to load, hide it
      event.target.style.display = 'none';
      // Add a simple icon placeholder
      const placeholder = document.createElement('div');
      placeholder.className = 'no-image-placeholder';
      placeholder.innerHTML = '<i class="bi bi-image"></i>';
      event.target.parentElement.appendChild(placeholder);
    }
  },

  watch: {
    searchQuery() {
      // Debounce search
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.currentPage = 1;
        this.fetchCourses();
      }, 500);
    },
    
    selectedCategory() {
      this.currentPage = 1;
      this.fetchCourses();
    },
    
    selectedLevel() {
      this.currentPage = 1;
      this.fetchCourses();
    },
    
    sortBy() {
      this.currentPage = 1;
      this.fetchCourses();
    }
  }
}
</script>

<style scoped>
.browse-container {
  width: 100%;
  padding: 0 20px;
}

/* Header Section */
.browse-header {
  background: linear-gradient(135deg, #001d3d, #0077b6);
  color: white;
  padding: 40px;
  border-radius: 12px;
  margin-bottom: 32px;
}

.header-content {
  text-align: center;
  margin-bottom: 32px;
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

/* Search and Filters */
.search-filters {
  display: flex;
  flex-direction: column;
  gap: 20px;
  align-items: center;
}

.search-box {
  position: relative;
  width: 100%;
  max-width: 500px;
}

.search-input {
  width: 100%;
  padding: 12px 20px 12px 50px;
  border: none;
  border-radius: 25px;
  font-size: 1rem;
  background: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.search-input:focus {
  outline: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.search-box i {
  position: absolute;
  left: 18px;
  top: 50%;
  transform: translateY(-50%);
  color: #0077b6;
  font-size: 1.1rem;
}

.filter-controls {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  justify-content: center;
}

.filter-select {
  padding: 8px 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-select:focus {
  outline: none;
  border-color: rgba(255, 255, 255, 0.6);
  background: rgba(255, 255, 255, 0.2);
}

.filter-select option {
  background: #0077b6;
  color: white;
}

/* Featured Section */
.featured-section {
  margin-bottom: 48px;
}

.section-title {
  font-size: 1.8rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
}

.featured-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 20px;
  max-width: 1600px;
  align-items: stretch;
}

.featured-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.featured-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
}

/* Course Cards */
.courses-section {
  margin-bottom: 32px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.results-count {
  color: #666;
  font-size: 0.95rem;
}

.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
  align-items: stretch;
}

.course-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.course-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.course-image {
  position: relative;
  height: 180px;
  overflow: hidden;
}

.course-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  background-color: #f8f9fa;
}

.course-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

.featured-badge {
  background: linear-gradient(135deg, #ffd700, #ffed4e);
  color: #8b5a00;
}

.new-badge {
  background: linear-gradient(135deg, #10b981, #34d399);
  color: white;
}

.course-content {
  padding: 20px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.course-meta-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.category-tag {
  background: #f3f4f6;
  color: #6b7280;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
}

.level-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
}

.level-beginner {
  background: #dcfce7;
  color: #16a34a;
}

.level-intermediate {
  background: #fef3c7;
  color: #d97706;
}

.level-advanced {
  background: #fee2e2;
  color: #dc2626;
}

.course-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 8px;
  line-height: 1.3;
}

.course-description {
  color: #666;
  font-size: 0.9rem;
  line-height: 1.5;
  margin-bottom: 16px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.instructor-info {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
}

.instructor-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #0077b6;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
}

.instructor-name {
  font-size: 0.9rem;
  color: #666;
}

.course-stats {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
  font-size: 0.85rem;
  color: #666;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

.course-footer {
  display: flex;
  justify-content: center;
  align-items: center;
  padding-top: 16px;
  border-top: 1px solid #f0f0f0;
  margin-top: auto;
}

.free-tag {
  background: #dcfce7;
  color: #16a34a;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
}

.price-tag {
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
}

.btn-enroll {
  width: 100%;
  padding: 8px 20px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
  background: #0077b6;
  border: none;
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-enroll:hover:not(:disabled) {
  background: #001d3d;
  transform: translateY(-1px);
}

.btn-enroll:disabled {
  background: #e5e7eb;
  color: #9ca3af;
  cursor: not-allowed;
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
/* Desktop: 5 courses in a row for larger screens */
@media (min-width: 1600px) {
  .featured-grid {
    grid-template-columns: repeat(5, 1fr);
  }
  
  .courses-grid {
    grid-template-columns: repeat(5, 1fr);
    max-width: 1600px;
  }
}

/* Large screens: 4 courses per row */
@media (min-width: 1200px) and (max-width: 1599px) {
  .featured-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .courses-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

/* Medium screens: Container padding adjustment */
@media (max-width: 1200px) {
  .browse-container {
    max-width: 100%;
    padding: 0 16px;
  }
}

/* Medium screens: 3 courses per row */
@media (min-width: 900px) and (max-width: 1199px) {
  .featured-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .courses-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Small desktop: 2 courses per row */
@media (min-width: 600px) and (max-width: 899px) {
  .courses-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Tablet and mobile */
@media (max-width: 768px) {
  .browse-container {
    padding: 0 15px;
  }
  
  .browse-header {
    padding: 24px;
  }
  
  .header-content h1 {
    font-size: 2rem;
  }
  
  .search-filters {
    gap: 16px;
  }
  
  .filter-controls {
    flex-direction: column;
    width: 100%;
  }
  
  .filter-select {
    width: 100%;
  }
  
  .featured-grid,
  .courses-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .results-count {
    font-size: 0.9rem;
  }
  
  /* Mobile pagination improvements */
  .pagination {
    flex-wrap: wrap;
    gap: 4px;
  }
  
  .pagination-btn {
    padding: 6px 12px;
    font-size: 0.9rem;
  }
  
  .page-btn {
    width: 36px;
    height: 36px;
    font-size: 0.9rem;
  }
  
  .page-numbers {
    gap: 2px;
  }
  
  /* Course card mobile optimizations */
  .course-card {
    margin-bottom: 4px;
  }
  
  .course-image {
    height: 160px;
  }
  
  .course-content {
    padding: 16px;
  }
  
  .course-title {
    font-size: 1.1rem;
  }
  
  .course-description {
    font-size: 0.85rem;
    margin-bottom: 12px;
  }
  
  .course-stats {
    gap: 12px;
    font-size: 0.8rem;
  }
  
  .btn-enroll {
    padding: 6px 16px;
    font-size: 0.85rem;
  }
  
  /* Mobile modal adjustments */
  .enrollment-modal {
    max-width: 95%;
    margin: 20px;
  }
  
  .enrollment-header {
    padding: 20px;
  }
  
  .enrollment-header h3 {
    font-size: 1.2rem;
  }
  
  .enrollment-icon {
    width: 40px;
    height: 40px;
  }
  
  .enrollment-icon i {
    font-size: 1.3rem;
  }
  
  .enrollment-body {
    padding: 20px;
  }
  
  .enrollment-actions {
    padding: 16px 20px;
  }
}

/* Enrollment Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
  padding: 20px;
  backdrop-filter: blur(4px);
}

.enrollment-modal {
  background: white;
  border-radius: 20px;
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
  animation: slideUp 0.3s ease-out;
  margin: auto;
}

@keyframes slideUp {
  from { 
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  to { 
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.enrollment-header {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 24px;
  color: white;
}

.enrollment-header.success {
  background: linear-gradient(135deg, #001d3d 0%, #0077b6 100%);
}

.enrollment-header.error {
  background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
}

.enrollment-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  flex-shrink: 0;
}

.enrollment-icon i {
  font-size: 1.5rem;
  color: white;
}

.enrollment-header h3 {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 600;
}

.enrollment-body {
  padding: 24px;
  text-align: center;
}

.enrollment-body p {
  margin: 0;
  color: #374151;
  font-size: 1rem;
  line-height: 1.5;
}

.enrollment-actions {
  display: flex;
  justify-content: center;
  padding: 20px 24px;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
}

.btn-success {
  background: #0077b6;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-success:hover {
  background: #001d3d;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 119, 182, 0.4);
}

.btn-danger {
  background: #dc2626;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-danger:hover {
  background: #b91c1c;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
}

/* No image placeholder */
.no-image-placeholder {
  width: 100%;
  height: 100%;
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
  color: #9ca3af;
}

.no-image-placeholder i {
  font-size: 3rem;
  opacity: 0.5;
}

.course-image {
  position: relative;
  overflow: hidden;
}

.course-image .no-image-placeholder {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}
</style>