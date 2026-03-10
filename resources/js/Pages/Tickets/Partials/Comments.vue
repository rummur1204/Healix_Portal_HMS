<template>
  <!-- Modal Overlay -->
  <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">

    <!-- Modal Container -->
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl p-6 relative">
      
      <!-- Close / Back Button -->
      <button @click="goBack" class="absolute top-4 right-4 text-gray-600 hover:text-gray-900 text-xl font-bold">
        ← Back
      </button>

      <h2 class="text-2xl font-semibold text-green-800 mb-4">
        Comments for Ticket #{{ ticket.id }}
      </h2>

      <!-- Add Comment Section -->
      <div class="comment-card mb-6">
        <h3 class="comment-title">Add Comment</h3>
        <form @submit.prevent="submitComment">
          <textarea
            v-model="form.comment_text"
            rows="4"
            class="comment-textarea"
            placeholder="Write your comment..."
          ></textarea>

          <div v-if="user.is_admin" class="mb-3">
            <label class="flex items-center">
              <input type="checkbox" v-model="form.is_internal" class="mr-2">
              Internal Note
            </label>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="btn-post"
          >
            {{ form.processing ? 'Posting...' : 'Post Comment' }}
          </button>
        </form>
      </div>

      <!-- Comment List -->
      <div v-for="comment in comments" :key="comment.id" class="comment-card mb-3">
        <div class="flex justify-between items-center mb-2">
          <div>
            <strong>{{ comment.user.name }}</strong>
            <span class="comment-date">{{ formatDate(comment.created_at) }}</span>
            <span v-if="comment.is_internal" class="internal-badge">Internal</span>
          </div>

          <div v-if="canModify(comment)" class="space-x-2">
            <button @click="startEdit(comment)" class="btn-edit-icon" title="Edit">✎</button>
            <button @click="deleteComment(comment)" class="btn-delete-icon" title="Delete">🗑</button>
          </div>
        </div>

        <!-- Edit Mode -->
        <div v-if="editing && editing.id === comment.id">
          <textarea v-model="editForm.comment_text" rows="3" class="comment-textarea mb-2"></textarea>
          <button @click="updateComment(comment)" class="btn-save mr-2">Save</button>
          <button @click="cancelEdit" class="btn-cancel">Cancel</button>
        </div>

        <!-- Normal View -->
        <div v-else>
          <p class="comment-text">{{ comment.comment_text }}</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="comments.length === 0" class="text-center text-gray-500 py-6">
        No comments yet.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'

const props = defineProps({
  ticket: Object,
  comments: Array
})

const page = usePage()
const user = page.props.auth.user

// Modal state
const showModal = ref(false)

// Open modal automatically on page load
onMounted(() => {
  showModal.value = true
})

// Back button: go to tickets index page
const goBack = () => {
  // Close modal first
  showModal.value = false
  
  // Navigate to the tickets index page
  router.visit('/tickets')
  
  // Alternative if you have the route helper:
  // router.visit(route('tickets.index'))
}

// Comment forms
const editing = ref(null)
const form = useForm({
  comment_text: '',
  is_internal: false
})
const editForm = useForm({
  comment_text: ''
})

// Submit a new comment
const submitComment = () => {
  form.post(route('tickets.comments.store', props.ticket.id), {
    onSuccess: () => {
      form.reset()
      router.reload() // reload comments
    }
  })
}

// Edit comment
const startEdit = (comment) => {
  editing.value = comment
  editForm.comment_text = comment.comment_text
}
const cancelEdit = () => editing.value = null
const updateComment = (comment) => {
  editForm.put(
    route('tickets.comments.update', [props.ticket.id, comment.id]),
    { preserveScroll: true, onSuccess: () => editing.value = null }
  )
}

// Delete comment
const deleteComment = (comment) => {
  if (confirm('Delete this comment?')) {
    editForm.delete(
      route('tickets.comments.destroy', [props.ticket.id, comment.id]),
      { preserveScroll: true }
    )
  }
}

// Permissions
const canModify = (comment) => user.is_admin || comment.user_id === user.id

// Format date
const formatDate = (date) => new Date(date).toLocaleString()
</script>

<style scoped>
.comment-card { background: #f0fff0; padding: 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(44, 94, 44, 0.1); }
.comment-title { font-size: 18px; font-weight: 600; color: #2c5e2c; margin-bottom: 12px; }
.comment-textarea { width: 100%; border: 1px solid #ccc; border-radius: 8px; padding: 8px; }
.btn-post { background: #2c5e2c; color: white; padding: 8px 16px; border-radius: 8px; cursor: pointer; }
.btn-post:hover { background: #1f4520; }
.comment-date { font-size: 12px; color: #6c757d; margin-left: 8px; }
.internal-badge { font-size: 10px; color: #856404; background: #fff3cd; padding: 2px 6px; border-radius: 6px; margin-left: 6px; }
.comment-text { white-space: pre-wrap; }
.btn-edit-icon, .btn-delete-icon { background: none; border: none; cursor: pointer; padding: 4px; border-radius: 6px; transition: 0.2s; }
.btn-edit-icon:hover { background: #d1f0d1; }
.btn-delete-icon:hover { background: #f8d6d6; }
.btn-save { background: #2c5e2c; color: white; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
.btn-save:hover { background: #1f4520; }
.btn-cancel { background: #ccc; color: #333; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
</style>