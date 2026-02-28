<template>
    <div class="ticket-form-container">
        <div class="form-header">
            <h1 class="form-title">Edit Ticket</h1>
            <button @click="cancel" class="btn-cancel">Cancel</button>
        </div>

        <form @submit.prevent="submitForm" class="ticket-form">
            <div class="form-grid">

                <!-- LEFT COLUMN -->
                <div>

                    <div class="form-group">
                        <label>Client</label>
                        <select v-model="form.client_id" class="form-input" disabled>
                            <option v-for="client in clients"
                                    :key="client.id"
                                    :value="client.id">
                                {{ client.organization_name || client.name }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Priority *</label>
                        <select v-model="form.priority" class="form-input">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status *</label>
                        <select v-model="form.status" class="form-input">
                            <option value="new">New</option>
                            <option value="in_progress">In Progress</option>
                            <option value="waiting_for_client">Waiting for Client</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Assign To</label>
                        <select v-model="form.assigned_to_user_id" class="form-input">
                            <option :value="null">Unassigned</option>
                            <option v-for="user in users"
                                    :key="user.id"
                                    :value="user.id">
                                {{ user.name }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date"
                               v-model="form.due_date"
                               class="form-input">
                    </div>

                </div>

                <!-- RIGHT COLUMN -->
                <div>

                    <div class="form-group">
                        <label>Ticket Number</label>
                        <input :value="ticket.ticket_number"
                               class="form-input"
                               readonly>
                    </div>

                    <div class="form-group">
                        <label>Title</label>
                        <input :value="ticket.title"
                               class="form-input"
                               readonly>
                    </div>

                    <div class="form-group">
                        <label>Created At</label>
                        <input :value="formatDate(ticket.created_at)"
                               class="form-input"
                               readonly>
                    </div>

                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    Update Ticket
                </button>
                <button type="button"
                        @click="cancel"
                        class="btn-cancel-form">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import { router } from '@inertiajs/vue3'

export default {
    props: {
        ticket: Object,
        clients: Array,
        users: Array
    },

    data() {
        return {
            form: {
                client_id: this.ticket.client_id,
                priority: this.ticket.priority,
                status: this.ticket.status,
                assigned_to_user_id: this.ticket.assigned_to_user_id,
                due_date: this.ticket.due_date
            }
        }
    },

    methods: {
        submitForm() {
            router.put(`/tickets/${this.ticket.id}`, {
                status: this.form.status,
                priority: this.form.priority,
                assigned_to_user_id: this.form.assigned_to_user_id,
                due_date: this.form.due_date,
            })
        },

        cancel() {
            router.visit('/tickets')
        },

        formatDate(date) {
            if (!date) return 'N/A'
            return new Date(date).toLocaleString()
        }
    }
}
</script>
<style scoped>
/* Keep your existing styles and add these enhanced dropdown styles */

/* Enhanced Dropdown Styling */
.dropdown-enhanced {
    position: relative;
}

.select-wrapper {
    position: relative;
    width: 100%;
}

.form-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    width: 100%;
    padding: 12px 40px 12px 16px;
    border: 2px solid #e0e8e0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
    cursor: pointer;
    line-height: 1.5;
}

.form-select.has-value {
    border-color: #2c5e2c;
    background-color: #f8fff8;
}

.form-select:focus {
    outline: none;
    border-color: #2c5e2c;
    box-shadow: 0 0 0 3px rgba(44, 94, 44, 0.1);
}

.form-select:hover {
    border-color: #2c5e2c;
}

.select-arrow {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #2c5e2c;
    font-size: 12px;
    pointer-events: none;
    transition: transform 0.3s ease;
}

.select-wrapper:hover .select-arrow {
    transform: translateY(-50%) scale(1.2);
}

/* Priority-based styling */
.form-select.priority-low {
    border-left: 4px solid #28a745;
}

.form-select.priority-medium {
    border-left: 4px solid #ffc107;
}

.form-select.priority-high {
    border-left: 4px solid #fd7e14;
}

.form-select.priority-critical {
    border-left: 4px solid #dc3545;
}

/* Status-based styling */
.form-select.status-new {
    border-left: 4px solid #17a2b8;
}

.form-select.status-progress {
    border-left: 4px solid #007bff;
}

.form-select.status-waiting {
    border-left: 4px solid #ffc107;
}

.form-select.status-resolved {
    border-left: 4px solid #28a745;
}

.form-select.status-closed {
    border-left: 4px solid #6c757d;
}

.form-select.status-rejected {
    border-left: 4px solid #dc3545;
}

/* Field description */
.field-description {
    display: block;
    font-size: 12px;
    font-weight: normal;
    color: #6c757d;
    margin-top: 2px;
}

.required-star {
    color: #dc3545;
    margin-left: 2px;
}

/* Enhanced error states */
.form-select.error {
    border-color: #dc3545;
    border-left-width: 4px;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .form-select {
        font-size: 16px; /* Prevents zoom on mobile */
        padding: 14px 40px 14px 16px;
    }
    
    .field-description {
        font-size: 11px;
    }
}

/* Keep all your existing styles from before */
.ticket-form-container {
    padding: 24px;
    background: #f0f7f0;
    min-height: 100vh;
}

.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.form-title {
    font-size: 28px;
    font-weight: 600;
    color: #2c5e2c;
    margin: 0;
}

.btn-cancel {
    background: #e8f0e8;
    color: #2c5e2c;
    border: 2px solid #2c5e2c;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-cancel:hover {
    background: #d0e0d0;
}

.ticket-form {
    background: white;
    border-radius: 12px;
    padding: 32px;
    box-shadow: 0 2px 8px rgba(44, 94, 44, 0.1);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
    margin-bottom: 32px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #2c5e2c;
    margin-bottom: 8px;
}

.form-input,
.form-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e0e8e0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: #2c5e2c;
    box-shadow: 0 0 0 3px rgba(44, 94, 44, 0.1);
}

.form-input.error,
.form-textarea.error {
    border-color: #dc3545;
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

.readonly {
    background: #f5f5f5;
    cursor: not-allowed;
    color: #666;
}

.readonly-fields {
    background: #f8faf8;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e0e8e0;
}

.error-message {
    display: block;
    font-size: 12px;
    color: #dc3545;
    margin-top: 4px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 16px;
    margin-top: 32px;
    padding-top: 32px;
    border-top: 2px solid #e0e8e0;
}

.btn-submit {
    background: #2c5e2c;
    color: white;
    border: none;
    padding: 14px 32px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-submit:hover:not(:disabled) {
    background: #1e431e;
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-cancel-form {
    background: #e8f0e8;
    color: #2c5e2c;
    border: 2px solid #2c5e2c;
    padding: 14px 32px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-cancel-form:hover {
    background: #d0e0d0;
}

.spinner {
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .btn-submit,
    .btn-cancel-form {
        width: 100%;
    }
}
</style>