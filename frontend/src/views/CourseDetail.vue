<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="loading" class="text-center py-12">
        <p class="text-gray-600">Loading course...</p>
      </div>

      <div v-else-if="course">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <h1 class="text-3xl font-bold mb-4">{{ course.title }}</h1>
          <p class="text-gray-600 mb-4">{{ course.description }}</p>
          <div class="flex items-center space-x-4 mb-4">
            <span class="text-sm text-gray-500">Instructor: {{ course.instructor_name }}</span>
          </div>
          
          <div v-if="authStore.isAuthenticated">
            <div v-if="authStore.userRole === 'student'">
              <div class="flex items-center space-x-4">
                <span class="text-green-600 font-semibold flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                  </svg>
                  Enrolled in this course
                </span>
                <router-link
                  :to="`/forums/${course.id}`"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition"
                >
                  Go to Forum
                </router-link>
                <span v-if="enrollment" class="text-sm text-gray-600">
                  Progress: {{ (enrollment.progress_percentage || 0).toFixed(0) }}%
                </span>
              </div>
            </div>
            <div v-else-if="authStore.userRole === 'teacher'">
              <div class="flex space-x-4">
                <router-link
                  :to="`/quizzes/${course.id}/create`"
                  class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition"
                >
                  Create Quiz
                </router-link>
                <router-link
                  :to="`/forums/${course.id}`"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition"
                >
                  Forum
                </router-link>
              </div>
            </div>
            <div v-else-if="authStore.userRole === 'admin'">
              <div class="flex space-x-4">
                <router-link
                  :to="`/forums/${course.id}`"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition"
                >
                  Forum
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <h2 class="text-2xl font-semibold mb-4">Quizzes</h2>
            <div v-if="quizzes.length === 0" class="bg-white rounded-lg shadow p-6">
              <p class="text-gray-600">No quizzes available</p>
            </div>
            <div v-else class="space-y-4">
              <div
                v-for="quiz in quizzes"
                :key="quiz.id"
                class="bg-white rounded-lg shadow p-6"
              >
                <h3 class="text-lg font-semibold mb-2">{{ quiz.title }}</h3>
                <p class="text-gray-600 mb-4">{{ quiz.description }}</p>
                <router-link
                  :to="`/quizzes/${quiz.id}`"
                  class="text-indigo-600 hover:text-indigo-700"
                >
                  Take Quiz →
                </router-link>
              </div>
            </div>
          </div>

          <div>
            <h2 class="text-2xl font-semibold mb-4">Course Information</h2>
            <div class="bg-white rounded-lg shadow p-6">
              <p class="text-gray-600 mb-2">
                <strong>Created:</strong> {{ new Date(course.created_at).toLocaleDateString() }}
              </p>
              <p class="text-gray-600">
                <strong>Instructor Email:</strong> {{ course.instructor_email }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const course = ref(null)
const quizzes = ref([])
const enrollment = ref(null)
const loading = ref(true)

onMounted(async () => {
  await loadCourse()
  await loadQuizzes()
  if (authStore.isAuthenticated && authStore.userRole === 'student') {
    await checkEnrollment()
  }
})

const loadCourse = async () => {
  try {
    const response = await api.get(`/courses/${route.params.id}`)
    course.value = response.data.course
    if (!course.value) {
      console.error('Course not found')
    }
  } catch (error) {
    console.error('Failed to load course:', error)
    console.error('Error details:', error.response?.data || error.message)
  } finally {
    loading.value = false
  }
}

const loadQuizzes = async () => {
  try {
    const response = await api.get(`/quizzes/course/${route.params.id}`)
    quizzes.value = response.data.quizzes || []
    console.log('Quizzes loaded:', quizzes.value.length)
  } catch (error) {
    console.error('Failed to load quizzes:', error)
    quizzes.value = []
  }
}

const checkEnrollment = async () => {
  try {
    const response = await api.get('/enrollments/my-courses')
    const enrollments = response.data.enrollments
    enrollment.value = enrollments.find(e => e.course_id == route.params.id)
  } catch (error) {
    console.error('Failed to check enrollment:', error)
  }
}
</script>

