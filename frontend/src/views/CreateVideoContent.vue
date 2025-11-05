<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Add Video Content</h1>

      <div class="bg-white rounded-lg shadow-md p-6">
        <form @submit.prevent="handleSubmit">
          <div class="space-y-6">
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                Video Title *
              </label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="e.g., Introduction to JavaScript"
              />
              <p class="mt-1 text-xs text-gray-500">
                Title will be automatically formatted to Title Case (e.g., "introduction to javascript" → "Introduction To Javascript")
              </p>
            </div>

            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Description
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Brief description of the video content"
              ></textarea>
              <p class="mt-1 text-xs text-gray-500">
                Description will be automatically formatted to Title Case
              </p>
            </div>

            <div>
              <label for="youtube_url" class="block text-sm font-medium text-gray-700 mb-2">
                YouTube URL *
              </label>
              <input
                id="youtube_url"
                v-model="form.youtube_url"
                type="url"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="https://www.youtube.com/watch?v=... or https://youtu.be/..."
              />
              <p class="mt-1 text-sm text-gray-500">
                Paste the full YouTube URL. The video will be embedded automatically.
              </p>
            </div>

            <div>
              <label for="min_watch_time_minutes" class="block text-sm font-medium text-gray-700 mb-2">
                Minimum Watch Time (minutes) *
              </label>
              <input
                id="min_watch_time_minutes"
                v-model.number="form.min_watch_time_minutes"
                type="number"
                min="1"
                max="60"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
              <p class="mt-1 text-sm text-gray-500">
                Students must watch at least this many minutes to mark the video as completed (default: 2 minutes)
              </p>
            </div>

            <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
              <p class="text-red-600">{{ error }}</p>
            </div>

            <div class="flex space-x-4">
              <button
                type="submit"
                :disabled="submitting"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md disabled:opacity-50"
              >
                {{ submitting ? 'Creating...' : 'Create Video Content' }}
              </button>
              <router-link
                :to="`/courses/${courseId}`"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-md"
              >
                Cancel
              </router-link>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import api from '../services/api'

const route = useRoute()
const router = useRouter()

const courseId = ref(route.params.courseId)
const form = ref({
  title: '',
  description: '',
  youtube_url: '',
  min_watch_time_minutes: 2
})
const submitting = ref(false)
const error = ref('')

const handleSubmit = async () => {
  error.value = ''
  submitting.value = true

  try {
    const response = await api.post('/videos', {
      course_id: parseInt(courseId.value),
      title: form.value.title,
      description: form.value.description,
      youtube_url: form.value.youtube_url,
      min_watch_time_minutes: form.value.min_watch_time_minutes
    })

    // Redirect to course detail page
    router.push(`/courses/${courseId.value}`)
  } catch (err) {
    console.error('Failed to create video content:', err)
    error.value = err.response?.data?.error || err.response?.data?.details || 'Failed to create video content. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>

