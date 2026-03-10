<template>
  <AuthenticatedLayout>
    <div class="tickets-container">
      <div class="tickets-header">
        <h1 class="tickets-title">Tickets Management</h1>
        <button @click="createTicket" class="btn-create">
          <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
          </svg>
          Create New Ticket
        </button>
      </div>

      <!-- Filters -->
      <div class="filters-wrapper">
        <input
          type="text"
          v-model="localFilters.search"
          placeholder="Search tickets..."
          @keyup.enter="applyFilters"
          class="filter-input"
        />
        <select v-model="localFilters.priority" class="filter-select">
          <option value="">All Priorities</option>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          <option value="critical">Critical</option>
        </select>
        <select v-model="localFilters.status" class="filter-select">
          <option value="">All Statuses</option>
          <option value="new">New</option>
          <option value="in_progress">In Progress</option>
          <option value="waiting_for_client">Waiting for Client</option>
          <option value="resolved">Resolved</option>
          <option value="closed">Closed</option>
          <option value="rejected">Rejected</option>
        </select>
        <select v-model="localFilters.active" class="filter-select">
          <option value="">All Tickets</option>
          <option value="1">Active Only</option>
          <option value="0">Inactive Only</option>
        </select>
        <button @click="applyFilters" class="btn-filter">Apply</button>
        <button @click="resetFilters" class="btn-reset">Reset</button>
      </div>

      <!-- Tickets Table -->
      <div class="table-wrapper">
        <table class="tickets-table">
          <thead>
            <tr>
              <th @click="sortBy('id')" class="sortable">ID <span v-if="sortField==='id'">{{ sortDirection==='asc'?'↑':'↓' }}</span></th>
              <th @click="sortBy('ticket_number')" class="sortable">Ticket # <span v-if="sortField==='ticket_number'">{{ sortDirection==='asc'?'↑':'↓' }}</span></th>
              <th>Client</th>
              <th>Type</th>
              <th>Title</th>
              <th>Priority</th>
              <th>Status</th>
              
              <th>Comments</th>
              <th>Attachments</th>
              <th @click="sortBy('active')" class="sortable text-center">Active <span v-if="sortField==='active'">{{ sortDirection==='asc'?'↑':'↓' }}</span></th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover-row">
              <td>{{ ticket.id }}</td>
              <td>{{ ticket.ticket_number }}</td>
              <td>{{ ticket.client?.organization_name ?? 'N/A' }}</td>
              <td>{{ formatTicketType(ticket.ticket_type) }}</td>
              <td>
                <span class="ticket-title">{{ ticket.title }}</span>
                <div v-if="ticket.description" class="ticket-desc">{{ truncateText(ticket.description,50) }}</div>
              </td>
              <td><span :class="['priority-badge', ticket.priority]">{{ formatPriority(ticket.priority) }}</span></td>
              <td><span :class="['status-badge', ticket.status]">{{ formatStatus(ticket.status) }}</span></td>
              
              <td>
                <button @click="viewTicketComments(ticket.id)" class="btn-comments">
                  <svg class="comment-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                  </svg>
                  {{ ticket.comments_count || 0 }}
                </button>
              </td>
              <td>
                <button @click="viewTicketAttachments(ticket.id)" class="btn-attachments">
                  <svg class="attachment-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"/>
                  </svg>
                  {{ ticket.attachments_count || 0 }}
                </button>
              </td>
              <td class="text-center">
                <button 
                  @click="toggleActive(ticket)" 
                  class="active-toggle-btn"
                  :class="{ 'active': ticket.active, 'inactive': !ticket.active }"
                  :title="ticket.active ? 'Click to deactivate' : 'Click to activate'"
                  :disabled="processingTicketId === ticket.id"
                >
                  <span class="active-indicator" :class="{ 'active-dot': ticket.active, 'inactive-dot': !ticket.active }"></span>
                  {{ ticket.active ? 'Active' : 'Inactive' }}
                </button>
              </td>
              <td class="text-center actions-cell">
                 <!-- View Ticket -->
  <button @click="viewTicket(ticket.id)" class="btn-icon-action" title="View Ticket">
    <svg class="action-icon" viewBox="0 0 20 20" fill="currentColor">
      <path d="M10 3C5 3 1.73 7.11 1 10c.73 2.89 4 7 9 7s8.27-4.11 9-7c-.73-2.89-4-7-9-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/>
    </svg>
  </button>
                <button @click="editTicket(ticket.id)" class="btn-icon-action" title="Edit">
                  <svg class="action-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                  </svg>
                </button>
                <button @click="deleteTicket(ticket.id)" class="btn-icon-action btn-delete-icon" title="Delete">
                  <svg class="action-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                </button>
              </td>
            </tr>

            <tr v-if="!tickets.data || tickets.data.length === 0">
              <td colspan="15" class="text-center py-6 text-gray-500">No tickets found</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="tickets.last_page > 1" class="pagination-wrapper">
        <button
          v-for="link in tickets.links"
          :key="link.label"
          :disabled="!link.url"
          @click="changePage(link.page)"
          class="pagination-btn"
          :class="{ active: link.active }"
          v-html="link.label"
        ></button>
      </div>

      <div class="tickets-count">
        Showing {{ tickets.from }} to {{ tickets.to }} of {{ tickets.total }} entries
      </div>

      <!-- Delete Modal -->
      <div v-if="showDeleteModal" class="modal-overlay">
        <div class="modal-content">
          <h3>Confirm Delete</h3>
          <p>Are you sure you want to delete this ticket? This cannot be undone.</p>
          <div class="modal-actions">
            <button @click="confirmDelete" class="btn-confirm-delete">Delete</button>
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
    tickets: Object,
    filters: Object 
  },
  data(){ 
    return {
      localFilters: { 
        search: this.filters?.search || '', 
        priority: this.filters?.priority || '', 
        status: this.filters?.status || '',
        active: this.filters?.active || ''
      },
      sortField: this.filters?.sort_field || 'id', 
      sortDirection: this.filters?.sort_direction || 'desc',
      showDeleteModal: false, 
      ticketToDelete: null,
      processingTicketId: null
    }
  },
  methods: {
    viewTicket(id) {
  router.visit(`/tickets/${id}/detail`);
},
    createTicket() {
      router.visit('/tickets/create');
    },
    
    editTicket(id) {
      router.visit(`/tickets/${id}/edit`);
    },
    
    viewTicketComments(id) {
      router.visit(`/tickets/${id}/comments`);
    },
    
    viewTicketAttachments(id) {
      router.visit(`/tickets/${id}/attachments`);
    },
    
    deleteTicket(id) {
      this.ticketToDelete = id; 
      this.showDeleteModal = true;
    },
    
    toggleActive(ticket) {
      if (this.processingTicketId) return;
      
      this.processingTicketId = ticket.id;
      
      router.put(`/tickets/${ticket.id}/toggle-active`, {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          ticket.active = !ticket.active;
          this.processingTicketId = null;
        },
        onError: (errors) => {
          console.error('Toggle failed:', errors);
          alert('Failed to toggle ticket status. Please try again.');
          this.processingTicketId = null;
        }
      });
    },
    
    confirmDelete() {
      router.delete(`/tickets/${this.ticketToDelete}`, {
        preserveScroll: true,
        onSuccess: () => {
          this.showDeleteModal = false;
          this.ticketToDelete = null;
        },
        onError: (errors) => {
          alert('Failed to delete ticket: ' + (errors.message || 'Unknown error'));
        }
      });
    },
    
    cancelDelete() {
      this.showDeleteModal = false; 
      this.ticketToDelete = null;
    },
    
    applyFilters() {
      router.get('/tickets', {
        ...this.localFilters, 
        sort_field: this.sortField, 
        sort_direction: this.sortDirection
      }, {
        preserveState: true,
        preserveScroll: true
      });
    },
    
    resetFilters() {
      this.localFilters = { search: '', priority: '', status: '', active: '' }; 
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
      if (page >= 1 && page <= this.tickets.last_page) { 
        router.get('/tickets', {
          ...this.localFilters, 
          page, 
          sort_field: this.sortField, 
          sort_direction: this.sortDirection
        }, {
          preserveState: true,
          preserveScroll: true
        }); 
      } 
    },
    
    formatTicketType(t) { 
      const types = {
        'support_issue': 'Support Issue',
        'feature_request': 'Feature Request',
        'bug': 'Bug',
        'billing': 'Billing',
        'deployment': 'Deployment',
        'other': 'Other'
      }; 
      return types[t] || t; 
    },
    
    formatPriority(p) { 
      return p ? p.charAt(0).toUpperCase() + p.slice(1) : 'N/A'; 
    },
    
    formatStatus(s) { 
      const statuses = {
        'new': 'New',
        'in_progress': 'In Progress',
        'waiting_for_client': 'Waiting for Client',
        'resolved': 'Resolved',
        'closed': 'Closed',
        'rejected': 'Rejected'
      }; 
      return statuses[s] || s; 
    },
    
    formatDate(d) { 
      return d ? new Date(d).toLocaleDateString() : 'N/A'; 
    },
    
    truncateText(t, l) { 
      return t && t.length > l ? t.substring(0, l) + '...' : t; 
    },
    
    isOverdue(d) { 
      return d ? new Date(d) < new Date() : false; 
    }
  }
}
</script>

<style scoped>
.tickets-container {
  padding: 24px;
  min-height: 100vh;
  background: #f0fff0;
  border-radius: 12px;
}
.tickets-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}
.tickets-title {
  font-size: 28px;
  font-weight: 600;
  color: #2c5e2c;
}
.btn-create {
  display: flex;
  align-items: center;
  gap: 3px;
  background: #4fd14f;
  color: white;
  border: none;
  padding: 4px 8px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 13px;
}
.btn-create:hover {
  background: #1f4520;
}
.btn-icon {
  width: 16px;
  height: 16px;
}
.filters-wrapper {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 16px;
}
.filter-input, .filter-select {
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #ccc;
}
.btn-filter, .btn-reset {
  padding: 8px 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}
.btn-filter {
  background: #2c5e2c;
  color: white;
}
.btn-reset {
  background: #ccc;
}
.btn-filter:hover {
  background: #1f4520;
}
.table-wrapper {
  overflow-x: auto;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(44,94,44,0.1);
}
.tickets-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1300px;
}
.tickets-table th, .tickets-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #e0e8e0;
  font-size: 14px;
}
.tickets-table th {
  background: #f3f4f6;
  color: #2c5e2c;
  font-weight: 600;
  text-align: left;
}
.hover-row:hover {
  background: #f8fff8;
  transition: 0.2s;
}
.ticket-title {
  font-weight: 600;
}
.ticket-desc {
  font-size: 13px;
  color: #555;
}
.btn-comments, .btn-attachments {
  background: #f0fff0;
  border: 1px solid #2c5e2c;
  border-radius: 20px;
  padding: 6px 10px;
  cursor: pointer;
  font-size: 12px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s ease;
  color: #2c5e2c;
}
.btn-attachments {
  color: #2c5e2c;
}
.btn-comments:hover, .btn-attachments:hover {
  background: #2c5e2c;
  color: white;
}
.btn-comments:hover svg, .btn-attachments:hover svg {
  fill: white;
}
.comment-icon, .attachment-icon {
  width: 14px;
  height: 14px;
}
.actions-cell {
  white-space: nowrap;
  display: flex;
  justify-content: center;
  gap: 4px;
}
.btn-icon-action {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  transition: 0.2s;
}
.btn-icon-action:hover {
  background: #d1f0d1;
}
.btn-delete-icon:hover {
  background: #f8d6d6;
}
.action-icon {
  width: 18px;
  height: 18px;
}

/* Active Toggle Button Styles */
.active-toggle-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border: none;
  border-radius: 20px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.2s ease;
  min-width: 80px;
  justify-content: center;
}

.active-toggle-btn.active {
  background-color: #d1f7d1;
  color: #0b5e0b;
  border: 1px solid #4fd14f;
}

.active-toggle-btn.inactive {
  background-color: #ffe5e5;
  color: #a10000;
  border: 1px solid #ff9999;
}

.active-toggle-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.active-toggle-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.active-indicator {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.active-dot {
  background-color: #2c5e2c;
  box-shadow: 0 0 4px #2c5e2c;
}

.inactive-dot {
  background-color: #dc3545;
  box-shadow: 0 0 4px #dc3545;
}

.pagination-wrapper {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 16px;
  flex-wrap: wrap;
}
.pagination-btn {
  padding: 8px 14px;
  border: 1px solid #e0e8e0;
  border-radius: 6px;
  cursor: pointer;
  background: white;
}
.pagination-btn.active {
  background: #2c5e2c;
  color: white;
  border-color: #2c5e2c;
}
.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.tickets-count {
  text-align: center;
  margin-top: 16px;
  font-size: 14px;
  color: #6c757d;
}
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal-content {
  background: white;
  padding: 24px;
  border-radius: 12px;
  max-width: 400px;
  width: 100%;
  text-align: center;
}
.modal-actions {
  margin-top: 16px;
  display: flex;
  justify-content: center;
  gap: 12px;
}
.btn-confirm-delete {
  background: #dc3545;
  color: white;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
}
.btn-cancel {
  background: #ccc;
  color: #333;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
}
.priority-badge.low {
  background: #d1f0d1;
  color: #1f4520;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 12px;
}
.priority-badge.medium {
  background: #a0e0a0;
  color: #144d14;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 12px;
}
.priority-badge.high {
  background: #58c958;
  color: white;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 12px;
}
.priority-badge.critical {
  background: #ff6961;
  color: white;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 12px;
}
.status-badge.new {
  background: #d1f0d1;
  color: #1f4520;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 12px;
}
.status-badge.in_progress {
  background: #a0e0a0;
  color: #144d14;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 12px;
}
.status-badge.waiting_for_client {
  background: #fff3cd;
  color: #856404;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 12px;
}
.status-badge.resolved {
  background: #cce5ff;
  color: #004085;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 12px;
}
.status-badge.closed {
  background: #d6d8d9;
  color: #1b1e21;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 12px;
}
.status-badge.rejected {
  background: #f8d7da;
  color: #721c24;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 12px;
}
.overdue {
  color: #dc3545;
  font-weight: 600;
}
.text-center {
  text-align: center;
}
.sortable {
  cursor: pointer;
  user-select: none;
}
.sortable:hover {
  background: #e8f0e8;
}
</style>