<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Course Forum</h1>
        <button
          @click="showNewTopic = true"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md"
        >
          New Topic
        </button>
      </div>

      <div v-if="showNewTopic" class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Create New Topic</h2>
        <form @submit.prevent="createTopic">
          <div class="space-y-4">
            <input
              v-model="newTopic.title"
              type="text"
              required
              placeholder="Topic title"
              class="w-full px-3 py-2 border border-gray-300 rounded-md"
            />
            <textarea
              v-model="newTopic.content"
              rows="4"
              required
              placeholder="Topic content"
              class="w-full px-3 py-2 border border-gray-300 rounded-md"
            ></textarea>
            <div class="flex space-x-4">
              <button
                type="submit"
                :disabled="loading"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md disabled:opacity-50"
              >
                Create
              </button>
              <button
                type="button"
                @click="showNewTopic = false"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md"
              >
                Cancel
              </button>
            </div>
          </div>
        </form>
      </div>

      <div v-if="loading" class="text-center py-12">
        <p class="text-gray-600">Loading topics...</p>
      </div>

      <div v-else-if="topics.length === 0" class="text-center py-12 bg-white rounded-lg shadow border border-gray-200 p-6">
        <p class="text-gray-600">No topics yet. Be the first to start a discussion!</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="topic in topics"
          :key="topic.id"
          class="bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition border border-gray-200"
          @click="viewTopic(topic.id)"
        >
          <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ topic.title }}</h3>
          <p class="text-gray-600 mb-4 line-clamp-2">{{ topic.content }}</p>
          <div class="flex justify-between text-sm text-gray-500">
            <span>By <strong>{{ topic.author_name }}</strong></span>
            <span>{{ topic.reply_count || 0 }} {{ topic.reply_count === 1 ? 'reply' : 'replies' }}</span>
          </div>
        </div>
      </div>

      <!-- Topic Detail Modal -->
      <div
        v-if="selectedTopic"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
        @click.self="selectedTopic = null"
      >
        <div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
          <div class="p-6">
            <div class="flex justify-between items-start mb-4">
              <h2 class="text-2xl font-bold">{{ selectedTopic.title }}</h2>
              <button
                @click="selectedTopic = null"
                class="text-gray-500 hover:text-gray-700"
              >
                ✕
              </button>
            </div>
            <p class="text-gray-600 mb-6">{{ selectedTopic.content }}</p>

            <div class="border-t pt-6 mb-6">
              <h3 class="text-lg font-semibold mb-4">Replies ({{ replies.length }})</h3>
              <div v-if="replies.length === 0" class="text-gray-600 mb-4">
                No replies yet. Be the first to reply!
              </div>
              <div v-else class="space-y-4 mb-6">
                <div
                  v-for="reply in replies"
                  :key="reply.id"
                  class="border-l-4 border-indigo-200 pl-4 py-2 bg-gray-50 rounded-r"
                >
                  <p class="text-gray-800 mb-2">{{ reply.content }}</p>
                  <p class="text-sm text-gray-500">By <strong>{{ reply.author_name }}</strong></p>
                </div>
              </div>

              <div class="border-t pt-4">
                <h4 class="font-semibold mb-2">Add Reply</h4>
                <form @submit.prevent="addReply">
                  <textarea
                    v-model="newReply"
                    rows="3"
                    required
                    placeholder="Write your reply..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md mb-2"
                  ></textarea>
                  <button
                    type="submit"
                    :disabled="replying"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md disabled:opacity-50"
                  >
                    {{ replying ? 'Posting...' : 'Post Reply' }}
                  </button>
                </form>
              </div>
            </div>
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

const courseId = route.params.courseId
const topics = ref([])
const selectedTopic = ref(null)
const replies = ref([])
const showNewTopic = ref(false)
const newTopic = ref({ title: '', content: '' })
const newReply = ref('')
const loading = ref(true)
const replying = ref(false)

onMounted(async () => {
  await loadTopics()
})

const loadTopics = async () => {
  try {
    const response = await api.get(`/forums/course/${courseId}/topics`)
    topics.value = response.data.topics
  } catch (err) {
    console.error('Failed to load topics:', err)
  } finally {
    loading.value = false
  }
}

const viewTopic = async (topicId) => {
  try {
    const response = await api.get(`/forums/topics/${topicId}`)
    selectedTopic.value = response.data.topic
    replies.value = response.data.replies
  } catch (err) {
    console.error('Failed to load topic:', err)
  }
}

const createTopic = async () => {
  try {
    loading.value = true
    await api.post('/forums/topics', {
      course_id: parseInt(courseId),
      title: newTopic.value.title,
      content: newTopic.value.content
    })
    newTopic.value = { title: '', content: '' }
    showNewTopic.value = false
    await loadTopics()
  } catch (err) {
    console.error('Failed to create topic:', err)
    alert(err.response?.data?.error || 'Failed to create topic. Please try again.')
  } finally {
    loading.value = false
  }
}

const addReply = async () => {
  if (!selectedTopic.value) return
  replying.value = true
  try {
    await api.post('/forums/replies', {
      topic_id: selectedTopic.value.id,
      content: newReply.value
    })
    newReply.value = ''
    await viewTopic(selectedTopic.value.id)
    await loadTopics()
  } catch (err) {
    alert(err.response?.data?.error || 'Failed to post reply')
  } finally {
    replying.value = false
  }
}
</script>

