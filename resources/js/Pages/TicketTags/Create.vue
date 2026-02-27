

<template>
  <div class="tickets-container">
    <div class="header-section">
      <h1 class="page-title">Create Tag</h1>
    </div>

    <form @submit.prevent="submitForm">
      <div class="filters-section">
        <div class="filter-group-vertical">
          <label>
            Name
            <input v-model="form.name" type="text" required class="filter-input" placeholder="Tag Name" />
          </label>

          <label>
            Color
            <input v-model="form.color" type="color" class="filter-input" />
          </label>

          <label class="checkbox-label">
            <input type="checkbox" v-model="form.is_active" />
            Active
          </label>

          <button type="submit" class="btn-create">Submit</button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    return {
      form: { name: '', color: '#3B82F6', is_active: true }
    };
  },
  methods: {
    async submitForm() {
      await axios.post('/ticket-tags', this.form);
      this.$inertia.visit('/ticket-tags');
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
    background: #69f069;
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
    background: #69f369;
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
    transition: border-color 0.3s ease;
}

.filter-input:focus {
    outline: none;
    border-color: #69d169;
}

.filter-select {
    padding: 10px 16px;
    border: 2px solid #e0e8e0;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    cursor: pointer;
    min-width: 150px;
}

.filter-select:focus {
    outline: none;
    border-color: #69f869;
}

.btn-filter {
    background: #69f369;
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
    background: #1e4a1e;
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

.title-cell {
    max-width: 300px;
}

.ticket-title {
    font-weight: 500;
    color: #2c5e2c;
    display: block;
    margin-bottom: 4px;
}

.description-preview {
    font-size: 12px;
    color: #666;
    display: block;
}

.priority-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
}

.priority-badge.low {
    background: #e8f0e8;
    color: #2c5e2c;
}

.priority-badge.medium {
    background: #fff3cd;
    color: #856404;
}

.priority-badge.high {
    background: #ffe0d0;
    color: #c43e1e;
}

.priority-badge.critical {
    background: #f8d7da;
    color: #721c24;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.open {
    background: #cce5ff;
    color: #004085;
}

.status-badge.in_progress {
    background: #fff3cd;
    color: #856404;
}

.status-badge.resolved {
    background: #d4edda;
    color: #69ff8c;
}

.status-badge.closed {
    background: #e2e3e5;
    color: #383d41;
}

.overdue {
    color: #dc3545;
    font-weight: 500;
}

.actions-cell {
    white-space: nowrap;
}

.btn-view, .btn-edit, .btn-delete {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    margin: 0 4px;
    border-radius: 4px;
    transition: background 0.3s ease;
}

.btn-view {
    color: #69f569;
}

.btn-view:hover {
    background: #e8f0e8;
}

.btn-edit {
    color: #ffc107;
}

.btn-edit:hover {
    background: #fff3cd;
}

.btn-delete {
    color: #dc3545;
}

.btn-delete:hover {
    background: #f8d7da;
}

.action-icon {
    width: 20px;
    height: 20px;
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
    border: 2px solid #69ff69;
    color: #69ff69;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.pagination-btn:hover:not(:disabled) {
    background: #69eb69;
    color: white;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-info {
    color: #69e469;
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
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 24px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modal-content h3 {
    color: #69f169;
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
    transition: background 0.3s ease;
}

.btn-confirm-delete:hover {
    background: #c82333;
}

.btn-cancel {
    background: #e8f0e8;
    color: #69f869;
    border: 2px solid #69ff69;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-cancel:hover {
    background: #d0e0d0;
}
</style>