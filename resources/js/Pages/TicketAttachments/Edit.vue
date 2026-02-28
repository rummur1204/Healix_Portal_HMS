<template>
  <div class="edit-container">
    <!-- Header Section -->
    <div class="header-section">
      <h1 class="page-title">Edit Attachment</h1>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="$page.props.flash?.success" class="alert alert-success">
      {{ $page.props.flash.success }}
    </div>
    <div v-if="$page.props.flash?.error" class="alert alert-error">
      {{ $page.props.flash.error }}
    </div>

    <!-- Edit Form -->
    <div class="form-container">
      <form @submit.prevent="submitForm">
        <!-- Ticket Selection -->
        <div class="form-row">
          <label for="ticket_id">Ticket Number:</label>
          <select
            id="ticket_id"
            v-model="form.ticket_id"
            class="filter-input"
            :class="{ 'error-input': form.errors.ticket_id }"
            required
          >
            <option value="">Select a ticket</option>
            <option v-for="ticket in tickets" :key="ticket.id" :value="ticket.id">
              {{ ticket.ticket_number }}
            </option>
          </select>
          <div v-if="form.errors.ticket_id" class="error-message">
            {{ form.errors.ticket_id }}
          </div>
        </div>

        <!-- File Information (Read-only) -->
        <div class="form-row">
          <label>File Name:</label>
          <div class="readonly-field">{{ attachment.file_name }}</div>
        </div>

        <div class="form-row">
          <label>File Type:</label>
          <div class="readonly-field">{{ attachment.file_type || 'N/A' }}</div>
        </div>

        <div class="form-row">
          <label>File Size:</label>
          <div class="readonly-field">{{ formatSize(attachment.file_size) }}</div>
        </div>

        <div class="form-row">
          <label>Uploaded By:</label>
          <div class="readonly-field uploaded-by">
            <span class="user-icon">👤</span>
            {{ getUploadedByName }}
          </div>
        </div>

        <div class="form-row">
          <label>Uploaded At:</label>
          <div class="readonly-field">{{ formatDate(attachment.created_at) }}</div>
        </div>

        <!-- File Preview/Download -->
        <div class="form-row" v-if="attachment.file_path">
          <label>File:</label>
          <div class="readonly-field file-preview">
            <a 
              :href="getFileUrl" 
              target="_blank" 
              class="file-link"
              :download="attachment.file_name"
            >
              <span class="file-icon">📄</span>
              {{ attachment.file_name }}
              <span class="download-icon">⬇️</span>
            </a>
            <button 
              @click="downloadFile" 
              class="btn-download"
              type="button"
              title="Download file"
            >
              Download
            </button>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
          <button type="submit" class="btn-create" :disabled="form.processing">
            {{ form.processing ? 'Updating...' : 'Update Attachment' }}
          </button>
          <button type="button" @click="cancel" class="btn-reset" :disabled="form.processing">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { router, useForm } from '@inertiajs/vue3';

export default {
  name: 'TicketAttachmentsEdit',
  props: {
    attachment: {
      type: Object,
      required: true
    },
    tickets: {
      type: Array,
      required: true
    }
  },
  
  setup(props) {
    const form = useForm({
      ticket_id: props.attachment.ticket_id || ''
    });

    return { form };
  },

  computed: {
    // Get uploaded by name with fallback
    getUploadedByName() {
      if (this.attachment.uploaded_by) {
        return this.attachment.uploaded_by.name || 'Unknown User';
      }
      
      // Check if uploaded_by_user_id exists but user data is missing
      if (this.attachment.uploaded_by_user_id) {
        return `User ID: ${this.attachment.uploaded_by_user_id}`;
      }
      
      return 'N/A';
    },

    // Get file URL with proper path
    getFileUrl() {
      if (!this.attachment.file_path) return '#';
      
      // Remove any duplicate 'storage' in the path
      let filePath = this.attachment.file_path;
      if (filePath.startsWith('public/')) {
        filePath = filePath.replace('public/', '');
      }
      if (filePath.startsWith('/storage/')) {
        filePath = filePath.replace('/storage/', '');
      }
      
      return `/storage/${filePath}`;
    },

    // Check if file exists and is accessible
    canViewFile() {
      return this.attachment.file_path && this.attachment.file_path.trim() !== '';
    },

    // Check if form has any changes
    hasChanges() {
      return this.form.ticket_id !== this.attachment.ticket_id;
    }
  },

  methods: {
    submitForm() {
      this.form.put(`/ticket-attachments/${this.attachment.id}`, {
        onSuccess: () => {
          // Redirect to index page on success
          router.visit('/ticket-attachments', {
            preserveScroll: true
          });
        },
        onError: (errors) => {
          console.error('Error updating attachment:', errors);
          
          // Handle specific error cases
          if (errors.ticket_id) {
            // Focus on the ticket select field
            document.getElementById('ticket_id')?.focus();
          }
        }
      });
    },
    
    cancel() {
      if (this.form.processing) return;
      
      // Check if there are unsaved changes
      if (this.hasChanges) {
        if (confirm('You have unsaved changes. Are you sure you want to leave?')) {
          router.visit('/ticket-attachments');
        }
      } else {
        router.visit('/ticket-attachments');
      }
    },

    // Download file method
    downloadFile() {
      if (!this.canViewFile) {
        alert('File not available for download');
        return;
      }

      // Create a temporary link and click it
      const link = document.createElement('a');
      link.href = this.getFileUrl;
      link.download = this.attachment.file_name || 'download';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },

    // Open file in new tab
    viewFile() {
      if (!this.canViewFile) {
        alert('File not available for viewing');
        return;
      }
      window.open(this.getFileUrl, '_blank');
    },
    
    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    formatSize(size) {
      if (!size) return '0 KB';
      
      const bytes = parseInt(size);
      if (bytes < 1024) return bytes + ' B';
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
      return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }
  },

  // Warn user if they try to leave with unsaved changes
  beforeUnmount() {
    window.onbeforeunload = null;
  },

  mounted() {
    // Set up beforeunload warning for unsaved changes
    window.onbeforeunload = (e) => {
      if (this.hasChanges && this.form.processing === false) {
        e.preventDefault();
        e.returnValue = '';
      }
    };

    // Debug: Log attachment data to console
    console.log('Attachment data:', this.attachment);
  }
};
</script>

<style scoped>
.edit-container {
  padding: 24px;
  background: #f0f7f0;
  min-height: 100vh;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-title {
  font-size: 28px;
  font-weight: 600;
  color: #2c5e2c;
  margin: 0;
}

/* Alert Messages */
.alert {
  padding: 12px 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-weight: 500;
}

.alert-success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.alert-error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.form-container {
  background: white;
  border-radius: 12px;
  padding: 32px;
  max-width: 600px;
  margin: 0 auto;
  box-shadow: 0 2px 8px rgba(44, 94, 44, 0.1);
}

.form-row {
  margin-bottom: 20px;
  position: relative;
}

.form-row label {
  display: block;
  font-weight: 600;
  color: #2c5e2c;
  font-size: 14px;
  margin-bottom: 8px;
}

.filter-input {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e0e8e0;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
  background: white;
}

.filter-input:focus {
  outline: none;
  border-color: #4CAF50;
  box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.filter-input.error-input {
  border-color: #dc3545;
}

.filter-input.error-input:focus {
  border-color: #dc3545;
  box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
}

.readonly-field {
  width: 100%;
  padding: 12px 16px;
  background: #f5f5f5;
  border-radius: 8px;
  font-size: 14px;
  color: #666;
  border: 1px solid #e0e0e0;
}

.uploaded-by {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-icon {
  font-size: 16px;
}

.file-preview {
  padding: 8px 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.file-link {
  color: #4CAF50;
  text-decoration: none;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.3s ease;
  flex: 1;
  min-width: 200px;
}

.file-link:hover {
  background: #e8f5e9;
  color: #2c5e2c;
}

.file-icon {
  font-size: 18px;
}

.download-icon {
  font-size: 14px;
  opacity: 0.7;
  margin-left: auto;
}

.btn-download {
  background: #4CAF50;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.btn-download:hover {
  background: #45a049;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(76, 175, 80, 0.2);
}

.btn-download:active {
  transform: translateY(0);
}

.error-message {
  color: #dc3545;
  font-size: 12px;
  margin-top: 4px;
  font-weight: 500;
}

.form-actions {
  display: flex;
  gap: 16px;
  margin-top: 32px;
  justify-content: flex-end;
}

.btn-create {
  background: #4CAF50;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 160px;
}

.btn-create:hover:not(:disabled) {
  background: #45a049;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(76, 175, 80, 0.2);
}

.btn-create:active:not(:disabled) {
  transform: translateY(0);
}

.btn-create:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-reset {
  background: #e8f0e8;
  color: #2c5e2c;
  border: 2px solid #2c5e2c;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 160px;
}

.btn-reset:hover:not(:disabled) {
  background: #d0e0d0;
  transform: translateY(-1px);
}

.btn-reset:active:not(:disabled) {
  transform: translateY(0);
}

.btn-reset:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Responsive Design */
@media (max-width: 768px) {
  .edit-container {
    padding: 16px;
  }

  .page-title {
    font-size: 24px;
  }

  .form-container {
    padding: 20px;
  }

  .file-preview {
    flex-direction: column;
    align-items: stretch;
  }

  .file-link {
    justify-content: center;
  }

  .btn-download {
    width: 100%;
  }

  .form-actions {
    flex-direction: column;
  }

  .btn-create,
  .btn-reset {
    width: 100%;
    min-width: auto;
  }
}

/* Loading State */
.btn-create.loading {
  position: relative;
  color: transparent;
}

.btn-create.loading::after {
  content: '';
  position: absolute;
  width: 16px;
  height: 16px;
  top: 50%;
  left: 50%;
  margin-left: -8px;
  margin-top: -8px;
  border: 2px solid white;
  border-radius: 50%;
  border-top-color: transparent;
  animation: spinner 0.6s linear infinite;
}

@keyframes spinner {
  to { transform: rotate(360deg); }
}

/* Debug info - remove in production */
.debug-info {
  font-size: 11px;
  color: #999;
  margin-top: 4px;
  padding: 4px;
  background: #f0f0f0;
  border-radius: 4px;
}
</style>