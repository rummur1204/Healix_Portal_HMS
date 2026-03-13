<template>
  <AuthenticatedLayout>

    <div class="tickets-container">

      <div class="tickets-header">
        <h1 class="tickets-title">Create SLA Configuration</h1>
      </div>

      <form @submit.prevent="submitForm" class="feature-form">

        <div class="form-grid">

          <div>
            <label class="form-label">SLA Name</label>
            <input v-model="form.sla_name" class="form-input" placeholder="Enter SLA name">
          </div>

          <div>
            <label class="form-label">Priority</label>
            <select v-model="form.priority" class="form-input">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="critical">Critical</option>
            </select>
          </div>

          <div>
            <label class="form-label">First Response (hrs)</label>
            <input type="number" v-model="form.first_response_hrs" class="form-input">
          </div>

          <div>
            <label class="form-label">Resolution (hrs)</label>
            <input type="number" v-model="form.resolution_hrs" class="form-input">
          </div>

          <div>
            <label class="form-label">Active</label>
            <input type="checkbox" v-model="form.is_active">
          </div>

        </div>

        <button type="submit" class="btn-create mt-4">
          Save SLA
        </button>

      </form>

    </div>

  </AuthenticatedLayout>
</template>

<script>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  components: { AuthenticatedLayout },
  setup() {
    const form = useForm({
      sla_name: '',
      priority: 'medium',
      first_response_hrs: '',
      resolution_hrs: '',
      is_active: true
    })

    function submitForm() {
      form.post('/sla-configurations')
    }

    return { form, submitForm }
  }
}
</script>

<style scoped>
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
}
.form-label {
  display: block;
  font-weight: 500;
  margin-bottom: 4px;
  color: #2c5e2c;
}
.form-input {
  width: 100%;
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 14px;
}
</style>