<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">My Certificates</h1>

      <div v-if="loading" class="text-center py-12">
        <p class="text-gray-600">Loading certificates...</p>
      </div>

      <div v-else-if="certificates.length === 0" class="text-center py-12">
        <p class="text-gray-600 mb-4">You don't have any certificates yet.</p>
        <p class="text-gray-500 text-sm">Complete a course to earn a certificate!</p>
      </div>

      <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="certificate in certificates"
          :key="certificate.id"
          class="bg-gradient-to-br from-yellow-50 to-yellow-100 border-2 border-yellow-300 rounded-lg shadow-lg p-8 text-center"
        >
          <div class="mb-6">
            <svg class="w-16 h-16 mx-auto text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Certificate of Completion</h3>
          <p class="text-lg font-semibold text-gray-700 mb-4">{{ certificate.course_title }}</p>
          <p class="text-sm text-gray-600 mb-2">Certificate Number:</p>
          <p class="text-xs text-gray-500 font-mono mb-4">{{ certificate.certificate_number }}</p>
          <p class="text-sm text-gray-600">
            Issued: {{ new Date(certificate.issued_at).toLocaleDateString() }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Navbar from '../components/Navbar.vue'
import api from '../services/api'

const certificates = ref([])
const loading = ref(true)

onMounted(async () => {
  await loadCertificates()
})

const loadCertificates = async () => {
  try {
    const response = await api.get('/certificates/my-certificates')
    certificates.value = response.data.certificates || []
    console.log('Certificates loaded:', certificates.value.length)
  } catch (err) {
    console.error('Failed to load certificates:', err)
    console.error('Error details:', err.response?.data || err.message)
    certificates.value = []
  } finally {
    loading.value = false
  }
}
</script>

