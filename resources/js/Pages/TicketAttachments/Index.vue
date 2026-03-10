<template>
  <!-- Modal Overlay -->
  <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <!-- Modal Container -->
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl p-6 relative max-h-[90vh] overflow-y-auto">
      
      <!-- Close / Back Button -->
      <button @click="goBack" class="absolute top-4 right-4 text-gray-600 hover:text-gray-900 text-xl font-bold">
        ← Back
      </button>

      <h2 class="text-2xl font-semibold text-green-800 mb-4">
        Attachments for Ticket #{{ ticketId || 'N/A' }}
      </h2>

      <!-- Upload Attachment Section -->
      <div class="comment-card mb-6">
        <h3 class="comment-title">Upload Attachment</h3>
        <form @submit.prevent="submitAttachment" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="block text-sm font-medium text-gray-700 mb-2">Choose File</label>
            <input
              type="file"
              @change="handleFileSelect"
              class="w-full border border-gray-300 rounded-lg p-2"
              :disabled="uploading"
            />
            <div v-if="uploadError" class="text-red-500 text-sm mt-1">{{ uploadError }}</div>
          </div>

          <button
            type="submit"
            :disabled="uploading || !selectedFile"
            class="btn-post"
          >
            {{ uploading ? 'Uploading...' : 'Upload Attachment' }}
          </button>
        </form>
      </div>

      <!-- Attachments List -->
      <div v-if="attachmentsData.length > 0">
        <div v-for="attachment in attachmentsData" :key="attachment.id" class="comment-card mb-3">
          <div class="flex justify-between items-center mb-2">
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                </svg>
                <a :href="`/storage/${attachment.file_path}`" target="_blank" class="file-link font-medium">
                  {{ attachment.file_name }}
                </a>
              </div>
              <div class="text-sm text-gray-600 mt-1">
                <span>Uploaded by {{ attachment.uploaded_by?.name || 'Unknown' }}</span>
                <span class="mx-2">•</span>
                <span>{{ formatDate(attachment.created_at) }}</span>
                <span class="mx-2">•</span>
                <span>{{ formatSize(attachment.file_size) }}</span>
                <span v-if="attachment.file_type" class="mx-2">•</span>
                <span v-if="attachment.file_type" class="text-xs bg-gray-200 px-2 py-1 rounded">
                  {{ attachment.file_type }}
                </span>
              </div>
            </div>

            <div v-if="canModify(attachment)" class="space-x-2">
              <button @click="deleteAttachment(attachment)" class="btn-delete-icon" title="Delete" :disabled="deleting">
                🗑
              </button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-section mt-4" v-if="attachments.last_page > 1">
          <button 
            @click="changePage(attachments.current_page - 1)" 
            :disabled="attachments.current_page === 1" 
            class="pagination-btn"
          >
            Previous
          </button>
          <span class="page-info">Page {{ attachments.current_page }} of {{ attachments.last_page }}</span>
          <button 
            @click="changePage(attachments.current_page + 1)" 
            :disabled="attachments.current_page === attachments.last_page" 
            class="pagination-btn"
          >
            Next
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center text-gray-500 py-6">
        No attachments yet. Upload the first one!
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  ticket: {
    type: Object,
    default: null
  },
  attachments: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const page = usePage()
const user = page.props.auth.user

// State
const showModal = ref(false)
const selectedFile = ref(null)
const uploading = ref(false)
const deleting = ref(false)
const uploadError = ref('')

// Compute ticket ID from either ticket prop or first attachment
const ticketId = computed(() => {
  if (props.ticket?.id) {
    return props.ticket.id
  }
  // Try to get ticket_id from first attachment if available
  if (props.attachments?.data?.length > 0 && props.attachments.data[0]?.ticket_id) {
    return props.attachments.data[0].ticket_id
  }
  // Try to get from URL params
  const urlParams = new URLSearchParams(window.location.search)
  const ticketParam = urlParams.get('ticket_id')
  if (ticketParam) {
    return ticketParam
  }
  return null
})

// Get attachments data array (handle both paginated object and array)
const attachmentsData = computed(() => {
  if (Array.isArray(props.attachments)) {
    return props.attachments
  }
  if (props.attachments?.data && Array.isArray(props.attachments.data)) {
    return props.attachments.data
  }
  return []
})

// Open modal automatically on page load
onMounted(() => {
  showModal.value = true
})

// Back button: go to tickets index page
const goBack = () => {
  showModal.value = false
  router.visit('/tickets')
}

// Handle file selection
const handleFileSelect = (event) => {
  selectedFile.value = event.target.files[0]
  uploadError.value = ''
}

// Submit a new attachment
const submitAttachment = () => {
  if (!selectedFile.value) return
  
  const id = ticketId.value
  if (!id) {
    uploadError.value = 'Cannot determine ticket ID'
    return
  }

  // Validate file size (10MB max)
  if (selectedFile.value.size > 10 * 1024 * 1024) {
    uploadError.value = 'File size must be less than 10MB'
    return
  }

  uploading.value = true
  uploadError.value = ''

  // Create FormData object
  const formData = new FormData()
  formData.append('file', selectedFile.value)
  formData.append('ticket_id', id)
  
  // Upload file
  router.post(`/tickets/${id}/attachments`, formData, {
    preserveScroll: true,
    headers: {
      'Content-Type': 'multipart/form-data'
    },
    onSuccess: () => {
      uploading.value = false
      selectedFile.value = null
      uploadError.value = ''
      // Clear file input
      const fileInput = document.querySelector('input[type="file"]')
      if (fileInput) fileInput.value = ''
    },
    onError: (errors) => {
      uploading.value = false
      console.error('Upload failed:', errors)
      
      // Display error message
      if (errors.file) {
        uploadError.value = errors.file
      } else if (errors.ticket_id) {
        uploadError.value = errors.ticket_id
      } else {
        uploadError.value = 'Failed to upload attachment. Please try again.'
      }
    }
  })
}

// Delete attachment
const deleteAttachment = (attachment) => {
  const id = ticketId.value
  if (!id) {
    alert('Cannot determine ticket ID')
    return
  }

  if (confirm('Are you sure you want to delete this attachment? This action cannot be undone.')) {
    deleting.value = true
    
    router.delete(`/tickets/${id}/attachments/${attachment.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        deleting.value = false
      },
      onError: (errors) => {
        deleting.value = false
        console.error('Delete failed:', errors)
        alert('Failed to delete attachment. Please try again.')
      }
    })
  }
}

// Change page
const changePage = (page) => {
  const id = ticketId.value
  if (!id) return

  router.get(`/tickets/${id}/attachments`, 
    {
      page: page,
      search: localFilters.value.search,
      sort_field: localFilters.value.sort_field,
      sort_direction: localFilters.value.sort_direction
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true
    }
  )
}

// Local filters for pagination/sorting
const localFilters = ref({
  search: props.filters?.search || '',
  sort_field: props.filters?.sort_field || 'created_at',
  sort_direction: props.filters?.sort_direction || 'desc'
})

// Permissions - only admin or uploader can delete
const canModify = (attachment) => user?.is_admin || attachment.upload_by_user_id === user?.id

// Format date
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Format file size
const formatSize = (size) => {
  if (!size) return '0 KB'
  
  const bytes = parseInt(size)
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(2) + ' MB'
}
</script>

<style scoped>
.comment-card { 
  background: #f0fff0; 
  padding: 16px; 
  border-radius: 12px; 
  box-shadow: 0 2px 8px rgba(44, 94, 44, 0.1); 
}

.comment-title { 
  font-size: 18px; 
  font-weight: 600; 
  color: #2c5e2c; 
  margin-bottom: 12px; 
}

.btn-post { 
  background: #2c5e2c; 
  color: white; 
  padding: 8px 16px; 
  border-radius: 8px; 
  cursor: pointer; 
  border: none;
  font-weight: 500;
  transition: background 0.3s ease;
}

.btn-post:hover:not(:disabled) { 
  background: #1f4520; 
}

.btn-post:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.file-link {
  color: #4CAF50;
  text-decoration: none;
  transition: color 0.3s ease;
}

.file-link:hover {
  color: #2c5e2c;
  text-decoration: underline;
}

.btn-delete-icon { 
  background: none; 
  border: none; 
  cursor: pointer; 
  padding: 8px; 
  border-radius: 6px; 
  transition: 0.2s; 
  font-size: 16px;
}

.btn-delete-icon:hover:not(:disabled) { 
  background: #f8d6d6; 
  transform: scale(1.1);
}

.btn-delete-icon:active:not(:disabled) {
  transform: scale(0.95);
}

.btn-delete-icon:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-section {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
}

.pagination-btn {
  background: white;
  border: 2px solid #4CAF50;
  color: #4CAF50;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: #4CAF50;
  color: white;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #2c5e2c;
  font-weight: 500;
  font-size: 13px;
}

/* Custom scrollbar for modal */
.modal-content::-webkit-scrollbar {
  width: 8px;
}

.modal-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.modal-content::-webkit-scrollbar-thumb {
  background: #2c5e2c;
  border-radius: 10px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
  background: #1f4520;
}
</style>