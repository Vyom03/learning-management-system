<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Loading video...</p>
        <p class="text-sm text-gray-500 mt-2">If this takes too long, check the browser console for errors.</p>
      </div>

      <div v-else-if="error" class="text-center py-12">
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
          <div class="text-red-600 mb-4">
            <p class="font-semibold mb-2">Error loading video:</p>
            <p>{{ error }}</p>
          </div>
          <div class="mt-4 space-x-4">
            <router-link 
              v-if="courseId" 
              :to="`/courses/${courseId}`" 
              class="text-indigo-600 hover:text-indigo-700 inline-block"
            >
              ← Back to Course
            </router-link>
            <router-link 
              to="/courses" 
              class="text-indigo-600 hover:text-indigo-700 inline-block"
            >
              ← Back to Courses
            </router-link>
          </div>
        </div>
      </div>

      <div v-else-if="video">
        <!-- Video Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h1 class="text-3xl font-bold mb-2">{{ video.title }}</h1>
              <p class="text-gray-600">{{ video.description }}</p>
            </div>
            <div v-if="authStore.userRole === 'student' && watchProgress" class="text-right">
              <div v-if="watchProgress.is_completed" class="bg-green-100 text-green-800 px-4 py-2 rounded-md">
                <span class="text-sm font-semibold">✓ Completed</span>
              </div>
              <div v-else class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-md">
                <span class="text-sm font-semibold">In Progress</span>
              </div>
            </div>
            <div v-else-if="authStore.userRole === 'teacher' || authStore.userRole === 'admin'" class="bg-blue-100 text-blue-800 px-4 py-2 rounded-md">
              <span class="text-sm font-semibold">Review Mode</span>
            </div>
          </div>
        </div>

        <!-- Student View: Video Player with Timer -->
        <div v-if="authStore.userRole === 'student'" class="bg-white rounded-lg shadow-md p-6">
          <div class="mb-6">
            <div class="aspect-video bg-black rounded-lg overflow-hidden mb-4">
              <iframe
                :id="'youtube-player-' + video.id"
                :src="youtubeEmbedUrl"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                class="w-full h-full"
                @load="onVideoLoad"
              ></iframe>
            </div>

            <!-- Watch Progress Info -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg p-5 mb-4">
              <div class="flex items-center justify-between mb-3">
                <div class="flex-1">
                  <div class="flex items-center space-x-3 mb-2">
                    <div class="flex items-center">
                      <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span class="text-sm font-semibold text-blue-900">Watch Progress</span>
                    </div>
                    <span v-if="isVideoPlaying" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 animate-pulse">
                      <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                      Playing
                    </span>
                    <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                      Paused
                    </span>
                  </div>
                  <p class="text-sm text-blue-800 mb-1">
                    <strong>{{ formatTime(currentWatchTime) }}</strong> / <strong>{{ video.min_watch_time_minutes }} minutes</strong> required
                  </p>
                  <p class="text-xs text-blue-600">
                    <span v-if="watchProgress?.is_completed" class="font-semibold text-green-700">
                      ✓ Video completed! You've met the minimum watch time requirement.
                    </span>
                    <span v-else>
                      Click play to start watching. Timer will track your viewing time automatically.
                    </span>
                  </p>
                </div>
                <div class="text-right ml-4">
                  <div class="text-3xl font-bold text-blue-600 mb-1">{{ formatTime(currentWatchTime) }}</div>
                  <div class="text-xs text-blue-500 font-medium">Watched</div>
                </div>
              </div>
              <div class="mt-4">
                <div class="flex items-center justify-between text-xs text-blue-700 mb-2">
                  <span>Progress</span>
                  <span class="font-semibold">{{ watchProgressPercentage }}%</span>
                </div>
                <div class="w-full bg-blue-200 rounded-full h-3 overflow-hidden">
                  <div
                    class="h-3 rounded-full transition-all duration-500 ease-out"
                    :class="watchProgress?.is_completed ? 'bg-green-500' : 'bg-blue-600'"
                    :style="{ width: watchProgressPercentage + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex space-x-4">
            <router-link
              :to="`/courses/${courseId}`"
              class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-md"
            >
              Back to Course
            </router-link>
          </div>
        </div>

        <!-- Teacher/Admin View: Video Player (Review Mode) + Analytics -->
        <div v-else>
          <!-- Video Player -->
          <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="mb-6">
              <div class="aspect-video bg-black rounded-lg overflow-hidden mb-4">
                <iframe
                  :src="youtubeEmbedUrl"
                  frameborder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowfullscreen
                  class="w-full h-full"
                ></iframe>
              </div>
              <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <p class="text-blue-800 text-sm">
                  <strong>Review Mode:</strong> You are viewing this video in review mode. Minimum watch time: {{ video.min_watch_time_minutes }} minutes.
                </p>
              </div>
            </div>

            <div class="flex space-x-4">
              <router-link
                :to="`/courses/${courseId}`"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-md"
              >
                Back to Course
              </router-link>
            </div>
          </div>

          <!-- Analytics Section -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-2xl font-bold text-gray-900">Student Analytics</h2>
              <button
                @click="loadAnalytics"
                :disabled="loadingAnalytics"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md disabled:opacity-50"
              >
                {{ loadingAnalytics ? 'Loading...' : 'Refresh' }}
              </button>
            </div>

            <div v-if="loadingAnalytics" class="text-center py-8">
              <p class="text-gray-600">Loading analytics...</p>
            </div>

            <div v-else-if="analyticsError" class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
              <p class="text-red-600">{{ analyticsError }}</p>
            </div>

            <div v-else-if="analytics">
              <!-- Statistics Cards -->
              <div class="grid md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                  <p class="text-sm text-blue-600 font-medium mb-1">Total Students</p>
                  <p class="text-2xl font-bold text-blue-900">{{ analytics.statistics.total_students_watched }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                  <p class="text-sm text-green-600 font-medium mb-1">Completed</p>
                  <p class="text-2xl font-bold text-green-900">{{ analytics.statistics.completed_students }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                  <p class="text-sm text-purple-600 font-medium mb-1">Completion Rate</p>
                  <p class="text-2xl font-bold text-purple-900">{{ analytics.statistics.completion_rate }}%</p>
                </div>
                <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                  <p class="text-sm text-orange-600 font-medium mb-1">Avg Watch Time</p>
                  <p class="text-2xl font-bold text-orange-900">{{ formatTime(analytics.statistics.average_watch_time_seconds) }}</p>
                </div>
              </div>

              <!-- Student Progress Table -->
              <div v-if="analytics.watch_progress && analytics.watch_progress.length > 0">
                <h3 class="text-lg font-semibold mb-4">Student Watch Progress</h3>
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Watch Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Watched</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="progress in analytics.watch_progress" :key="progress.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                          {{ progress.student_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {{ progress.student_email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                          {{ formatTime(progress.watch_time_seconds) }}
                          <span class="text-gray-500 text-xs ml-2">
                            ({{ Math.round((progress.watch_time_seconds / (video.min_watch_time_minutes * 60)) * 100) }}%)
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span
                            v-if="progress.is_completed"
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                          >
                            Completed
                          </span>
                          <span
                            v-else
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"
                          >
                            In Progress
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {{ progress.last_watched_at ? new Date(progress.last_watched_at).toLocaleString() : 'Never' }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div v-else class="text-center py-8 bg-gray-50 rounded-lg">
                <p class="text-gray-600">No students have watched this video yet.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'

const route = useRoute()
const authStore = useAuthStore()

const video = ref(null)
const watchProgress = ref(null)
const courseId = ref(null)
const loading = ref(true)
const error = ref('')
const currentWatchTime = ref(0)
const watchInterval = ref(null)
const progressUpdateInterval = ref(null)
const analytics = ref(null)
const loadingAnalytics = ref(false)
const analyticsError = ref('')

const watchProgressPercentage = computed(() => {
  if (!video.value || !video.value.min_watch_time_minutes) return 0
  const requiredSeconds = video.value.min_watch_time_minutes * 60
  if (requiredSeconds === 0) return 0
  return Math.min(100, Math.round((currentWatchTime.value / requiredSeconds) * 100))
})

const youtubeEmbedUrl = computed(() => {
  if (!video.value || !video.value.youtube_id) return ''
  const origin = typeof window !== 'undefined' && window.location ? window.location.origin : ''
  // Always enable JS API for student role to track play/pause
  if (authStore.userRole === 'student') {
    return `https://www.youtube.com/embed/${video.value.youtube_id}?enablejsapi=1${origin ? `&origin=${origin}` : ''}`
  }
  return `https://www.youtube.com/embed/${video.value.youtube_id}`
})

// Load YouTube IFrame API
const loadYouTubeAPI = () => {
  if (typeof window === 'undefined') return
  
  if (window.YT && window.YT.Player) {
    // API already loaded
    console.log('YouTube API already loaded')
    return
  }
  
  // Check if script is already being loaded
  if (document.querySelector('script[src*="youtube.com/iframe_api"]')) {
    console.log('YouTube API script already loading')
    return
  }
  
  const tag = document.createElement('script')
  tag.src = 'https://www.youtube.com/iframe_api'
  const firstScriptTag = document.getElementsByTagName('script')[0]
  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag)
  
  // Set up callback for when API is ready
  window.onYouTubeIframeAPIReady = () => {
    console.log('YouTube IFrame API ready')
    // Player will be initialized when iframe loads (onVideoLoad)
  }
}

onMounted(async () => {
  console.log('VideoDetail mounted, route params:', route.params)
  console.log('Video ID:', route.params.id)
  console.log('Auth status:', authStore.isAuthenticated, 'User:', authStore.user)
  
  if (authStore.isAuthenticated && !authStore.user) {
    console.log('Fetching user...')
    await authStore.fetchUser()
    console.log('User fetched:', authStore.user)
  }
  
  // Load YouTube API if student
  if (authStore.userRole === 'student') {
    loadYouTubeAPI()
  }
  
  await loadVideo()
})

onUnmounted(() => {
  stopWatchTracking()
  if (progressUpdateInterval.value) {
    clearInterval(progressUpdateInterval.value)
  }
  // Remove visibility change listener
  document.removeEventListener('visibilitychange', handleVisibilityChange)
  // Save progress before leaving
  if (authStore.userRole === 'student' && currentWatchTime.value > 0) {
    saveWatchProgress()
  }
  // Destroy YouTube player if it exists
  if (youtubePlayer && typeof youtubePlayer.destroy === 'function') {
    youtubePlayer.destroy()
  }
})

const loadVideo = async () => {
  loading.value = true
  error.value = ''
  
  try {
    console.log('Loading video with ID:', route.params.id)
    const response = await api.get(`/videos/${route.params.id}`)
    console.log('Video API response:', response.data)
    
    if (!response.data || !response.data.video) {
      error.value = 'Video not found'
      loading.value = false
      return
    }
    
    video.value = response.data.video
    watchProgress.value = response.data.watch_progress || null
    courseId.value = video.value?.course_id || null
    
    if (watchProgress.value) {
      currentWatchTime.value = watchProgress.value.watch_time_seconds || 0
    }

    // Don't start tracking immediately - wait for video to play
    // Tracking will start when YouTube player fires onStateChange event

    // Load analytics if teacher/admin
    if (authStore.userRole === 'teacher' || authStore.userRole === 'admin') {
      await loadAnalytics()
    }
  } catch (err) {
    console.error('Failed to load video:', err)
    console.error('Error response:', err.response)
    error.value = err.response?.data?.error || err.response?.data?.details || err.message || 'Failed to load video. Please try again.'
  } finally {
    loading.value = false
  }
}

const loadAnalytics = async () => {
  if (!video.value || !video.value.id) return
  
  loadingAnalytics.value = true
  analyticsError.value = ''
  
  try {
    const response = await api.get(`/videos/${video.value.id}/analytics`)
    analytics.value = response.data
  } catch (err) {
    console.error('Failed to load analytics:', err)
    analyticsError.value = err.response?.data?.error || 'Failed to load analytics. Please try again.'
  } finally {
    loadingAnalytics.value = false
  }
}

let youtubePlayer = null
const isVideoPlaying = ref(false)

const onVideoLoad = () => {
  // Video iframe loaded, initialize YouTube player
  if (authStore.userRole === 'student') {
    // Wait a bit for iframe to be ready, then initialize player
    setTimeout(() => {
      if (typeof window !== 'undefined' && window.YT && window.YT.Player) {
        initializeYouTubePlayer()
      } else if (typeof window !== 'undefined') {
        // If API not loaded yet, wait for it
        const checkAPI = setInterval(() => {
          if (window.YT && window.YT.Player) {
            clearInterval(checkAPI)
            initializeYouTubePlayer()
          }
        }, 100)
        // Stop checking after 5 seconds
        setTimeout(() => clearInterval(checkAPI), 5000)
      }
    }, 500)
  }
}

const initializeYouTubePlayer = () => {
  if (!video.value || !video.value.youtube_id) return
  
  try {
    const playerElement = document.getElementById(`youtube-player-${video.value.id}`)
    if (!playerElement) {
      console.error('Player element not found')
      return
    }
    
    youtubePlayer = new window.YT.Player(`youtube-player-${video.value.id}`, {
      events: {
        onStateChange: onPlayerStateChange,
        onReady: onPlayerReady
      }
    })
    console.log('YouTube player initialized')
  } catch (error) {
    console.error('Error initializing YouTube player:', error)
  }
}

const onPlayerReady = () => {
  console.log('YouTube player ready')
}

const onPlayerStateChange = (event) => {
  // YouTube player states:
  // -1 (UNSTARTED), 0 (ENDED), 1 (PLAYING), 2 (PAUSED), 3 (BUFFERING), 5 (CUED)
  const PLAYING = 1
  const PAUSED = 2
  const ENDED = 0
  
  if (event.data === PLAYING) {
    // Video started playing
    isVideoPlaying.value = true
    startWatchTracking()
  } else if (event.data === PAUSED || event.data === ENDED) {
    // Video paused or ended
    isVideoPlaying.value = false
    stopWatchTracking()
    // Save progress when paused/ended
    if (authStore.userRole === 'student' && currentWatchTime.value > 0) {
      saveWatchProgress()
    }
  }
}

const startWatchTracking = () => {
  // Only start if not already running
  if (watchInterval.value) {
    return
  }

  watchInterval.value = setInterval(() => {
    if (authStore.userRole === 'student' && isVideoPlaying.value && document.visibilityState === 'visible') {
      // Only increment if video is playing and page is visible
      currentWatchTime.value += 1
    }
  }, 1000)

  // Save progress every 30 seconds
  if (progressUpdateInterval.value) {
    clearInterval(progressUpdateInterval.value)
  }

  progressUpdateInterval.value = setInterval(() => {
    if (authStore.userRole === 'student' && currentWatchTime.value > 0 && isVideoPlaying.value) {
      saveWatchProgress()
    }
  }, 30000) // Save every 30 seconds

  // Track page visibility changes
  document.addEventListener('visibilitychange', handleVisibilityChange)
}

const stopWatchTracking = () => {
  if (watchInterval.value) {
    clearInterval(watchInterval.value)
    watchInterval.value = null
  }
}

const handleVisibilityChange = () => {
  // Pause tracking when page is hidden
  if (document.visibilityState === 'hidden') {
    // Save progress when user switches tabs
    if (authStore.userRole === 'student' && currentWatchTime.value > 0) {
      saveWatchProgress()
    }
  }
}

const saveWatchProgress = async () => {
  try {
    const response = await api.post(`/videos/${route.params.id}/watch-progress`, {
      watch_time_seconds: currentWatchTime.value
    })
    
    if (response.data.watch_progress) {
      watchProgress.value = response.data.watch_progress
    }
  } catch (err) {
    console.error('Failed to save watch progress:', err)
  }
}

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

</script>

