<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="loading" class="text-center py-12">
        <p class="text-gray-600">Loading quiz...</p>
      </div>

      <div v-else-if="error" class="text-center py-12">
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
          <p class="text-red-600 mb-4">{{ error }}</p>
          <router-link to="/courses" class="text-indigo-600 hover:text-indigo-700">
            Back to Courses →
          </router-link>
        </div>
      </div>

      <div v-else-if="quiz">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <h1 class="text-3xl font-bold mb-2">{{ quiz.title }}</h1>
          <p class="text-gray-600 mb-6">{{ quiz.description }}</p>
        </div>

        <div v-if="!submitted" class="bg-white rounded-lg shadow-md p-6">
          <form @submit.prevent="handleSubmit">
            <div v-for="(question, index) in questions" :key="question.id" class="mb-8">
              <h3 class="text-lg font-semibold mb-4">
                {{ index + 1 }}. {{ question.question }} ({{ question.points }} points)
              </h3>
              <div class="space-y-2">
                <label
                  v-for="(option, optIndex) in (typeof question.options === 'string' ? JSON.parse(question.options) : question.options)"
                  :key="optIndex"
                  class="flex items-center p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer"
                >
                  <input
                    type="radio"
                    :name="`question-${question.id}`"
                    :value="optIndex"
                    v-model="answers[index]"
                    class="mr-3"
                  />
                  <span>{{ option }}</span>
                </label>
              </div>
            </div>

            <div v-if="error && !submitted" class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
              <p class="text-red-600">{{ error }}</p>
            </div>

            <button
              type="submit"
              :disabled="submitting || answers.length !== questions.length"
              class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-md disabled:opacity-50"
            >
              {{ submitting ? 'Submitting...' : 'Submit Quiz' }}
            </button>
          </form>
        </div>

        <div v-else class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-2xl font-bold mb-4">Quiz Results</h2>
          <div class="text-center py-8">
            <p class="text-3xl font-bold mb-2">{{ result.score }} / {{ result.totalPoints }}</p>
            <p class="text-xl text-gray-600 mb-6">{{ result.percentage }}%</p>
            <router-link
              to="/courses"
              class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-md"
            >
              Back to Courses
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import api from '../services/api'

const route = useRoute()

const quiz = ref(null)
const questions = ref([])
const answers = ref([])
const loading = ref(true)
const submitting = ref(false)
const submitted = ref(false)
const result = ref(null)
const error = ref('')

onMounted(async () => {
  await loadQuiz()
})

const loadQuiz = async () => {
  try {
    console.log('Loading quiz:', route.params.id)
    const response = await api.get(`/quizzes/${route.params.id}`)
    console.log('Quiz response:', response.data)
    
    if (!response.data.quiz) {
      error.value = 'Quiz not found'
      return
    }
    
    quiz.value = response.data.quiz
    questions.value = response.data.questions || []
    answers.value = new Array(questions.value.length).fill(null)
    
    console.log('Quiz loaded:', quiz.value.title, 'Questions:', questions.value.length)
  } catch (err) {
    console.error('Failed to load quiz:', err)
    console.error('Error details:', err.response?.data || err.message)
    error.value = err.response?.data?.error || 'Failed to load quiz. Please try again.'
  } finally {
    loading.value = false
  }
}

const handleSubmit = async () => {
  error.value = ''
  
  if (answers.value.some(a => a === null || a === undefined)) {
    error.value = 'Please answer all questions'
    return
  }

  submitting.value = true

  try {
    console.log('Submitting quiz:', route.params.id, 'Answers:', answers.value)
    const response = await api.post(`/quizzes/${route.params.id}/submit`, {
      answers: answers.value
    })
    console.log('Quiz submission response:', response.data)
    result.value = response.data.result
    submitted.value = true
  } catch (err) {
    console.error('Failed to submit quiz:', err)
    console.error('Error details:', err.response?.data || err.message)
    error.value = err.response?.data?.error || err.response?.data?.details || 'Failed to submit quiz. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>

