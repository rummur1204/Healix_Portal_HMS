<template>
  <div class="tickets-container">
    <!-- Header Section -->
    <div class="header-section">
      <h1 class="page-title">Ticket Attachments</h1>
      <button @click="createAttachment" class="btn-create">
        <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
            clip-rule="evenodd" />
        </svg>
        Upload Attachment
      </button>
    </div>

    <!-- Filters -->
    <div class="filters-section">
      <div class="filter-group">
        <input
          type="text"
          v-model="filters.search"
          placeholder="Search by file name..."
          class="filter-input"
          @keyup.enter="applyFilters"
        />
        <button @click="applyFilters" class="btn-filter">Apply</button>
        <button @click="resetFilters" class="btn-reset">Reset</button>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="$page.props.flash?.success" class="alert alert-success">
      {{ $page.props.flash.success }}
    </div>
    <div v-if="$page.props.flash?.error" class="alert alert-error">
      {{ $page.props.flash.error }}
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="tickets-table">
        <thead>
          <tr>
            <th @click="sortBy('id')" class="sortable">
              ID
              <span v-if="sortField === 'id'" class="sort-icon">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
            </th>
            <th>Ticket #</th>
            <th @click="sortBy('file_name')" class="sortable">
              File Name
              <span v-if="sortField === 'file_name'" class="sort-icon">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
            </th>
            <th>File Type</th>
            <th>Size</th>
            <th>Uploaded By</th>
            <th @click="sortBy('created_at')" class="sortable">
              Created At
              <span v-if="sortField === 'created_at'" class="sort-icon">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
            </th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in attachments.data" :key="item.id" class="ticket-row">
            <td>{{ item.id }}</td>
            <td class="ticket-number">{{ item.ticket?.ticket_number || 'N/A' }}</td>
            <td>
              <a :href="`/storage/${item.file_path}`" target="_blank" class="file-link">
                {{ item.file_name }}
              </a>
            </td>
            <td>{{ item.file_type || 'N/A' }}</td>
            <td>{{ formatSize(item.file_size) }}</td>
            <td>{{ item.uploaded_by?.name || 'N/A' }}</td>
            <td>{{ formatDate(item.created_at) }}</td>
            <td class="actions-cell">
              <button @click="editAttachment(item.id)" class="btn-edit" title="Edit">✏️</button>
              <button @click="deleteAttachment(item.id)" class="btn-delete" title="Delete">🗑️</button>
            </td>
          </tr>
          <tr v-if="!attachments.data || attachments.data.length === 0">
            <td colspan="8" class="no-records">No attachments found</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-section" v-if="attachments.last_page > 1">
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

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="cancelDelete">
      <div class="modal-content">
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete this attachment? This action cannot be undone.</p>
        <div class="modal-actions">
          <button @click="confirmDelete" class="btn-confirm-delete" :disabled="isDeleting">
            {{ isDeleting ? 'Deleting...' : 'Delete' }}
          </button>
          <button @click="cancelDelete" class="btn-cancel" :disabled="isDeleting">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { router } from '@inertiajs/vue3';

export default {
  name: 'TicketAttachmentsIndex',
  props: {
    attachments: {
      type: Object,
      required: true
    },
    filters: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      filters: {
        search: this.filters.search || ''
      },
      sortField: this.filters.sort_field || 'id',
      sortDirection: this.filters.sort_direction || 'desc',
      showDeleteModal: false,
      attachmentToDelete: null,
      isDeleting: false
    };
  },
  methods: {
    createAttachment() {
      router.visit('/ticket-attachments/create');
    },
    
    editAttachment(id) {
      router.visit(`/ticket-attachments/${id}/edit`);
    },
    
    deleteAttachment(id) {
      this.attachmentToDelete = id;
      this.showDeleteModal = true;
    },
    
    cancelDelete() {
      this.showDeleteModal = false;
      this.attachmentToDelete = null;
      this.isDeleting = false;
    },

    confirmDelete() {
      if (!this.attachmentToDelete) return;
      
      this.isDeleting = true;
      
      router.delete(`/ticket-attachments/${this.attachmentToDelete}`, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          this.showDeleteModal = false;
          this.attachmentToDelete = null;
          this.isDeleting = false;
        },
        onError: (errors) => {
          console.error('Delete failed:', errors);
          let errorMessage = 'Failed to delete attachment. Please try again.';
          
          if (typeof errors === 'string') {
            errorMessage = errors;
          } else if (errors.message) {
            errorMessage = errors.message;
          }
          
          alert(errorMessage);
          this.showDeleteModal = false;
          this.attachmentToDelete = null;
          this.isDeleting = false;
        },
        onFinish: () => {
          this.showDeleteModal = false;
          this.attachmentToDelete = null;
          this.isDeleting = false;
        }
      });
    },

    applyFilters() {
      router.get('/ticket-attachments', 
        {
          search: this.filters.search,
          sort_field: this.sortField,
          sort_direction: this.sortDirection
        },
        {
          preserveState: true,
          preserveScroll: true,
          replace: true
        }
      );
    },
    
    resetFilters() {
      this.filters.search = '';
      this.sortField = 'id';
      this.sortDirection = 'desc';
      this.applyFilters();
    },
    
    sortBy(field) {
      if (this.sortField === field) {
        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortField = field;
        this.sortDirection = 'asc';
      }
      this.applyFilters();
    },
    
    changePage(page) {
      router.get('/ticket-attachments', 
        {
          page: page,
          search: this.filters.search,
          sort_field: this.sortField,
          sort_direction: this.sortDirection
        },
        {
          preserveState: true,
          preserveScroll: true,
          replace: true
        }
      );
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
  }
};
</script>

<style scoped>
.tickets-container {
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

.btn-create {
  background: #4CAF50;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background 0.3s ease;
}

.btn-create:hover {
  background: #45a049;
}

.btn-create:active {
  transform: translateY(1px);
}

.btn-icon {
  width: 20px;
  height: 20px;
}

.filters-section {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(44, 94, 44, 0.1);
}

.filter-group {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.filter-input {
  flex: 1;
  min-width: 250px;
  padding: 10px 16px;
  border: 2px solid #e0e8e0;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
}

.filter-input:focus {
  outline: none;
  border-color: #4CAF50;
  box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.btn-filter {
  background: #4CAF50;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.3s ease;
}

.btn-filter:hover {
  background: #45a049;
}

.btn-reset {
  background: #e8f0e8;
  color: #2c5e2c;
  border: 2px solid #2c5e2c;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-reset:hover {
  background: #d0e0d0;
}

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

.table-responsive {
  background: white;
  border-radius: 12px;
  overflow-x: auto;
  box-shadow: 0 2px 8px rgba(44, 94, 44, 0.1);
}

.tickets-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1200px;
}

.tickets-table th {
  background: #e8f0e8;
  color: #2c5e2c;
  font-weight: 600;
  font-size: 14px;
  padding: 16px;
  text-align: left;
  border-bottom: 2px solid #2c5e2c;
}

.tickets-table td {
  padding: 16px;
  border-bottom: 1px solid #e0e8e0;
  color: #333;
  font-size: 14px;
}

.sortable {
  cursor: pointer;
  user-select: none;
  position: relative;
}

.sortable:hover {
  background: #d0e0d0;
}

.sort-icon {
  margin-left: 4px;
  font-weight: bold;
}

.ticket-row:hover {
  background: #f5faf5;
}

.ticket-number {
  font-family: monospace;
  font-weight: 500;
  color: #2c5e2c;
}

.file-link {
  color: #4CAF50;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}

.file-link:hover {
  color: #2c5e2c;
  text-decoration: underline;
}

.actions-cell {
  white-space: nowrap;
}

.btn-edit, .btn-delete {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  margin: 0 4px;
  border-radius: 6px;
  transition: all 0.3s ease;
  font-size: 16px;
}

.btn-edit {
  color: #ffc107;
}

.btn-edit:hover {
  background: #fff3cd;
  transform: scale(1.1);
}

.btn-delete {
  color: #dc3545;
}

.btn-delete:hover {
  background: #f8d7da;
  transform: scale(1.1);
}

.btn-edit:active, .btn-delete:active {
  transform: scale(0.95);
}

.no-records {
  text-align: center;
  padding: 48px;
  color: #666;
  font-style: italic;
}

.pagination-section {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 24px;
  gap: 16px;
}

.pagination-btn {
  background: white;
  border: 2px solid #4CAF50;
  color: #4CAF50;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: #4CAF50;
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(76, 175, 80, 0.2);
}

.pagination-btn:active:not(:disabled) {
  transform: translateY(0);
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #2c5e2c;
  font-weight: 500;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  background: white;
  border-radius: 12px;
  padding: 24px;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    transform: translateY(-20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-content h3 {
  color: #dc3545;
  margin: 0 0 16px 0;
  font-size: 20px;
}

.modal-content p {
  color: #666;
  margin-bottom: 24px;
  line-height: 1.5;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-confirm-delete {
  background: #dc3545;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-confirm-delete:hover:not(:disabled) {
  background: #c82333;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
}

.btn-confirm-delete:active:not(:disabled) {
  transform: translateY(0);
}

.btn-confirm-delete:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-cancel {
  background: #e8f0e8;
  color: #2c5e2c;
  border: 2px solid #2c5e2c;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel:hover:not(:disabled) {
  background: #d0e0d0;
  transform: translateY(-1px);
}

.btn-cancel:active:not(:disabled) {
  transform: translateY(0);
}

.btn-cancel:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Responsive Design */
@media (max-width: 768px) {
  .tickets-container {
    padding: 16px;
  }

  .header-section {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }

  .page-title {
    font-size: 24px;
    text-align: center;
  }

  .btn-create {
    justify-content: center;
  }

  .filter-group {
    flex-direction: column;
  }

  .filter-input {
    min-width: 100%;
  }

  .pagination-section {
    flex-wrap: wrap;
  }
}
</style>