<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Create Quiz</h1>

      <div class="bg-white rounded-lg shadow-md p-6">
        <form @submit.prevent="handleSubmit">
          <div class="space-y-6">
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                Quiz Title *
              </label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
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
              ></textarea>
            </div>

            <div>
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Questions</h3>
                <button
                  type="button"
                  @click="addQuestion"
                  class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm"
                >
                  Add Question
                </button>
              </div>

              <div v-for="(question, index) in form.questions" :key="index" class="border border-gray-200 rounded-lg p-4 mb-4">
                <div class="flex justify-between items-center mb-2">
                  <span class="font-medium">Question {{ index + 1 }}</span>
                  <button
                    type="button"
                    @click="removeQuestion(index)"
                    class="text-red-600 hover:text-red-700 text-sm"
                  >
                    Remove
                  </button>
                </div>

                <div class="space-y-3">
                  <input
                    v-model="question.question"
                    type="text"
                    required
                    placeholder="Question text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md"
                  />

                  <div class="space-y-2">
                    <label class="block text-sm font-medium">Options</label>
                    <input
                      v-for="(option, optIndex) in question.options"
                      :key="optIndex"
                      v-model="question.options[optIndex]"
                      type="text"
                      required
                      :placeholder="`Option ${optIndex + 1}`"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium mb-2">Correct Answer (0-3)</label>
                    <input
                      v-model.number="question.correct_answer"
                      type="number"
                      min="0"
                      max="3"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium mb-2">Points</label>
                    <input
                      v-model.number="question.points"
                      type="number"
                      min="1"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    />
                  </div>
                </div>
              </div>
            </div>

            <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

            <div class="flex space-x-4">
              <button
                type="submit"
                :disabled="loading || form.questions.length === 0"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md disabled:opacity-50"
              >
                {{ loading ? 'Creating...' : 'Create Quiz' }}
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

const courseId = route.params.courseId
const form = ref({
  title: '',
  description: '',
  questions: []
})
const loading = ref(false)
const error = ref('')

const addQuestion = () => {
  form.value.questions.push({
    question: '',
    options: ['', '', '', ''],
    correct_answer: 0,
    points: 1
  })
}

const removeQuestion = (index) => {
  form.value.questions.splice(index, 1)
}

const handleSubmit = async () => {
  error.value = ''
  
  if (form.value.questions.length === 0) {
    error.value = 'Please add at least one question'
    return
  }

  loading.value = true

  try {
    const response = await api.post('/quizzes', {
      course_id: parseInt(courseId),
      title: form.value.title,
      description: form.value.description,
      questions: form.value.questions
    })
    router.push(`/courses/${courseId}`)
  } catch (err) {
    console.error('Failed to create quiz:', err)
    const errorMsg = err.response?.data?.error || err.response?.data?.message || 'Failed to create quiz'
    const details = err.response?.data?.details
    error.value = details ? `${errorMsg}: ${JSON.stringify(details)}` : errorMsg
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (form.value.questions.length === 0) {
    addQuestion()
  }
})
</script>

