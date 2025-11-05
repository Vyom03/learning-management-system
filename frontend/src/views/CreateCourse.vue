<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Create New Course</h1>

      <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
        <form @submit.prevent="handleSubmit">
          <div class="space-y-4">
            <div>
              <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                Course Title *
              </label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                required
                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                placeholder="Enter course title"
              />
            </div>

            <div>
              <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                Description
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="6"
                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                placeholder="Enter course description"
              ></textarea>
            </div>

            <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

            <div class="flex space-x-4">
              <button
                type="submit"
                :disabled="loading"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 uppercase tracking-widest disabled:opacity-50 transition"
              >
                {{ loading ? 'Creating...' : 'Create Course' }}
              </button>
              <router-link
                to="/courses"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 uppercase tracking-widest transition"
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
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import api from '../services/api'

const router = useRouter()

const form = ref({
  title: '',
  description: ''
})
const loading = ref(false)
const error = ref('')

const handleSubmit = async () => {
  error.value = ''
  loading.value = true

  try {
    const response = await api.post('/courses', form.value)
    router.push(`/courses/${response.data.course.id}`)
  } catch (err) {
    error.value = err.response?.data?.error || 'Failed to create course'
  } finally {
    loading.value = false
  }
}
</script>

