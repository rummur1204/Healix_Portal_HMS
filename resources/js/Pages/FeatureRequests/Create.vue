<template>
  <AuthenticatedLayout>
    <div class="tickets-container">

      <div class="tickets-header">
        <h1 class="tickets-title">Create Feature Request</h1>

        <button @click="cancel" class="btn-cancel">
          Cancel
        </button>
      </div>

      <form @submit.prevent="submitForm" class="feature-form">

        <div class="form-grid">

          <!-- Ticket -->
          <div class="form-group">
            <label>Ticket</label>
            <select v-model="form.ticket_id" class="form-input">
              <option value="">Select Ticket</option>
              <option v-for="ticket in tickets" :key="ticket.id" :value="ticket.id">
                {{ ticket.title }}
              </option>
            </select>
          </div>

          <!-- Business Value -->
          <div class="form-group">
            <label>Business Value</label>
            <select v-model="form.business_value" class="form-input">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
          </div>

          <!-- Estimated Effort -->
          <div class="form-group">
            <label>Estimated Effort</label>
            <select v-model="form.estimated_effort" class="form-input">
              <option value="small">Small</option>
              <option value="medium">Medium</option>
              <option value="large">Large</option>
              <option value="xlarge">XLarge</option>
            </select>
          </div>

          <!-- Target Release -->
          <div class="form-group">
            <label>Target Release</label>
            <input
              type="text"
              v-model="form.target_release"
              class="form-input"
              placeholder="Example: v2.0"
            />
          </div>

          <!-- Approval Status -->
          <div class="form-group">
            <label>Approval Status</label>
            <select v-model="form.approval_status" class="form-input">
              <option value="proposed">Proposed</option>
              <option value="approved">Approved</option>
              <option value="planned">Planned</option>
              <option value="delivered">Delivered</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>

          <!-- External Link -->
          <div class="form-group">
            <label>External Link</label>
            <input
              type="text"
              v-model="form.external_link"
              class="form-input"
              placeholder="https://..."
            />
          </div>

        </div>

        <div class="form-actions">
          <button type="submit" class="btn-create">
            Save Feature Request
          </button>
        </div>

      </form>

    </div>
  </AuthenticatedLayout>
</template>

<script>
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {

  components: { AuthenticatedLayout },

  props: {
    tickets: Array
  },

  setup() {

    const form = useForm({
      ticket_id: '',
      business_value: 'medium',
      estimated_effort: 'medium',
      target_release: '',
      approval_status: 'proposed',
      external_link: ''
    })

    function submitForm(){
      form.post('/feature-requests')
    }

    function cancel(){
      router.visit('/feature-requests')
    }

    return { form, submitForm, cancel }

  }

}
</script>

<style scoped>

.feature-form{
  background:white;
  padding:24px;
  border-radius:12px;
  box-shadow:0 2px 8px rgba(44,94,44,0.1);
}

.form-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:20px;
}

.form-group{
  display:flex;
  flex-direction:column;
}

.form-group label{
  font-weight:600;
  margin-bottom:6px;
}

.form-input{
  padding:8px 10px;
  border-radius:6px;
  border:1px solid #ccc;
}

.form-actions{
  margin-top:20px;
}

.btn-cancel{
  background:#ccc;
  padding:6px 10px;
  border-radius:6px;
  cursor:pointer;
}

</style>