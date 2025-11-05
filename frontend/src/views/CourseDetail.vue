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
                  :to="`/videos/${course.id}/create`"
                  class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md transition"
                >
                  Add Video
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

        <div class="grid md:grid-cols-2 gap-6 mb-6">
          <!-- Videos Section -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-2xl font-semibold flex items-center">
                <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Videos
                <span class="ml-2 text-sm font-normal text-gray-500">({{ videos.length }})</span>
              </h2>
              <div v-if="authStore.userRole === 'teacher'" class="text-sm text-gray-500">
                <router-link
                  :to="`/videos/${course.id}/create`"
                  class="text-purple-600 hover:text-purple-700 font-medium flex items-center"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Add Video
                </router-link>
              </div>
            </div>

            <div v-if="videos.length === 0" class="bg-white rounded-lg shadow p-6 border border-gray-200">
              <div class="text-center py-8">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-600 font-medium">No videos available</p>
                <p class="text-sm text-gray-500 mt-1">
                  <span v-if="authStore.userRole === 'teacher'">Add your first video to get started</span>
                  <span v-else>No videos have been added to this course yet</span>
                </p>
              </div>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="video in videos"
                :key="video.id"
                class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition p-6"
              >
                <div class="flex items-start justify-between mb-3">
                  <div class="flex items-start space-x-3 flex-1">
                    <div class="flex-shrink-0 mt-1">
                      <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ video.title }}</h3>
                      <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ video.description || 'No description' }}</p>
                      <div class="flex flex-wrap items-center gap-3 text-xs">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          Min: {{ video.min_watch_time_minutes }} min
                        </span>
                        <span v-if="authStore.userRole === 'student' && videoProgress[video.id]" class="inline-flex items-center">
                          <span v-if="videoProgress[video.id].is_completed" class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            ✓ Completed
                          </span>
                          <span v-else class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ Math.round((videoProgress[video.id].watch_time_seconds / (video.min_watch_time_minutes * 60)) * 100) }}% watched
                          </span>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                  <router-link
                    :to="`/videos/${video.id}`"
                    class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md font-medium transition text-sm"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ authStore.userRole === 'student' ? 'Watch Video' : authStore.userRole === 'teacher' ? 'Review & Analytics' : 'View Video' }}
                  </router-link>
                </div>
              </div>
            </div>
          </div>

          <!-- Quizzes Section -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-2xl font-semibold flex items-center">
                <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Quizzes
                <span class="ml-2 text-sm font-normal text-gray-500">({{ quizzes.length }})</span>
              </h2>
              <div v-if="authStore.userRole === 'teacher'" class="text-sm text-gray-500">
                <router-link
                  :to="`/quizzes/${course.id}/create`"
                  class="text-green-600 hover:text-green-700 font-medium flex items-center"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Create Quiz
                </router-link>
              </div>
            </div>

            <div v-if="quizzes.length === 0" class="bg-white rounded-lg shadow p-6 border border-gray-200">
              <div class="text-center py-8">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-600 font-medium">No quizzes available</p>
                <p class="text-sm text-gray-500 mt-1">
                  <span v-if="authStore.userRole === 'teacher'">Create your first quiz to test students</span>
                  <span v-else>No quizzes have been created for this course yet</span>
                </p>
              </div>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="quiz in quizzes"
                :key="quiz.id"
                class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition p-6"
              >
                <div class="flex items-start justify-between mb-3">
                  <div class="flex items-start space-x-3 flex-1">
                    <div class="flex-shrink-0 mt-1">
                      <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ quiz.title }}</h3>
                      <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ quiz.description || 'No description' }}</p>
                      <div class="flex flex-wrap items-center gap-3 text-xs">
                        <span class="text-gray-500">
                          Created {{ new Date(quiz.created_at).toLocaleDateString() }}
                        </span>
                        <span v-if="authStore.userRole === 'student' && quizAttempts[quiz.id]" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          Completed ({{ quizAttempts[quiz.id].percentage }}%)
                        </span>
                        <span v-else-if="authStore.userRole === 'student'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                          Not attempted
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                  <router-link
                    :to="`/quizzes/${quiz.id}`"
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-medium transition text-sm"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ authStore.userRole === 'student' ? 'Take Quiz' : 'Review Quiz' }}
                  </router-link>
                </div>
              </div>
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
const videos = ref([])
const enrollment = ref(null)
const loading = ref(true)
const videoProgress = ref({})
const quizAttempts = ref({})

onMounted(async () => {
  await loadCourse()
  await loadQuizzes()
  await loadVideos()
  if (authStore.isAuthenticated && authStore.userRole === 'student') {
    await checkEnrollment()
    await loadStudentProgress()
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
    
    // Store quiz attempts if available
    if (response.data.attempts) {
      // Handle both object and array formats
      const attempts = response.data.attempts
      if (Array.isArray(attempts)) {
        attempts.forEach(attempt => {
          quizAttempts.value[attempt.quiz_id] = attempt
        })
      } else {
        Object.keys(attempts).forEach(quizId => {
          quizAttempts.value[quizId] = attempts[quizId]
        })
      }
    }
    
    console.log('Quizzes loaded:', quizzes.value.length)
  } catch (error) {
    console.error('Failed to load quizzes:', error)
    quizzes.value = []
  }
}

const loadVideos = async () => {
  try {
    const response = await api.get(`/videos/course/${route.params.id}`)
    videos.value = response.data.videos || []
    console.log('Videos loaded:', videos.value.length)
  } catch (error) {
    console.error('Failed to load videos:', error)
    videos.value = []
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

const loadStudentProgress = async () => {
  if (authStore.userRole !== 'student') return
  
  try {
    // Load video progress
    const videoProgressResponse = await api.get(`/videos/course/${route.params.id}/progress`)
    const progressArray = videoProgressResponse.data.progress || []
    progressArray.forEach(progress => {
      // Use video_content_id as key to match with video.id
      if (progress.video_content_id) {
        videoProgress.value[progress.video_content_id] = progress
      }
    })
    
    // Quiz attempts are already loaded in loadQuizzes()
  } catch (error) {
    console.error('Failed to load student progress:', error)
    // Don't fail silently - log but continue
  }
}
</script>

