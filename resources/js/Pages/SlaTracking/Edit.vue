<template>
  <AuthenticatedLayout>

    <div class="tickets-container">
      <div class="tickets-header">
        <h1 class="tickets-title">Edit SLA Tracking</h1>
      </div>

      <form @submit.prevent="submitForm" class="feature-form">

        <div class="form-grid">

          <div>
            <label class="form-label">Ticket</label>
            <select v-model="form.ticket_id" class="form-input">
              <option v-for="ticket in tickets" :key="ticket.id" :value="ticket.id">
                {{ ticket.ticket_number }} - {{ ticket.title }}
              </option>
            </select>
          </div>

          <div>
            <label class="form-label">First Response Due</label>
            <input type="datetime-local" v-model="form.first_response_due" class="form-input">
          </div>

          <div>
            <label class="form-label">First Response Actual</label>
            <input type="datetime-local" v-model="form.first_response_actual" class="form-input">
          </div>

          <div>
            <label class="form-label">Resolution Due</label>
            <input type="datetime-local" v-model="form.resolution_due" class="form-input">
          </div>

          <div>
            <label class="form-label">Resolution Actual</label>
            <input type="datetime-local" v-model="form.resolution_actual" class="form-input">
          </div>

          <div>
            <label class="form-label">Is Breached</label>
            <input type="checkbox" v-model="form.is_breached">
          </div>

          <div>
            <label class="form-label">Breach Reason</label>
            <input type="text" v-model="form.breach_reason" class="form-input">
          </div>

        </div>

        <button type="submit" class="btn-create mt-4">Update SLA Tracking</button>

      </form>
    </div>

  </AuthenticatedLayout>
</template>

<script>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  props: {
    slaTracking: Object,
    tickets: Array
  },
  components: { AuthenticatedLayout },
  setup(props) {
    const form = useForm({
      ticket_id: props.slaTracking.ticket_id,
      first_response_due: props.slaTracking.first_response_due,
      first_response_actual: props.slaTracking.first_response_actual,
      resolution_due: props.slaTracking.resolution_due,
      resolution_actual: props.slaTracking.resolution_actual,
      is_breached: props.slaTracking.is_breached,
      breach_reason: props.slaTracking.breach_reason
    })

    function submitForm() {
      form.put(`/sla-tracking/${props.slaTracking.id}`)
    }

    return { form, submitForm }
  }
}
</script>