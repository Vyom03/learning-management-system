<template>
  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <!-- Logo Container with Background -->
    <div class="mb-8">
      <div class="flex flex-col items-center">
        <div class="bg-white p-6 rounded-2xl shadow-lg">
          <ApplicationLogo class="w-20 h-20 fill-current text-indigo-600" />
        </div>
        <h1 class="mt-4 text-2xl font-bold text-gray-800">Learning Management System</h1>
        <p class="text-sm text-gray-600">Welcome Back</p>
      </div>
    </div>

    <!-- Login Card -->
    <div class="w-full sm:max-w-md">
      <div class="bg-white shadow-2xl rounded-2xl overflow-hidden border border-gray-100">
        <div class="px-8 py-8">
          <!-- Page Title -->
          <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-gray-900">Sign In</h2>
            <p class="mt-2 text-sm text-gray-600">Enter your credentials to access your account</p>
          </div>

          <form class="space-y-6" @submit.prevent="handleLogin">
            <!-- Email Address -->
            <div>
              <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                  </svg>
                </div>
                <input
                  id="email"
                  v-model="email"
                  name="email"
                  type="email"
                  required
                  autofocus
                  autocomplete="username"
                  class="block w-full pl-10 pr-3 py-3 border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 rounded-lg transition"
                  placeholder="student@school.edu"
                />
              </div>
            </div>

            <!-- Password -->
            <div>
              <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <input
                  id="password"
                  v-model="password"
                  name="password"
                  type="password"
                  required
                  autocomplete="current-password"
                  class="block w-full pl-10 pr-3 py-3 border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 rounded-lg transition"
                  placeholder="••••••••"
                />
              </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
              <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input
                  id="remember_me"
                  type="checkbox"
                  class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer"
                  name="remember"
                />
                <span class="ml-2 text-sm text-gray-600 hover:text-gray-900">Remember me</span>
              </label>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="text-red-600 text-sm text-center">{{ error }}</div>

            <!-- Login Button -->
            <div>
              <button
                type="submit"
                :disabled="loading"
                class="w-full flex justify-center py-3 px-4 border border-transparent text-base font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:ring-indigo-500 focus:ring-2 focus:ring-offset-2 disabled:opacity-50 transition"
              >
                {{ loading ? 'Signing in...' : 'Sign In' }}
              </button>
            </div>

            <!-- Info Message -->
            <div class="text-center mt-6">
              <p class="text-sm text-gray-600">
                Only students added from the Student Management System can access the LMS.
              </p>
            </div>
          </form>
        </div>
        
        <!-- Footer -->
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100">
          <p class="text-xs text-center text-gray-600">
            &copy; {{ new Date().getFullYear() }} Learning Management System. All rights reserved.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import ApplicationLogo from '../components/ApplicationLogo.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
  error.value = ''
  loading.value = true
  
  const result = await authStore.login(email.value, password.value)
  
  if (result.success) {
    const redirect = route.query.redirect || '/'
    router.push(redirect)
  } else {
    error.value = result.error
  }
  
  loading.value = false
}
</script>

