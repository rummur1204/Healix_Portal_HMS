<template>
  <div class="tickets-container">
    <!-- Header -->
    <div class="header-section">
      <h1 class="page-title">Upload Ticket Attachment</h1>
      <button @click="$inertia.visit('/ticket-attachments')" class="btn-cancel">
        ← Back to List
      </button>
    </div>

    <!-- Form -->
    <div class="filters-section">
      <div class="form-row">
        <label for="ticket_id">Ticket</label>
        <select v-model="form.ticket_id" id="ticket_id" class="filter-select">
          <option value="">-- Select Ticket --</option>
          <option v-for="ticket in tickets" :key="ticket.id" :value="ticket.id">
            {{ ticket.ticket_number }}
          </option>
        </select>
      </div>

      <div class="form-row">
        <label for="file">File</label>
        <input type="file" id="file" @change="handleFileUpload" />
      </div>

      <!-- Actions -->
      <div class="modal-actions" style="margin-top:20px;">
        <button @click="submitForm" class="btn-create">Upload</button>
        <button @click="$inertia.visit('/ticket-attachments')" class="btn-cancel">Cancel</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    tickets: Array
  },

  data() {
    return {
      form: {
        ticket_id: '',
        file: null
      }
    }
  },

  methods: {
    handleFileUpload(event) {
      this.form.file = event.target.files[0];
    },

    async submitForm() {
      if (!this.form.ticket_id || !this.form.file) {
        alert('Please select a ticket and a file.');
        return;
      }

      const formData = new FormData();
      formData.append('ticket_id', this.form.ticket_id);
      formData.append('upload_by_user_id', this.$page.props.auth.user.id); // use logged-in user
      formData.append('file', this.form.file);

      try {
        // POST to Laravel route
        await axios.post('/ticket-attachments', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });

        this.$inertia.visit('/ticket-attachments');
      } catch (error) {
        console.error(error);
        alert('Failed to upload attachment.');
      }
    }
  }
}
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

.filters-section {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(44, 94, 44, 0.1);
}

.form-row {
    display: flex;
    align-items: center;
    margin-bottom: 16px;
}

.form-row label {
    width: 180px;
    font-weight: 500;
    color: #2c5e2c;
}

.filter-input, .filter-select {
    flex: 1;
    padding: 10px 16px;
    border: 2px solid #e0e8e0;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.filter-input:focus, .filter-select:focus {
    outline: none;
    border-color: #69d169;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}
</style>


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

.filters-section {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(44, 94, 44, 0.1);
}

.form-row {
    display: flex;
    align-items: center;
    margin-bottom: 16px;
}

.form-row label {
    width: 180px;
    font-weight: 500;
    color: #2c5e2c;
}

.filter-input {
    flex: 1;
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

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}
</style>