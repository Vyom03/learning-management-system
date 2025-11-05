<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <header class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
      </header>
      
      <div v-if="authStore.userRole === 'student'" class="space-y-6">
        <div class="grid md:grid-cols-3 gap-6">
          <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">My Courses</h3>
            <p class="text-3xl font-bold text-indigo-600">{{ stats.enrollments }}</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Certificates</h3>
            <p class="text-3xl font-bold text-green-600">{{ stats.certificates }}</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Quiz Attempts</h3>
            <p class="text-3xl font-bold text-blue-600">{{ stats.quizAttempts }}</p>
          </div>
        </div>
        
        <!-- Info Note for Students -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-blue-700">
                <strong>Note:</strong> Access new quizzes from your enrolled courses. Go to <router-link to="/courses" class="font-semibold underline">Courses</router-link> and select a course to view available quizzes.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Teacher Dashboard -->
      <div v-if="authStore.userRole === 'teacher'" class="space-y-6">
        <div class="grid md:grid-cols-3 gap-6">
          <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">My Courses</h3>
            <p class="text-3xl font-bold text-indigo-600">{{ stats.myCourses }}</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Total Students</h3>
            <p class="text-3xl font-bold text-green-600">{{ stats.totalStudents }}</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Quizzes Created</h3>
            <p class="text-3xl font-bold text-blue-600">{{ stats.quizzes }}</p>
          </div>
        </div>
        
        <!-- Info Note for Teachers -->
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-indigo-700">
                <strong>Note:</strong> To create quizzes for your courses, go to <router-link to="/courses" class="font-semibold underline">Courses</router-link>, select a course, and click "Create Quiz" from the course detail page.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Admin Dashboard -->
      <div v-else-if="authStore.userRole === 'admin'" class="space-y-6">
        <div class="grid md:grid-cols-3 gap-6">
          <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Total Courses</h3>
            <p class="text-3xl font-bold text-indigo-600">{{ stats.myCourses }}</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Total Students</h3>
            <p class="text-3xl font-bold text-green-600">{{ stats.totalStudents }}</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Total Quizzes</h3>
            <p class="text-3xl font-bold text-blue-600">{{ stats.quizzes }}</p>
          </div>
        </div>
        
        <!-- Admin Management Section -->
        <div class="mt-6">
          <h2 class="text-2xl font-semibold mb-4 text-gray-900">Management</h2>
          <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
              <h3 class="text-lg font-semibold mb-2 text-gray-700">User & Teacher Management</h3>
              <p class="text-gray-600 mb-4">Manage users, teachers, and students from the Student Management System.</p>
              <p class="text-sm text-gray-500 italic">Access this feature from the Student Management System.</p>
            </div>
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
              <h3 class="text-lg font-semibold mb-2 text-gray-700">Quiz Management</h3>
              <p class="text-gray-600 mb-4">View all quizzes created by teachers across all courses.</p>
              <router-link to="/courses" class="text-indigo-600 hover:text-indigo-700 font-medium">
                View All Courses →
              </router-link>
            </div>
          </div>
        </div>
        
        <!-- Info Note for Admin -->
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-amber-700">
                <strong>Note:</strong> As an admin, you can view all quizzes and manage users/teachers, but quiz creation is handled by teachers from their course pages.
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-8">
        <h2 class="text-2xl font-semibold mb-4 text-gray-900">Recent Activity</h2>
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
          <p class="text-gray-600">No recent activity</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Navbar from '../components/Navbar.vue'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'

const authStore = useAuthStore()
const stats = ref({
  enrollments: 0,
  certificates: 0,
  quizAttempts: 0,
  myCourses: 0,
  totalStudents: 0,
  quizzes: 0
})

onMounted(async () => {
  console.log('Dashboard mounted, isAuthenticated:', authStore.isAuthenticated)
  if (authStore.isAuthenticated) {
    await authStore.fetchUser()
    console.log('User fetched, role:', authStore.userRole)
    
    // Load stats based on user role
    try {
      console.log('Fetching dashboard stats...')
      const response = await api.get('/dashboard/stats')
      console.log('Dashboard stats response:', response.data)
      if (response.data) {
        stats.value = {
          enrollments: Number(response.data.enrollments) || 0,
          certificates: Number(response.data.certificates) || 0,
          quizAttempts: Number(response.data.quizAttempts) || 0,
          myCourses: Number(response.data.myCourses) || 0,
          totalStudents: Number(response.data.totalStudents) || 0,
          quizzes: Number(response.data.quizzes) || 0
        }
        console.log('Stats updated:', stats.value)
      }
    } catch (error) {
      console.error('Failed to load dashboard stats:', error)
      console.error('Error details:', error.response?.data || error.message)
    }
  } else {
    console.log('User not authenticated, skipping stats fetch')
  }
})
</script>

