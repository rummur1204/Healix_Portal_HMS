<template>
  <AuthenticatedLayout>

    <div class="tickets-container">

      <div class="tickets-header">
        <h1 class="tickets-title">SLA Tracking</h1>
        <button @click="createSlaTracking" class="btn-create">
          Create SLA Tracking
        </button>
      </div>

      <div class="table-wrapper">
        <table class="tickets-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Ticket #</th>
              <th>First Response Due</th>
              <th>First Response Actual</th>
              <th>Resolution Due</th>
              <th>Resolution Actual</th>
              <th>Breach</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="sla in slaTrackings" :key="sla.id" class="hover-row">
              <td>{{ sla.id }}</td>
              <td>{{ sla.ticket?.ticket_number ?? 'N/A' }}</td>
              <td>{{ formatDate(sla.first_response_due) }}</td>
              <td>{{ formatDate(sla.first_response_actual) }}</td>
              <td>{{ formatDate(sla.resolution_due) }}</td>
              <td>{{ formatDate(sla.resolution_actual) }}</td>
              <td>
                <span :class="sla.is_breached ? 'status-badge rejected' : 'status-badge resolved'">
                  {{ sla.is_breached ? 'Breached' : 'On Time' }}
                </span>
              </td>
              <td class="actions-cell">
                <button @click="editSlaTracking(sla.id)" class="btn-icon-action">Edit</button>
                <button @click="deleteSlaTracking(sla.id)" class="btn-icon-action btn-delete-icon">Delete</button>
              </td>
            </tr>
            <tr v-if="!slaTrackings.length">
              <td colspan="8" class="text-center py-6 text-gray-500">No SLA tracking found</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

  </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { router } from '@inertiajs/vue3'

export default {
  props: {
    slaTrackings: Array
  },
  components: { AuthenticatedLayout },
  methods: {
    createSlaTracking() {
      router.visit('/sla-tracking/create')
    },
    editSlaTracking(id) {
      router.visit(`/sla-tracking/${id}/edit`)
    },
    deleteSlaTracking(id) {
      if (confirm("Delete this SLA tracking record?")) {
        router.delete(`/sla-tracking/${id}`)
      }
    },
    formatDate(d) {
      return d ? new Date(d).toLocaleString() : 'N/A'
    }
  }
}
</script>