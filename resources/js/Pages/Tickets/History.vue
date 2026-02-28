<template>
  <div class="ticket-history-container">
    <div class="history-header">
      <h1 class="history-title">Ticket History</h1>
    </div>

    <div class="history-table-wrapper">
      <table class="history-table">
        <thead>
          <tr>
            <th>Ticket ID</th>
            <th>Ticket Number</th>
            <th>Field Changed</th>
            <th>Old Value</th>
            <th>New Value</th>
            <th>Changed By</th>
            <th>Date</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="history in historyData.data" :key="history.id" class="hover-row">
            <td>{{ history.ticket_id }}</td>
            <td>{{ history.ticket?.ticket_number ?? '-' }}</td>
            <td>{{ history.changed_field }}</td>
            <td>{{ history.old_value ?? '-' }}</td>
            <td>{{ history.new_value }}</td>
            <td>{{ history.changed_by?.name ?? 'Unknown' }}</td>
            <td>{{ formatDate(history.created_at) }}</td>
            <td class="text-center">
              <button @click="confirmDelete(history.id)" class="btn-delete">Delete</button>
            </td>
          </tr>

          <tr v-if="!historyData.data || historyData.data.length === 0">
            <td colspan="8" class="text-center py-6 text-gray-500">
              No history found.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="historyData.last_page > 1" class="pagination-wrapper">
      <button
        v-for="link in historyData.links"
        :key="link.label"
        v-html="link.label"
        :disabled="!link.url"
        @click.prevent="goTo(link.url)"
        class="pagination-btn"
        :class="{ active: link.active }"
      ></button>
    </div>

    <!-- Count -->
    <div v-if="historyData.total" class="history-count">
      Showing {{ historyData.from }} to {{ historyData.to }} of {{ historyData.total }} entries
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const { props } = usePage();

// Provide default empty pagination structure if history doesn't exist
const historyData = ref(props.history || {
  data: [],
  links: [],
  from: 0,
  to: 0,
  total: 0,
  last_page: 1,
  current_page: 1,
  per_page: 15
});

function formatDate(date) {
  if (!date) return '-';
  return new Date(date).toLocaleString();
}

// Pagination
function goTo(url) {
  if (url) {
    router.visit(url, { preserveScroll: true });
  }
}

// Confirm and delete
function confirmDelete(historyId) {
  if (confirm('Are you sure you want to delete this history record?')) {
    router.delete(`/history/${historyId}`, {
      preserveScroll: true,
      onSuccess: (page) => {
        // Update the local data after successful deletion
        if (page.props.history) {
          historyData.value = page.props.history;
        }
        
        // Show success message (you can replace this with a toast notification)
        console.log('History entry deleted successfully');
        
        // Optional: Show alert for now
        alert('History entry deleted successfully');
      },
      onError: (errors) => {
        console.error('Error deleting history entry:', errors);
        alert('Error deleting history entry. Please try again.');
      }
    });
  }
}
</script>

<style scoped>
.ticket-history-container {
  padding: 24px;
  background: #f0f7f0;
  min-height: 100vh;
  border-radius: 12px;
}

.history-header {
  margin-bottom: 24px;
}

.history-title {
  font-size: 28px;
  font-weight: 600;
  color: #2c5e2c;
}

.history-table-wrapper {
  overflow-x: auto;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(44, 94, 44, 0.1);
}

.history-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 800px;
}

.history-table th,
.history-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #e0e8e0;
  font-size: 14px;
}

.history-table th {
  background-color: #f3f4f6;
  color: #2c5e2c;
  font-weight: 600;
  text-align: left;
}

.hover-row:hover {
  background-color: #f8fff8;
  transition: background 0.2s ease;
}

.btn-delete {
  background: #dc3545;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.3s ease;
}

.btn-delete:hover {
  background: #a71d2a;
}

.pagination-wrapper {
  margin-top: 16px;
  display: flex;
  justify-content: center;
  gap: 8px;
  flex-wrap: wrap;
}

.pagination-btn {
  padding: 8px 16px;
  border: 1px solid #e0e8e0;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s ease;
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

.pagination-btn:hover:not(:disabled) {
  background: #f0f7f0;
}

.history-count {
  margin-top: 16px;
  text-align: center;
  font-size: 14px;
  color: #6c757d;
}

@media (max-width: 768px) {
  .history-table th,
  .history-table td {
    padding: 10px 12px;
    font-size: 13px;
  }
}
</style>