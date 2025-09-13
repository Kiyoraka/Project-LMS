import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createRouter, createWebHistory } from 'vue-router';

// Create Pinia store instance
const pinia = createPinia();

// ==========================================
// Admin Dashboard Setup
// ==========================================

// Import Admin components
import AdminApp from './Admin/App.vue';
import AdminDashboard from './Admin/views/ConditionalDashboard.vue';
import UserManagement from './Admin/views/UserManagement.vue';

// Create Admin router instance with explicit configuration  
const adminRouter = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/admin',
            redirect: '/admin/dashboard'
        },
        {
            path: '/admin/dashboard',
            name: 'admin.dashboard',
            component: AdminDashboard,
        },
        {
            path: '/admin/users',
            name: 'admin.users',
            component: UserManagement,
        }
    ]
});

// ==========================================
// Teacher Dashboard Setup Test
// ==========================================

// Import Teacher components
import TeacherApp from './Teacher/App.vue';
import TeacherDashboard from './Teacher/views/ConditionalDashboard.vue';
import TeacherCourses from './Teacher/views/Courses.vue';
import TeacherCourseView from './Teacher/views/CourseView.vue';
import TeacherCourseEdit from './Teacher/views/CourseEdit.vue';
import TeacherStudents from './Teacher/views/Students.vue';
import TeacherMessages from './Teacher/views/Messages.vue';

// Create Teacher router instance with explicit configuration
const teacherRouter = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/teacher',
            redirect: '/teacher/dashboard'
        },
        {
            path: '/teacher/dashboard',
            name: 'teacher.dashboard',
            component: TeacherDashboard,
        },
        {
            path: '/teacher/courses',
            name: 'teacher.courses',
            component: TeacherCourses,
        },
        {
            path: '/teacher/students',
            name: 'teacher.students',
            component: TeacherStudents,
        },
        {
            path: '/teacher/messages',
            name: 'teacher.messages',
            component: TeacherMessages,
        },
        // Course management routes
        {
            path: '/teacher/courses/create',
            name: 'teacher.courses.create',
            component: TeacherCourses, // Modal-based creation in main courses view
        },
        {
            path: '/teacher/courses/:id',
            name: 'teacher.courses.view',
            component: TeacherCourseView,
            props: true
        },
        {
            path: '/teacher/courses/:id/edit',
            name: 'teacher.courses.edit',
            component: TeacherCourseEdit,
            props: true
        },
        {
            path: '/teacher/students/:id',
            name: 'teacher.students.view',
            component: TeacherStudents, // Will be replaced with actual component
            props: true
        }
    ]
});

// ==========================================
// Conditional Mounting
// ==========================================

// Mount Admin app if admin-app element exists
const adminAppElement = document.getElementById('admin-app');
if (adminAppElement) {
    const adminApp = createApp(AdminApp);
    adminApp.use(adminRouter);
    adminApp.use(pinia);
    adminApp.mount('#admin-app');
}

// Mount Teacher app if teacher-app element exists
const teacherAppElement = document.getElementById('teacher-app');
if (teacherAppElement) {
    const teacherApp = createApp(TeacherApp);
    teacherApp.use(teacherRouter);
    teacherApp.use(pinia);
    teacherApp.mount('#teacher-app');
}

// ==========================================
// Student Dashboard Setup
// ==========================================

// Import Student components
import StudentApp from './Student/App.vue';
import StudentDashboard from './Student/views/ConditionalDashboard.vue';
import StudentCourses from './Student/views/Courses.vue';
import StudentBrowse from './Student/views/Browse.vue';
import StudentCertificates from './Student/views/Certificates.vue';
import StudentMessages from './Student/views/Messages.vue';
import StudentLearning from './Student/views/Learning.vue';

// Create Student router instance with explicit configuration
const studentRouter = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/student',
            redirect: '/student/dashboard'
        },
        {
            path: '/student/dashboard',
            name: 'student.dashboard',
            component: StudentDashboard,
        },
        {
            path: '/student/courses',
            name: 'student.courses',
            component: StudentCourses,
        },
        {
            path: '/student/browse',
            name: 'student.browse',
            component: StudentBrowse,
        },
        {
            path: '/student/certificates',
            name: 'student.certificates',
            component: StudentCertificates,
        },
        {
            path: '/student/messages',
            name: 'student.messages',
            component: StudentMessages,
        },
        {
            path: '/student/learning/:id',
            name: 'student.learning',
            component: StudentLearning,
            props: true
        },
        {
            path: '/student/profile',
            name: 'student.profile',
            component: StudentDashboard, // Will be replaced with actual profile component
        }
    ]
});

// Mount Student app if student-app element exists
const studentAppElement = document.getElementById('student-app');
if (studentAppElement) {
    const studentApp = createApp(StudentApp);
    studentApp.use(studentRouter);
    studentApp.use(pinia);
    studentApp.mount('#student-app');
}