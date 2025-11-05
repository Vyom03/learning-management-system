<template>
  <nav class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex">
          <!-- Logo -->
          <div class="shrink-0 flex items-center">
            <router-link to="/" class="flex items-center">
              <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
              <span class="ml-2 text-lg font-semibold text-gray-800">LMS</span>
            </router-link>
          </div>

          <!-- Navigation Links -->
          <div class="hidden space-x-8 sm:flex sm:ml-10">
            <router-link
              v-if="authStore.isAuthenticated"
              to="/"
              :class="[
                $route.path === '/' 
                  ? 'border-indigo-500 text-gray-900' 
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition'
              ]"
            >
              Dashboard
            </router-link>
            <router-link
              to="/courses"
              :class="[
                $route.path.startsWith('/courses') 
                  ? 'border-indigo-500 text-gray-900' 
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition'
              ]"
            >
              Courses
            </router-link>
            <router-link
              v-if="authStore.isAuthenticated && authStore.userRole === 'admin'"
              to="/create-course"
              :class="[
                $route.path.startsWith('/create-course') 
                  ? 'border-indigo-500 text-gray-900' 
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition'
              ]"
            >
              Create Course
            </router-link>
          </div>
        </div>

        <!-- Settings Dropdown -->
        <div class="hidden sm:flex sm:items-center sm:ml-6">
          <div v-if="authStore.isAuthenticated" class="flex items-center space-x-4">
            <span class="text-gray-700 text-sm font-medium">{{ authStore.user?.name }}</span>
            <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-medium">
              {{ authStore.userRole }}
            </span>
            <button
              @click="handleLogout"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-800 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
            >
              Log Out
            </button>
          </div>
          <div v-else class="flex items-center space-x-4">
            <router-link
              to="/login"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 uppercase tracking-widest transition"
            >
              Login
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'
import ApplicationLogo from './ApplicationLogo.vue'

const authStore = useAuthStore()
const router = useRouter()

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}
</script>

