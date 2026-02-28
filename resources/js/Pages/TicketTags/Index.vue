<template>
  <div class="tickets-container">
    <div class="header-section">
      <h1 class="page-title">Ticket Tags</h1>
      <button @click="$inertia.visit('/ticket-tags/create')" class="btn-create">
        Add Tag
      </button>
    </div>

    <div class="table-responsive">
      <table class="tickets-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Color</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="tag in tagList" :key="tag.id">
            <td>{{ tag.id }}</td>
            <td>{{ tag.name }}</td>
            <td>
              <span :style="{ backgroundColor: tag.color, padding: '4px 12px', borderRadius: '4px', color: '#fff' }">
                {{ tag.color }}
              </span>
            </td>
            <td>
              <span :class="tag.is_active ? 'status-badge open' : 'status-badge closed'">
                {{ tag.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td>
              <button @click="$inertia.visit(`/ticket-tags/${tag.id}/edit`)" class="btn-edit">Edit</button>
              <button @click="deleteTag(tag.id)" class="btn-delete">Delete</button>
            </td>
          </tr>
          <tr v-if="tagList.length === 0">
            <td colspan="5" class="no-records">No tags found.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  props: { tags: Object },
  data() {
    return {
      tagList: this.tags.data, // local copy for instant updates
    };
  },
  methods: {
    deleteTag(id) {
      if (!confirm('Are you sure you want to delete this tag?')) return;

      this.$inertia.delete(`/ticket-tags/${id}`, {
        onSuccess: () => {
          // remove tag from UI immediately
          this.tagList = this.tagList.filter(tag => tag.id !== id);
        },
        onError: (errors) => {
          console.error('Delete failed', errors);
          alert('Failed to delete tag. Please try again.');
        },
      });
    },
  },
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

.status-badge.closed {
    background: #e2e3e5;
    color: #383d41;
}

.btn-edit, .btn-delete {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    margin: 0 4px;
    border-radius: 4px;
    transition: background 0.3s ease;
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

.no-records {
    text-align: center;
    padding: 48px;
    color: #666;
    font-style: italic;
}
</style>