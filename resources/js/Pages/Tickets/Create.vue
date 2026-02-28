<!-- resources/js/components/Tickets/Form.vue -->
<template>
    <div class="ticket-form-container">
        <div class="form-header">
            <h1 class="form-title">{{ isEdit ? 'Edit Ticket' : 'Create New Ticket' }}</h1>
            <button @click="cancel" class="btn-cancel">
                Cancel
            </button>
        </div>

        <form @submit.prevent="submitForm" class="ticket-form">
            <div class="form-grid">
                <!-- Left Column -->
                <div class="form-left">
                    <!-- Enhanced Client Dropdown with search/filter capability -->
                    <div class="form-group dropdown-enhanced">
                        <label for="client_id" class="form-label">
                            Client <span class="required-star">*</span>
                            <span class="field-description">Select the client for this ticket</span>
                        </label>
                        <div class="select-wrapper">
                            <select 
                                id="client_id"
                                v-model="form.client_id"
                                class="form-select"
                                :class="{ 'error': errors.client_id, 'has-value': form.client_id }"
                                required
                            >
                                <option value="" disabled>-- Choose a client --</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">
                                    {{ client.organization_name || client.name }}
                                </option>
                            </select>
                            <span class="select-arrow">▼</span>
                        </div>
                        <span v-if="errors.client_id" class="error-message">
                            {{ errors.client_id }}
                        </span>
                    </div>

                    <!-- Ticket Type Dropdown with icons -->
                    <div class="form-group dropdown-enhanced">
                        <label for="ticket_type" class="form-label">
                            Ticket Type <span class="required-star">*</span>
                            <span class="field-description">What kind of ticket is this?</span>
                        </label>
                        <div class="select-wrapper">
                            <select 
                                id="ticket_type"
                                v-model="form.ticket_type"
                                class="form-select"
                                :class="{ 'error': errors.ticket_type, 'has-value': form.ticket_type }"
                                required
                            >
                                <option value="" disabled>-- Select ticket type --</option>
                                <option value="support_issue">🔧 Support Issue</option>
                                <option value="feature_request">✨ Feature Request</option>
                                <option value="bug">🐛 Bug</option>
                                <option value="billing">💰 Billing</option>
                                <option value="deployment">🚀 Deployment</option>
                                <option value="other">📋 Other</option>
                            </select>
                            <span class="select-arrow">▼</span>
                        </div>
                        <span v-if="errors.ticket_type" class="error-message">
                            {{ errors.ticket_type }}
                        </span>
                    </div>

                    <!-- Priority Dropdown with visual indicators -->
                    <div class="form-group dropdown-enhanced">
                        <label for="priority" class="form-label">
                            Priority <span class="required-star">*</span>
                            <span class="field-description">Set the urgency level</span>
                        </label>
                        <div class="select-wrapper">
                            <select 
                                id="priority"
                                v-model="form.priority"
                                class="form-select"
                                :class="{ 
                                    'error': errors.priority,
                                    'priority-low': form.priority === 'low',
                                    'priority-medium': form.priority === 'medium',
                                    'priority-high': form.priority === 'high',
                                    'priority-critical': form.priority === 'critical'
                                }"
                                required
                            >
                                <option value="" disabled>-- Set priority --</option>
                                <option value="low">🟢 Low</option>
                                <option value="medium">🟡 Medium</option>
                                <option value="high">🟠 High</option>
                                <option value="critical">🔴 Critical</option>
                            </select>
                            <span class="select-arrow">▼</span>
                        </div>
                        <span v-if="errors.priority" class="error-message">
                            {{ errors.priority }}
                        </span>
                    </div>

                    <!-- Title and Description fields remain the same -->
                    <div class="form-group">
                        <label for="title" class="form-label">Title *</label>
                        <input 
                            type="text" 
                            id="title"
                            v-model="form.title"
                            class="form-input"
                            :class="{ 'error': errors.title }"
                            placeholder="Brief summary of the issue"
                            required
                        >
                        <span v-if="errors.title" class="error-message">
                            {{ errors.title }}
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description *</label>
                        <textarea 
                            id="description"
                            v-model="form.description"
                            class="form-textarea"
                            :class="{ 'error': errors.description }"
                            rows="4"
                            placeholder="Detailed description of the ticket..."
                            required
                        ></textarea>
                        <span v-if="errors.description" class="error-message">
                            {{ errors.description }}
                        </span>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="form-right">
                    <!-- Status dropdown (edit only) with visual styling -->
                    <div v-if="isEdit" class="form-group dropdown-enhanced">
                        <label for="status" class="form-label">
                            Status *
                            <span class="field-description">Current ticket status</span>
                        </label>
                        <div class="select-wrapper">
                            <select 
                                id="status"
                                v-model="form.status"
                                class="form-select"
                                :class="{ 
                                    'error': errors.status,
                                    'status-new': form.status === 'new',
                                    'status-progress': form.status === 'in_progress',
                                    'status-waiting': form.status === 'waiting_for_client',
                                    'status-resolved': form.status === 'resolved',
                                    'status-closed': form.status === 'closed',
                                    'status-rejected': form.status === 'rejected'
                                }"
                                required
                            >
                                <option value="" disabled>-- Select status --</option>
                                <option value="new">🆕 New</option>
                                <option value="in_progress">⚡ In Progress</option>
                                <option value="waiting_for_client">⏳ Waiting for Client</option>
                                <option value="resolved">✅ Resolved</option>
                                <option value="closed">🔒 Closed</option>
                                <option value="rejected">❌ Rejected</option>
                            </select>
                            <span class="select-arrow">▼</span>
                        </div>
                        <span v-if="errors.status" class="error-message">
                            {{ errors.status }}
                        </span>
                    </div>

                    <!-- Assign To dropdown with user avatars (if available) -->
                    <div class="form-group dropdown-enhanced">
                        <label for="assigned_to_user_id" class="form-label">
                            Assign To
                            <span class="field-description">Team member responsible</span>
                        </label>
                        <div class="select-wrapper">
                            <select 
                                id="assigned_to_user_id"
                                v-model="form.assigned_to_user_id"
                                class="form-select"
                                :class="{ 'error': errors.assigned_to_user_id, 'has-value': form.assigned_to_user_id }"
                            >
                                <option :value="null">👤 Unassigned</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    👤 {{ user.name }}
                                </option>
                            </select>
                            <span class="select-arrow">▼</span>
                        </div>
                        <span v-if="errors.assigned_to_user_id" class="error-message">
                            {{ errors.assigned_to_user_id }}
                        </span>
                    </div>

                    <!-- Due Date field with validation -->
                    <div class="form-group">
                        <label for="due_date" class="form-label">
                            Due Date
                            <span class="field-description">Expected completion date</span>
                        </label>
                        <input 
                            type="date" 
                            id="due_date"
                            v-model="form.due_date"
                            class="form-input"
                            :class="{ 'error': errors.due_date }"
                            :min="today"
                        >
                        <span v-if="errors.due_date" class="error-message">
                            {{ errors.due_date }}
                        </span>
                    </div>

                    <!-- Read-only fields remain the same -->
                    <div v-if="isEdit" class="readonly-fields">
                        <div class="form-group">
                            <label class="form-label">Ticket Number</label>
                            <input 
                                type="text" 
                                :value="ticket.ticket_number"
                                class="form-input readonly"
                                readonly
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Created By</label>
                            <input 
                                type="text" 
                                :value="ticket.created_by?.name || 'N/A'"
                                class="form-input readonly"
                                readonly
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Created At</label>
                            <input 
                                type="text" 
                                :value="formatDate(ticket.created_at)"
                                class="form-input readonly"
                                readonly
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Last Updated</label>
                            <input 
                                type="text" 
                                :value="formatDate(ticket.updated_at)"
                                class="form-input readonly"
                                readonly
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-submit" :disabled="loading">
                    <span v-if="loading" class="spinner"></span>
                    {{ isEdit ? 'Update Ticket' : 'Create Ticket' }}
                </button>
                <button type="button" @click="cancel" class="btn-cancel-form">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import { router } from '@inertiajs/vue3'

export default {
    name: 'TicketForm',
    
    props: {
        ticket: {
            type: Object,
            default: () => ({})
        },
        isEdit: {
            type: Boolean,
            default: false
        },
        clients: {
            type: Array,
            required: true
        },
        users: {
            type: Array,
            required: true
        }
    },

    data() {
        return {
            form: this.getInitialForm(),
            errors: {},
            loading: false,
            today: new Date().toISOString().split('T')[0]
        }
    },

    mounted() {
        if (this.isEdit && this.ticket) {
            this.populateForm();
        }
        
        // Debug: Log clients to verify they're received
        console.log('Clients received:', this.clients);
        console.log('Users received:', this.users);
    },

    methods: {
        getInitialForm() {
            const baseForm = {
                client_id: '',
                ticket_type: '',
                title: '',
                description: '',
                priority: 'medium',
                assigned_to_user_id: null,
                due_date: '',
            };

            if (this.isEdit) {
                baseForm.status = '';
            }

            return baseForm;
        },

        populateForm() {
            this.form = {
                client_id: this.ticket.client_id,
                ticket_type: this.ticket.ticket_type,
                title: this.ticket.title,
                description: this.ticket.description || '',
                priority: this.ticket.priority,
                status: this.ticket.status,
                assigned_to_user_id: this.ticket.assigned_to_user_id || null,
                due_date: this.ticket.due_date || '',
            };
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};

            try {
                if (this.isEdit) {
                    // For edit, only send updatable fields
                    const updateData = {
                        status: this.form.status,
                        priority: this.form.priority,
                        assigned_to_user_id: this.form.assigned_to_user_id,
                        due_date: this.form.due_date,
                    };
                    
                    await router.put(`/tickets/${this.ticket.id}`, updateData);
                    this.showSuccess('Ticket updated successfully');
                } else {
                    await router.post('/tickets', this.form);
                    this.showSuccess('Ticket created successfully');
                }
                
                // Redirect to index
                router.visit('/tickets');
            } catch (error) {
                this.loading = false;
                
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                } else {
                    this.showError('An error occurred. Please try again.');
                }
            }
        },

        cancel() {
            router.visit('/tickets');
        },

        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleString();
        },

        showSuccess(message) {
            alert(message);
        },

        showError(message) {
            alert(message);
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