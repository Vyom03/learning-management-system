<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ authStore.userRole === 'student' ? 'My Enrolled Courses' : authStore.userRole === 'teacher' ? 'My Courses' : 'All Courses' }}</h1>
        <div v-if="authStore.isAuthenticated && authStore.userRole === 'admin'">
          <router-link
            to="/create-course"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 uppercase tracking-widest transition"
          >
            Create Course
          </router-link>
        </div>
      </div>

      <div v-if="loading" class="text-center py-12">
        <p class="text-gray-600">Loading courses...</p>
      </div>

      <div v-else-if="courses.length === 0" class="text-center py-12">
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
          <p class="text-gray-600 mb-4">
            <span v-if="authStore.userRole === 'student'">You haven't enrolled in any courses yet.</span>
            <span v-else-if="authStore.userRole === 'teacher'">You haven't created any courses yet.</span>
            <span v-else>No courses available.</span>
          </p>
          <p v-if="authStore.userRole === 'student'" class="text-sm text-gray-500">
            Contact your administrator to get enrolled in courses.
          </p>
        </div>
      </div>

      <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="course in courses"
          :key="course.id"
          class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition border border-gray-200"
        >
          <div class="p-6">
            <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ course.title }}</h3>
            <p class="text-gray-600 mb-4 line-clamp-3">{{ course.description }}</p>
            <div class="flex justify-between items-center mb-4">
              <span class="text-sm text-gray-500">Instructor: {{ course.instructor_name }}</span>
              <span class="text-sm text-gray-500">{{ course.enrollment_count }} students</span>
            </div>
            <router-link
              :to="`/courses/${course.id}`"
              class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md transition"
            >
              View Course
            </router-link>
          </div>
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
const courses = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    console.log('Loading courses, user role:', authStore.userRole)
    const response = await api.get('/courses')
    console.log('Courses response:', response.data)
    courses.value = response.data.courses || []
    console.log('Courses loaded:', courses.value.length, 'courses')
  } catch (error) {
    console.error('Failed to load courses:', error)
    console.error('Error details:', error.response?.data || error.message)
    courses.value = []
  } finally {
    loading.value = false
  }
})
</script>

