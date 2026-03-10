<template>
  <AuthenticatedLayout>
    <div class="ticket-detail-container">
      <!-- Header -->
      <div class="detail-header">
        <div class="header-left">
          <button @click="goBack" class="btn-back">
            <svg class="back-icon" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
            </svg>
            Back to Tickets
          </button>
          <h1 class="ticket-title">Ticket #{{ ticket.ticket_number }}</h1>
        </div>
        <div class="header-actions">
          <button @click="editTicket" class="btn-edit">
            <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
              <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
            </svg>
            Edit Ticket
          </button>
        </div>
      </div>

      <!-- Ticket Details Card -->
      <div class="detail-card">
        <div class="ticket-status-bar">
          <span :class="['priority-badge', ticket.priority]">{{ formatPriority(ticket.priority) }}</span>
          <span :class="['status-badge', ticket.status]">{{ formatStatus(ticket.status) }}</span>
          <span class="active-badge" :class="{ 'active': ticket.active, 'inactive': !ticket.active }">
            {{ ticket.active ? 'Active' : 'Inactive' }}
          </span>
        </div>

        <div class="ticket-content">
          <h2 class="ticket-subject">{{ ticket.title }}</h2>
          <p class="ticket-description">{{ ticket.description }}</p>
        </div>

        <div class="detail-grid">
          <div class="detail-item">
            <label>Client</label>
            <p>{{ ticket.client?.organization_name ?? 'N/A' }}</p>
          </div>
          <div class="detail-item">
            <label>Ticket Type</label>
            <p>{{ formatTicketType(ticket.ticket_type) }}</p>
          </div>
          <div class="detail-item">
            <label>Assigned To</label>
            <p>{{ ticket.assigned_to?.name ?? 'Unassigned' }}</p>
          </div>
          <div class="detail-item">
            <label>Due Date</label>
            <p :class="{ 'overdue': isOverdue(ticket.due_date) }">{{ formatDate(ticket.due_date) }}</p>
            
          </div>
          
          <div class="detail-item">
            <label>Created By</label>
            <p>{{ ticket.created_by?.name ?? 'N/A' }}</p>
          </div>
          <div class="detail-item">
            <label>Created At</label>
            <p>{{ formatDate(ticket.created_at) }}</p>
            <!-- Temporary debug section - REMOVE AFTER TESTING -->


          </div>
        </div>
      </div>

      <!-- Comments Section -->
      <div class="detail-section">
        <div class="section-header">
          <h2>Comments ({{ ticket.comments?.length || 0 }})</h2>
          <button @click="openCommentModal()" class="btn-add">
            <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Add Comment
          </button>
        </div>

        <div v-if="ticket.comments?.length" class="comments-list">
          <div v-for="comment in ticket.comments" :key="comment.id" class="comment-card">
            <div class="comment-header">
              <div class="comment-user">
                <div class="user-avatar">{{ getUserInitials(comment.user) }}</div>
                <div>
                  <strong>{{ comment.user?.name ?? 'Unknown User' }}</strong>
                  <span class="comment-date">{{ formatDateTime(comment.created_at) }}</span>
                </div>
              </div>
              <div class="comment-actions">
                <button @click="openCommentModal(comment)" class="btn-icon-sm" title="Edit">
                  <svg class="action-icon-small" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                  </svg>
                </button>
                <button @click="deleteComment(comment)" class="btn-icon-sm text-red-600" title="Delete">
                  <svg class="action-icon-small" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                </button>
              </div>
            </div>
            <p class="comment-text">{{ comment.comment_text || comment.comment }}</p>
            <div v-if="comment.updated_at !== comment.created_at" class="comment-edit-info">
              Edited {{ formatDateTime(comment.updated_at) }}
            </div>
          </div>
        </div>
        <p v-else class="empty-text">No comments yet. Be the first to comment!</p>
      </div>

      <!-- Attachments Section -->
      <div class="detail-section">
        <div class="section-header">
          <h2>Attachments ({{ ticket.attachments?.length || 0 }})</h2>
          <button @click="openAttachmentModal()" class="btn-add">
            <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Upload Files
          </button>
        </div>

        <div v-if="ticket.attachments?.length" class="attachments-grid">
          <div v-for="file in ticket.attachments" :key="file.id" class="attachment-card">
            <div class="attachment-icon">
              <svg v-if="isImage(file.file_name)" class="file-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
              </svg>
              <svg v-else class="file-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="attachment-details">
              <a :href="`/storage/${file.file_path}`" target="_blank" class="attachment-name">
                {{ file.file_name }}
              </a>
              <span class="attachment-meta">
                {{ formatFileSize(file.file_size) }} • Uploaded by {{ file.user?.name ?? 'Unknown' }}
              </span>
            </div>
            <div class="attachment-actions">
              <button @click="downloadAttachment(file)" class="btn-icon-sm" title="Download">
                <svg class="action-icon-small" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
              </button>
              <button @click="deleteAttachment(file)" class="btn-icon-sm text-red-600" title="Delete">
                <svg class="action-icon-small" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
        <p v-else class="empty-text">No attachments uploaded yet.</p>
      </div>

      <!-- History Section -->
      <div class="detail-section">
        <div class="section-header">
          <h2>History</h2>
        </div>

        <div v-if="ticket.history?.length" class="history-list">
          <div v-for="entry in ticket.history" :key="entry.id" class="history-card">
            <div class="history-header">
              <div class="history-user">
                <strong>{{ entry.user?.name ?? 'System' }}</strong>
                <span class="history-date">{{ formatDateTime(entry.created_at) }}</span>
              </div>
            </div>
            <p class="history-text">
              <span class="changed-field">{{ formatFieldName(entry.field_name) }}</span>:
              <span class="old-value">{{ entry.old_value || 'empty' }}</span>
              <span class="arrow">→</span>
              <span class="new-value">{{ entry.new_value || 'empty' }}</span>
            </p>
          </div>
        </div>
        <p v-else class="empty-text">No history available</p>
      </div>

      <!-- Comment Modal -->
      <div v-if="showCommentModal" class="modal-overlay" @click.self="closeCommentModal">
        <div class="modal-content">
          <h3>{{ editingComment ? 'Edit Comment' : 'Add Comment' }}</h3>
          <!-- Update the comment form section in the template -->
<form @submit.prevent="saveComment">
  <div class="form-group">
    <label for="comment">Comment</label>
    <textarea
      id="comment"
      v-model="commentForm.comment"
      rows="4"
      required
      class="form-input"
      placeholder="Enter your comment..."
    ></textarea>
    <div v-if="commentForm.errors" class="error-message">
      {{ commentForm.errors }}
    </div>
  </div>
  <div class="modal-actions">
    <button type="submit" class="btn-save" :disabled="commentForm.processing">
      {{ commentForm.processing ? 'Saving...' : 'Save' }}
    </button>
    <button type="button" @click="closeCommentModal" class="btn-cancel">Cancel</button>
  </div>
</form>
        </div>
      </div>

      <!-- Attachment Upload Modal -->
      <div v-if="showAttachmentModal" class="modal-overlay" @click.self="closeAttachmentModal">
        <div class="modal-content">
          <h3>Upload Files</h3>
          <form @submit.prevent="uploadAttachments">
            <div class="form-group">
              <label for="files">Select Files</label>
              <input
                type="file"
                id="files"
                multiple
                @change="handleFileSelect"
                class="form-input"
                accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt"
              />
              <div v-if="attachmentForm.files.length" class="file-list">
                <div v-for="(file, index) in attachmentForm.files" :key="index" class="file-item">
                  <span>{{ file.name }}</span>
                  <button type="button" @click="removeFile(index)" class="btn-remove-file">×</button>
                </div>
              </div>
            </div>
            <div class="modal-actions">
              <button type="submit" class="btn-save" :disabled="attachmentForm.processing || !attachmentForm.files.length">
                {{ attachmentForm.processing ? 'Uploading...' : 'Upload' }}
              </button>
              <button type="button" @click="closeAttachmentModal" class="btn-cancel">Cancel</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="modal-overlay" @click.self="cancelDelete">
        <div class="modal-content delete-modal">
          <h3>Confirm Delete</h3>
          <p>Are you sure you want to delete this {{ deleteItemType }}? This cannot be undone.</p>
          <div class="modal-actions">
            <button @click="confirmDelete" class="btn-confirm-delete" :disabled="deleteProcessing">
              {{ deleteProcessing ? 'Deleting...' : 'Delete' }}
            </button>
            <button @click="cancelDelete" class="btn-cancel">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  components: {
    AuthenticatedLayout
  },
  props: { 
    ticket: Object 
  },
  data() {
    return {
      // Comment modal
      showCommentModal: false,
      editingComment: null,
      commentForm: {
        comment: '',
        processing: false,
        errors: null 
      },
      
      // Attachment modal
      showAttachmentModal: false,
      attachmentForm: {
        files: [],
        processing: false
      },
      
      // Delete modal
      showDeleteModal: false,
      deleteItemType: '',
      deleteItemId: null,
      deleteProcessing: false
    }
  },
  methods: {
    goBack() {
      router.visit('/tickets')
    },
    
    editTicket() {
      router.visit(`/tickets/${this.ticket.id}/edit`)
    },
    
    // Comment methods
    openCommentModal(comment = null) {
      if (comment) {
        this.editingComment = comment
        this.commentForm.comment = comment.comment
      } else {
        this.editingComment = null
        this.commentForm.comment = ''
      }
      this.showCommentModal = true
    },
    
    closeCommentModal() {
      this.showCommentModal = false
      this.editingComment = null
      this.commentForm.comment = ''
    },
    
    saveComment() {
  this.commentForm.processing = true
  this.commentForm.errors = null
  
  // Your backend expects 'comment_text' based on the error message
  const payload = {
    comment_text: this.commentForm.comment  // Changed from 'comment' to 'comment_text'
  }
  
  const url = this.editingComment 
    ? `/tickets/${this.ticket.id}/comments/${this.editingComment.id}`
    : `/tickets/${this.ticket.id}/comments`
  
  const method = this.editingComment ? 'put' : 'post'
  
  router[method](url, payload, {
    preserveScroll: true,
    onSuccess: () => {
      this.closeCommentModal()
      this.commentForm.processing = false
      this.commentForm.errors = null
    },
    onError: (errors) => {
      console.error('Save failed:', errors)
      
      // Handle the error message
      if (typeof errors === 'object') {
        // Extract the first error message
        const firstError = Object.values(errors)[0]
        this.commentForm.errors = Array.isArray(firstError) ? firstError[0] : firstError
      } else {
        this.commentForm.errors = errors || 'Failed to save comment'
      }
      
      this.commentForm.processing = false
    }
  })

},
      
    
    
    deleteComment(comment) {
      this.deleteItemType = 'comment'
      this.deleteItemId = comment.id
      this.showDeleteModal = true
    },
    
    // Attachment methods
    openAttachmentModal() {
      this.showAttachmentModal = true
      this.attachmentForm.files = []
    },
    
    closeAttachmentModal() {
      this.showAttachmentModal = false
      this.attachmentForm.files = []
    },
    
    handleFileSelect(event) {
      this.attachmentForm.files = Array.from(event.target.files)
    },
    
    removeFile(index) {
      this.attachmentForm.files.splice(index, 1)
    },
    
    uploadAttachments() {
      if (!this.attachmentForm.files.length) return
      
      this.attachmentForm.processing = true
      
      const formData = new FormData()
      this.attachmentForm.files.forEach(file => {
        formData.append('attachments[]', file)
      })
      
      router.post(`/tickets/${this.ticket.id}/attachments`, formData, {
        preserveScroll: true,
        onSuccess: () => {
          this.closeAttachmentModal()
          this.attachmentForm.processing = false
        },
        onError: (errors) => {
          alert('Failed to upload files: ' + Object.values(errors).join(', '))
          this.attachmentForm.processing = false
        }
      })
    },
    
    downloadAttachment(attachment) {
      window.open(`/storage/${attachment.file_path}`, '_blank')
    },
    
    deleteAttachment(attachment) {
      this.deleteItemType = 'attachment'
      this.deleteItemId = attachment.id
      this.showDeleteModal = true
    },
    
    // Delete confirmation
    confirmDelete() {
      this.deleteProcessing = true
      
      let url = ''
      if (this.deleteItemType === 'comment') {
        url = `/tickets/${this.ticket.id}/comments/${this.deleteItemId}`
      } else if (this.deleteItemType === 'attachment') {
        url = `/tickets/${this.ticket.id}/attachments/${this.deleteItemId}`
      }
      
      router.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
          this.showDeleteModal = false
          this.deleteProcessing = false
          this.deleteItemId = null
        },
        onError: (errors) => {
          alert('Failed to delete: ' + (errors.message || 'Unknown error'))
          this.deleteProcessing = false
        }
      })
    },
    
    cancelDelete() {
      this.showDeleteModal = false
      this.deleteItemId = null
    },
    
    // Formatting methods - these were missing!
    formatPriority(p) {
      if (!p) return 'N/A'
      const priorities = {
        'low': 'Low',
        'medium': 'Medium',
        'high': 'High',
        'critical': 'Critical'
      }
      return priorities[p] || p.charAt(0).toUpperCase() + p.slice(1)
    },
    
    formatStatus(s) {
      if (!s) return 'N/A'
      const statuses = {
        'new': 'New',
        'in_progress': 'In Progress',
        'waiting_for_client': 'Waiting for Client',
        'resolved': 'Resolved',
        'closed': 'Closed',
        'rejected': 'Rejected'
      }
      return statuses[s] || s
    },
    
    formatTicketType(t) {
      if (!t) return 'N/A'
      const types = {
        'support_issue': 'Support Issue',
        'feature_request': 'Feature Request',
        'bug': 'Bug',
        'billing': 'Billing',
        'deployment': 'Deployment',
        'other': 'Other'
      }
      return types[t] || t
    },
    
    formatDate(d) {
      if (!d) return 'N/A'
      return new Date(d).toLocaleDateString()
    },
    
    formatDateTime(d) {
      if (!d) return 'N/A'
      return new Date(d).toLocaleString()
    },
    
    formatFileSize(bytes) {
      if (!bytes) return '0 B'
      const units = ['B', 'KB', 'MB', 'GB']
      let size = bytes
      let unitIndex = 0
      while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024
        unitIndex++
      }
      return `${size.toFixed(1)} ${units[unitIndex]}`
    },
    
    formatFieldName(field) {
      if (!field) return 'Unknown'
      const fieldMap = {
        'title': 'Title',
        'description': 'Description',
        'priority': 'Priority',
        'status': 'Status',
        'assigned_to': 'Assigned To',
        'due_date': 'Due Date',
        'ticket_type': 'Ticket Type',
        'active': 'Active Status'
      }
      return fieldMap[field] || field
    },
    
    getUserInitials(user) {
      if (!user || !user.name) return '?'
      return user.name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2)
    },
    
    isImage(filename) {
      if (!filename) return false
      const ext = filename.split('.').pop().toLowerCase()
      return ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(ext)
    },
    
    isOverdue(date) {
      if (!date) return false
      return new Date(date) < new Date()
    }
  }
}
</script>

<style scoped>
.ticket-detail-container {
  padding: 24px;
  background: #f0fff0;
  min-height: 100vh;
}

.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.btn-back {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #f3f4f6;
  color: #2c5e2c;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-back:hover {
  background: #e5e7eb;
}

.back-icon {
  width: 16px;
  height: 16px;
}

.ticket-title {
  font-size: 24px;
  font-weight: 600;
  color: #2c5e2c;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn-edit, .btn-add {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-edit {
  background: #2c5e2c;
  color: white;
}

.btn-edit:hover {
  background: #1f4520;
}

.btn-add {
  background: #4fd14f;
  color: white;
}

.btn-add:hover {
  background: #3cb03c;
}

.btn-icon {
  width: 16px;
  height: 16px;
}

.detail-card {
  background: white;
  padding: 24px;
  border-radius: 12px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(44,94,44,0.1);
}

.ticket-status-bar {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.priority-badge, .status-badge, .active-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 500;
}

.priority-badge.low {
  background: #d1f0d1;
  color: #1f4520;
}

.priority-badge.medium {
  background: #a0e0a0;
  color: #144d14;
}

.priority-badge.high {
  background: #58c958;
  color: white;
}

.priority-badge.critical {
  background: #ff6961;
  color: white;
}

.status-badge.new {
  background: #d1f0d1;
  color: #1f4520;
}

.status-badge.in_progress {
  background: #a0e0a0;
  color: #144d14;
}

.status-badge.waiting_for_client {
  background: #fff3cd;
  color: #856404;
}

.status-badge.resolved {
  background: #cce5ff;
  color: #004085;
}

.status-badge.closed {
  background: #d6d8d9;
  color: #1b1e21;
}

.status-badge.rejected {
  background: #f8d7da;
  color: #721c24;
}

.active-badge.active {
  background: #d1f7d1;
  color: #0b5e0b;
  border: 1px solid #4fd14f;
}

.active-badge.inactive {
  background: #ffe5e5;
  color: #a10000;
  border: 1px solid #ff9999;
}
.error-message {
  margin-top: 8px;
  padding: 8px 12px;
  background-color: #fee2e2;
  border: 1px solid #ef4444;
  border-radius: 6px;
  color: #dc2626;
  font-size: 14px;
}

.ticket-content {
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid #e0e8e0;
}

.ticket-subject {
  font-size: 20px;
  font-weight: 600;
  color: #1f4520;
  margin-bottom: 12px;
}

.ticket-description {
  font-size: 15px;
  line-height: 1.6;
  color: #333;
  white-space: pre-wrap;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.detail-item label {
  font-size: 12px;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: block;
  margin-bottom: 4px;
}

.detail-item p {
  font-weight: 500;
  margin: 0;
  font-size: 15px;
}

.overdue {
  color: #dc3545;
  font-weight: 600;
}

.detail-section {
  background: white;
  padding: 24px;
  border-radius: 12px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(44,94,44,0.1);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h2 {
  font-size: 18px;
  color: #2c5e2c;
  margin: 0;
}

.comments-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.comment-card {
  border: 1px solid #e0e8e0;
  border-radius: 8px;
  padding: 16px;
  background: #f8fff8;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.comment-user {
  display: flex;
  gap: 12px;
  align-items: center;
}

.user-avatar {
  width: 36px;
  height: 36px;
  background: #2c5e2c;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
}

.comment-date {
  font-size: 12px;
  color: #666;
  margin-left: 8px;
}

.comment-text {
  margin: 0 0 8px 48px;
  font-size: 14px;
  line-height: 1.6;
  white-space: pre-wrap;
}

.comment-edit-info {
  margin-left: 48px;
  font-size: 11px;
  color: #999;
  font-style: italic;
}

.comment-actions {
  display: flex;
  gap: 8px;
}

.attachments-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.attachment-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px;
  border: 1px solid #e0e8e0;
  border-radius: 8px;
  background: #f8fff8;
  transition: all 0.2s;
}

.attachment-card:hover {
  background: #f0fff0;
  border-color: #2c5e2c;
}

.attachment-icon {
  flex-shrink: 0;
}

.file-icon {
  width: 24px;
  height: 24px;
  color: #2c5e2c;
}

.attachment-details {
  flex: 1;
  min-width: 0;
}

.attachment-name {
  display: block;
  font-weight: 500;
  color: #2c5e2c;
  text-decoration: none;
  margin-bottom: 4px;
  word-break: break-word;
}

.attachment-name:hover {
  text-decoration: underline;
}

.attachment-meta {
  font-size: 12px;
  color: #666;
}

.attachment-actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.history-card {
  padding: 12px;
  border-bottom: 1px solid #e0e8e0;
}

.history-card:last-child {
  border-bottom: none;
}

.history-header {
  margin-bottom: 8px;
}

.history-user {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.history-date {
  font-size: 12px;
  color: #666;
}

.history-text {
  margin: 0;
  font-size: 14px;
}

.changed-field {
  font-weight: 600;
  color: #2c5e2c;
}

.old-value {
  text-decoration: line-through;
  color: #dc3545;
  margin: 0 4px;
}

.new-value {
  font-weight: 600;
  color: #0b5e0b;
  margin: 0 4px;
}

.arrow {
  color: #666;
  margin: 0 4px;
}

.btn-icon-sm {
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px;
  border-radius: 4px;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-icon-sm:hover {
  background: #e0e8e0;
}

.text-red-600 {
  color: #dc3545;
}

.text-red-600:hover {
  background: #ffe5e5;
}

.action-icon-small {
  width: 16px;
  height: 16px;
}

.file-list {
  margin-top: 12px;
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #e0e8e0;
  border-radius: 6px;
  padding: 8px;
}

.file-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px;
  border-bottom: 1px solid #e0e8e0;
}

.file-item:last-child {
  border-bottom: none;
}

.btn-remove-file {
  background: none;
  border: none;
  color: #dc3545;
  font-size: 18px;
  cursor: pointer;
  padding: 0 4px;
}

.btn-remove-file:hover {
  color: #a10000;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 24px;
  border-radius: 12px;
  max-width: 500px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
}

.modal-content h3 {
  color: #2c5e2c;
  margin-bottom: 20px;
  font-size: 18px;
}

.delete-modal {
  max-width: 400px;
  text-align: center;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #2c5e2c;
  font-size: 14px;
}

.form-input {
  width: 100%;
  padding: 10px 12px;
  border: 2px solid #e0e8e0;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #4fd14f;
}

textarea.form-input {
  resize: vertical;
  min-height: 100px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.btn-save, .btn-confirm-delete, .btn-cancel {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-save {
  background: #2c5e2c;
  color: white;
}

.btn-save:hover:not(:disabled) {
  background: #1f4520;
}

.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-confirm-delete {
  background: #dc3545;
  color: white;
}

.btn-confirm-delete:hover:not(:disabled) {
  background: #bb2d3b;
}

.btn-cancel {
  background: #6c757d;
  color: white;
}

.btn-cancel:hover {
  background: #5a6268;
}

.empty-text {
  color: #777;
  font-style: italic;
  text-align: center;
  padding: 32px;
  background: #f8fff8;
  border-radius: 8px;
  border: 2px dashed #e0e8e0;
}

@media (max-width: 768px) {
  .detail-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .header-left {
    flex-direction: column;
    align-items: flex-start;
    width: 100%;
  }
  
  .btn-back {
    width: 100%;
    justify-content: center;
  }
  
  .detail-grid {
    grid-template-columns: 1fr;
  }
  
  .attachment-card {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .attachment-actions {
    width: 100%;
    justify-content: flex-end;
  }
  
  .comment-header {
    flex-direction: column;
    gap: 8px;
  }
  
  .comment-user {
    width: 100%;
  }
  
  .comment-text {
    margin-left: 0;
  }
}
</style>