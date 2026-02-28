<template>
  <div class="comments-container space-y-6">

    <!-- Back Button -->
    <button @click="goBack" class="btn-back mb-4">
      ← Back to Tickets
    </button>

    <!-- Add Comment -->
    <div class="comment-card">
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
    <div v-for="comment in comments" :key="comment.id" class="comment-card">

      <!-- Header -->
      <div class="flex justify-between items-center mb-3">
        <div>
          <strong>{{ comment.user.name }}</strong>
          <span class="comment-date">{{ formatDate(comment.created_at) }}</span>

          <span v-if="comment.is_internal" class="internal-badge">Internal</span>
        </div>

        <div v-if="canModify(comment)" class="space-x-2">
          <button @click="startEdit(comment)" class="btn-edit-icon" title="Edit">
            <svg viewBox="0 0 20 20" fill="currentColor" class="action-icon">
              <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
            </svg>
          </button>

          <button @click="deleteComment(comment)" class="btn-delete-icon" title="Delete">
            <svg viewBox="0 0 20 20" fill="currentColor" class="action-icon">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Edit Mode -->
      <div v-if="editing && editing.id === comment.id">
        <textarea v-model="editForm.comment_text" rows="3" class="comment-textarea mb-3"></textarea>

        <button @click="updateComment(comment)" class="btn-save mr-2">Save</button>
        <button @click="cancelEdit" class="btn-cancel">Cancel</button>
      </div>

      <!-- Normal View -->
      <div v-else>
        <p class="comment-text">{{ comment.comment_text }}</p>
      </div>

    </div>

    <!-- Empty -->
    <div v-if="comments.length === 0" class="text-center text-gray-500 py-6">
      No comments yet.
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'

const props = defineProps({
  ticket: Object,
  comments: Array
})

const page = usePage()
const user = page.props.auth.user

const editing = ref(null)

const form = useForm({
  comment_text: '',
  is_internal: false
})

const editForm = useForm({
  comment_text: ''
})

// Back to Tickets index
const goBack = () => {
  router.visit('/tickets')
}

// Submit comment and redirect instantly
const submitComment = () => {
  form.post(route('tickets.comments.store', props.ticket.id), {
    onSuccess: () => {
      router.visit('/tickets') // redirect to index after comment
    }
  })
}

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

const deleteComment = (comment) => {
  if (confirm('Delete this comment?')) {
    editForm.delete(
      route('tickets.comments.destroy', [props.ticket.id, comment.id]),
      { preserveScroll: true }
    )
  }
}

const canModify = (comment) => user.is_admin || comment.user_id === user.id

const formatDate = (date) => new Date(date).toLocaleString()
</script>

<style scoped>
.comments-container { display: flex; flex-direction: column; gap: 24px; }
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
.action-icon { width: 18px; height: 18px; }
.btn-save { background: #2c5e2c; color: white; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
.btn-save:hover { background: #1f4520; }
.btn-cancel { background: #ccc; color: #333; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
.btn-back { background: #d1f0d1; color: #2c5e2c; padding: 6px 12px; border-radius: 8px; cursor: pointer; }
.btn-back:hover { background: #b0e0b0; }
</style>