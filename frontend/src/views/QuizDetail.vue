<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
        <p class="text-gray-600 font-medium">Loading quiz...</p>
        <p class="text-sm text-gray-500 mt-2">Please wait while we load the quiz questions</p>
      </div>

      <div v-else-if="error" class="text-center py-12">
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
          <div class="text-red-600 mb-4">
            <p class="font-semibold mb-2">Error loading quiz:</p>
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

      <div v-else-if="quiz">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold mb-2">{{ quiz.title }}</h1>
              <p class="text-gray-600 mb-6">{{ quiz.description }}</p>
            </div>
            <div v-if="isTeacherOrAdmin" class="bg-blue-100 text-blue-800 px-4 py-2 rounded-md">
              <span class="text-sm font-semibold">Review Mode</span>
            </div>
          </div>
        </div>

        <!-- Student View: Quiz Taking Form -->
        <div v-if="isStudent && !submitted" class="bg-white rounded-lg shadow-md p-6">
          <!-- Quiz Instructions -->
          <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-blue-700">
                  <strong>Instructions:</strong> Answer all questions before submitting. Once submitted, you cannot change your answers.
                </p>
              </div>
            </div>
          </div>

          <!-- Progress Indicator -->
          <div class="mb-6">
            <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
              <span>Questions answered: <strong class="text-gray-900">{{ answers.filter(a => a !== null && a !== undefined).length }}</strong> / {{ questions.length }}</span>
              <span class="text-indigo-600 font-semibold">{{ Math.round((answers.filter(a => a !== null && a !== undefined).length / questions.length) * 100) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                :style="{ width: (answers.filter(a => a !== null && a !== undefined).length / questions.length) * 100 + '%' }"
              ></div>
            </div>
          </div>

          <form @submit.prevent="handleSubmit">
            <div v-for="(question, index) in questions" :key="question.id" class="mb-8 pb-6 border-b border-gray-200 last:border-0">
              <div class="flex items-start justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex-1">
                  <span class="inline-flex items-center justify-center w-8 h-8 bg-indigo-100 text-indigo-700 rounded-full text-sm font-bold mr-3">
                    {{ index + 1 }}
                  </span>
                  {{ question.question }}
                </h3>
                <span class="ml-3 text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-md whitespace-nowrap">
                  {{ question.points }} point{{ question.points !== 1 ? 's' : '' }}
                </span>
              </div>
              <div class="space-y-3 ml-11">
                <label
                  v-for="(option, optIndex) in (typeof question.options === 'string' ? JSON.parse(question.options) : question.options)"
                  :key="optIndex"
                  class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                  :class="answers[index] === optIndex 
                    ? 'border-indigo-500 bg-indigo-50' 
                    : 'border-gray-300 hover:border-indigo-300 hover:bg-gray-50'"
                >
                  <input
                    type="radio"
                    :name="`question-${question.id}`"
                    :value="optIndex"
                    v-model="answers[index]"
                    class="mr-3 w-4 h-4 text-indigo-600 focus:ring-indigo-500"
                  />
                  <span class="text-gray-700 flex-1">{{ option }}</span>
                  <svg v-if="answers[index] === optIndex" class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                  </svg>
                </label>
              </div>
            </div>

            <div v-if="error && !submitted" class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm text-red-700 font-medium">{{ error }}</p>
                </div>
              </div>
            </div>

            <div class="mt-8 pt-6 border-t-2 border-gray-200">
              <button
                type="submit"
                :disabled="submitting || answers.filter(a => a !== null && a !== undefined).length !== questions.length"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-4 rounded-lg font-semibold text-lg disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center"
              >
                <svg v-if="!submitting" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ submitting ? 'Submitting Quiz...' : answers.filter(a => a !== null && a !== undefined).length === questions.length ? 'Submit Quiz' : `Answer ${questions.length - answers.filter(a => a !== null && a !== undefined).length} more question${questions.length - answers.filter(a => a !== null && a !== undefined).length !== 1 ? 's' : ''}` }}
              </button>
              <p v-if="answers.filter(a => a !== null && a !== undefined).length !== questions.length" class="text-center text-sm text-gray-500 mt-3">
                Please answer all {{ questions.length }} questions to submit
              </p>
            </div>
          </form>
        </div>

        <!-- Student View: Quiz Results -->
        <div v-else-if="isStudent && submitted" class="bg-white rounded-lg shadow-md p-6">
          <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4"
                 :class="result.percentage >= 70 ? 'bg-green-100' : result.percentage >= 50 ? 'bg-yellow-100' : 'bg-red-100'">
              <svg class="w-10 h-10"
                   :class="result.percentage >= 70 ? 'text-green-600' : result.percentage >= 50 ? 'text-yellow-600' : 'text-red-600'"
                   fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="result.percentage >= 70" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h2 class="text-3xl font-bold mb-2">Quiz Completed!</h2>
            <p class="text-gray-600">Your results are below</p>
          </div>

          <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-8 mb-6">
            <div class="grid grid-cols-3 gap-4 text-center">
              <div>
                <p class="text-sm text-gray-600 mb-1">Score</p>
                <p class="text-3xl font-bold text-indigo-600">{{ result.score }} / {{ result.totalPoints }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Percentage</p>
                <p class="text-3xl font-bold"
                   :class="result.percentage >= 70 ? 'text-green-600' : result.percentage >= 50 ? 'text-yellow-600' : 'text-red-600'">
                  {{ result.percentage }}%
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Status</p>
                <p class="text-lg font-semibold"
                   :class="result.percentage >= 70 ? 'text-green-600' : result.percentage >= 50 ? 'text-yellow-600' : 'text-red-600'">
                  {{ result.percentage >= 70 ? 'Excellent!' : result.percentage >= 50 ? 'Good' : 'Needs Improvement' }}
                </p>
              </div>
            </div>
          </div>

          <div class="flex justify-center space-x-4">
            <router-link
              v-if="courseId"
              :to="`/courses/${courseId}`"
              class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-md font-medium transition inline-flex items-center"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Back to Course
            </router-link>
            <router-link
              to="/courses"
              class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-md font-medium transition inline-flex items-center"
            >
              All Courses
            </router-link>
          </div>
        </div>

        <!-- Teacher/Admin View: Review Mode (Read-only with correct answers) -->
        <div v-else-if="isTeacherOrAdmin" class="bg-white rounded-lg shadow-md p-6">
          <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-400 rounded-md">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-semibold text-blue-900 mb-1">Review Mode</h3>
                <p class="text-sm text-blue-800">
                  You are viewing this quiz in review mode. You can see all questions and correct answers (highlighted in green), but cannot submit the quiz.
                </p>
              </div>
            </div>
          </div>
          
          <div class="mb-4 flex items-center justify-between text-sm text-gray-600">
            <span class="font-medium">Total Questions: <span class="text-gray-900">{{ questions.length }}</span></span>
            <span class="font-medium">Total Points: <span class="text-gray-900">{{ questions.reduce((sum, q) => sum + (q.points || 0), 0) }}</span></span>
          </div>
          
          <div v-for="(question, index) in questions" :key="question.id" class="mb-8 pb-8 border-b-2 border-gray-200 last:border-0">
            <div class="flex items-start justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-900 flex-1">
                <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-700 rounded-full text-sm font-bold mr-3">
                  {{ index + 1 }}
                </span>
                {{ question.question }}
              </h3>
              <span class="ml-3 text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-md whitespace-nowrap">
                {{ question.points }} point{{ question.points !== 1 ? 's' : '' }}
              </span>
            </div>
            <div class="space-y-2">
              <div
                v-for="(option, optIndex) in (typeof question.options === 'string' ? JSON.parse(question.options) : question.options)"
                :key="optIndex"
                :class="[
                  'flex items-center p-3 border rounded-md',
                  optIndex === question.correct_answer
                    ? 'bg-green-50 border-green-300 border-2'
                    : 'bg-gray-50 border-gray-200'
                ]"
              >
                <div class="mr-3 flex-shrink-0">
                  <div
                    v-if="optIndex === question.correct_answer"
                    class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center"
                  >
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div
                    v-else
                    class="w-6 h-6 rounded-full border-2 border-gray-300"
                  ></div>
                </div>
                <span :class="optIndex === question.correct_answer ? 'font-semibold text-green-800' : 'text-gray-700'">
                  {{ option }}
                  <span v-if="optIndex === question.correct_answer" class="ml-2 text-sm text-green-600">(Correct Answer)</span>
                </span>
              </div>
            </div>
          </div>

          <div class="mt-6 pt-6 border-t-2 border-gray-200">
            <div class="flex justify-center space-x-4">
              <router-link
                v-if="courseId"
                :to="`/courses/${courseId}`"
                class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-md font-medium transition"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Course
              </router-link>
              <router-link
                to="/courses"
                class="inline-flex items-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-md font-medium transition"
              >
                All Courses
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'

const route = useRoute()
const authStore = useAuthStore()

const quiz = ref(null)
const questions = ref([])
const answers = ref([])
const loading = ref(true)
const submitting = ref(false)
const submitted = ref(false)
const result = ref(null)
const error = ref('')
const courseId = ref(null)

const isStudent = computed(() => authStore.userRole === 'student')
const isTeacherOrAdmin = computed(() => authStore.userRole === 'teacher' || authStore.userRole === 'admin')

onMounted(async () => {
  if (authStore.isAuthenticated && !authStore.user) {
    await authStore.fetchUser()
  }
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
    courseId.value = quiz.value?.course_id || null
    
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

