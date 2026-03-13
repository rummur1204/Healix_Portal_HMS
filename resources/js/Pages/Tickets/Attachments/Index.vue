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
        Attachments for Ticket #{{ ticket.id }}
      </h2>

      <!-- Upload Section -->
      <div class="comment-card mb-6">
        <h3 class="comment-title">Upload Files</h3>
        <form @submit.prevent="submitUpload">
          <div 
            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition-colors cursor-pointer"
            :class="{ 'border-blue-500 bg-blue-50': isDragging }"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            @click="triggerFileInput"
          >
            <input 
              type="file" 
              ref="fileInput" 
              multiple 
              class="hidden" 
              @change="handleFileSelect"
              accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
            >
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <p class="mt-2 text-sm text-gray-600">
              <span class="font-medium text-blue-600 hover:text-blue-500">Click to upload</span> or drag and drop
            </p>
            <p class="text-xs text-gray-500 mt-1">
              PDF, DOC, Images, ZIP up to 10MB
            </p>
          </div>

          <!-- Selected files preview -->
          <div v-if="selectedFiles.length > 0" class="mt-4">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Selected files ({{ selectedFiles.length }}):</h4>
            <div class="space-y-2 max-h-40 overflow-y-auto">
              <div v-for="(file, index) in selectedFiles" :key="index" class="flex items-center justify-between bg-gray-50 p-2 rounded">
                <div class="flex items-center min-w-0 flex-1">
                  <svg class="h-5 w-5 text-gray-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  <span class="text-sm text-gray-600 truncate" :title="file.name">{{ file.name }}</span>
                  <span class="text-xs text-gray-500 ml-2 flex-shrink-0">({{ formatFileSize(file.size) }})</span>
                </div>
                <button @click="removeFile(index)" type="button" class="text-red-500 hover:text-red-700 ml-2 flex-shrink-0">
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>
            
            <!-- Upload button -->
            <button 
              type="submit"
              :disabled="form.processing"
              class="mt-3 w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
              </svg>
              {{ form.processing ? 'Uploading...' : `Upload ${selectedFiles.length} file(s)` }}
            </button>
          </div>
        </form>

        <!-- Upload progress -->
        <div v-if="uploadProgress > 0" class="mt-4">
          <div class="flex justify-between mb-1">
            <span class="text-sm font-medium text-blue-700">Uploading...</span>
            <span class="text-sm font-medium text-blue-700">{{ uploadProgress }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" :style="{ width: uploadProgress + '%' }"></div>
          </div>
        </div>

        <!-- Error message -->
        <div v-if="error" class="mt-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-700">{{ error }}</p>
            </div>
          </div>
        </div>

        <!-- Success message -->
        <div v-if="successMessage" class="mt-4 bg-green-50 border-l-4 border-green-400 p-4 rounded">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-green-700">{{ successMessage }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Attachments List -->
      <div v-for="attachment in attachments" :key="attachment.id" class="comment-card mb-3">
        <div class="flex justify-between items-start">
          <div class="flex items-start min-w-0 flex-1">
            <!-- File icon based on type -->
            <div class="flex-shrink-0 mr-3">
              <svg v-if="attachment.file_type?.startsWith('image/')" class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              <svg v-else-if="attachment.file_type === 'application/pdf'" class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
              </svg>
              <svg v-else-if="attachment.file_type?.includes('word')" class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <svg v-else-if="attachment.file_type?.includes('excel')" class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <svg v-else class="h-8 w-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            
            <!-- File info -->
            <div class="min-w-0 flex-1">
              <div class="flex items-center flex-wrap gap-2 mb-1">
                <p class="text-sm font-medium text-gray-900 truncate" :title="attachment.file_name">
                  {{ attachment.file_name }}
                </p>
                <span class="text-xs text-gray-500">({{ attachment.file_size_formatted }})</span>
                <span v-if="attachment.is_internal" class="internal-badge">Internal</span>
              </div>
              <div class="flex items-center text-xs text-gray-500">
                Uploaded by {{ attachment.uploader?.name || 'Unknown' }}
                <span class="mx-1">•</span>
                <span>{{ formatDate(attachment.created_at) }}</span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
            <a 
              :href="`/tickets/${ticket.id}/attachments/${attachment.id}/download`"
              class="btn-download-icon"
              title="Download"
              target="_blank"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
              </svg>
            </a>
            <button 
              v-if="canModify(attachment)"
              @click="deleteAttachment(attachment)"
              class="btn-delete-icon"
              title="Delete"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="attachments.length === 0" class="text-center text-gray-500 py-6">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
        </svg>
        <p>No attachments yet</p>
        <p class="text-xs mt-1">Upload files by clicking or dragging above</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'

const props = defineProps({
  ticket: {
    type: Object,
    required: true
  },
  attachments: {
    type: Array,
    default: () => []
  }
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
  showModal.value = false
  router.visit('/tickets')
}

// File upload state
const isDragging = ref(false)
const uploadProgress = ref(0)
const selectedFiles = ref([])
const error = ref(null)
const successMessage = ref(null)
const fileInput = ref(null)

// Form for upload
const form = useForm({
  attachments: []
})

// Format file size
const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Trigger file input
const triggerFileInput = () => {
  fileInput.value.click()
}

// Handle drop
const handleDrop = (event) => {
  isDragging.value = false
  const files = Array.from(event.dataTransfer.files)
  addFiles(files)
}

// Handle file select
const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  addFiles(files)
  event.target.value = ''
}

// Add files to selected list
const addFiles = (files) => {
  selectedFiles.value = [...selectedFiles.value, ...files]
}

// Remove file from selected list
const removeFile = (index) => {
  selectedFiles.value.splice(index, 1)
}

// Submit upload
const submitUpload = () => {
  if (selectedFiles.value.length === 0) {
    error.value = 'Please select files to upload'
    return
  }

  error.value = null
  successMessage.value = null
  uploadProgress.value = 0

  // Create FormData
  const formData = new FormData()
  selectedFiles.value.forEach(file => {
    formData.append('attachments[]', file)
  })

  // Use router.post for upload
  router.post(`/tickets/${props.ticket.id}/attachments`, formData, {
    forceFormData: true,
    preserveState: true,
    preserveScroll: true,
    onProgress: (event) => {
      uploadProgress.value = Math.round((event.loaded * 100) / event.total)
    },
    onSuccess: () => {
      successMessage.value = 'Files uploaded successfully'
      selectedFiles.value = []
      uploadProgress.value = 0
      
      setTimeout(() => {
        successMessage.value = null
      }, 3000)
    },
    onError: (errors) => {
      error.value = errors.attachments || 'Failed to upload files'
      uploadProgress.value = 0
    }
  })
}

// Delete attachment
const deleteAttachment = (attachment) => {
  if (!confirm('Are you sure you want to delete this attachment?')) {
    return
  }

  router.delete(`/tickets/${props.ticket.id}/attachments/${attachment.id}`, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'File deleted successfully'
      setTimeout(() => {
        successMessage.value = null
      }, 3000)
    },
    onError: (errors) => {
      error.value = 'Failed to delete attachment'
    }
  })
}

// Permissions
const canModify = (attachment) => user.is_admin || attachment.uploader_id === user.id

// Format date
const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleString()
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

.comment-textarea { 
  width: 100%; 
  border: 1px solid #ccc; 
  border-radius: 8px; 
  padding: 8px; 
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

.comment-date { 
  font-size: 12px; 
  color: #6c757d; 
  margin-left: 8px; 
}

.internal-badge { 
  font-size: 10px; 
  color: #856404; 
  background: #fff3cd; 
  padding: 2px 6px; 
  border-radius: 6px; 
  margin-left: 6px; 
}

.comment-text { 
  white-space: pre-wrap; 
}

.btn-edit-icon, .btn-delete-icon, .btn-download-icon { 
  background: none; 
  border: none; 
  cursor: pointer; 
  padding: 8px; 
  border-radius: 6px; 
  transition: 0.2s; 
  font-size: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-edit-icon:hover { 
  background: #d1f0d1; 
}

.btn-delete-icon:hover { 
  background: #f8d6d6; 
}

.btn-download-icon:hover {
  background: #e6f0ff;
}

.btn-save { 
  background: #2c5e2c; 
  color: white; 
  padding: 6px 12px; 
  border-radius: 6px; 
  cursor: pointer; 
}

.btn-save:hover { 
  background: #1f4520; 
}

.btn-cancel { 
  background: #ccc; 
  color: #333; 
  padding: 6px 12px; 
  border-radius: 6px; 
  cursor: pointer; 
}
</style>