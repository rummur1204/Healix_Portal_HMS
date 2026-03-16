<template>
  <AuthenticatedLayout>
    <div class="tickets-container">

      <!-- Header -->
      <div class="tickets-header">
        <h1 class="tickets-title">Feature Requests</h1>

        <button @click="createFeature" class="btn-create">
          <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
          </svg>
          Create Feature Request
        </button>
      </div>

      <!-- Table -->
      <div class="table-wrapper">
        <table class="tickets-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Ticket</th>
              <th>Business Value</th>
              <th>Effort</th>
              <th>Target Release</th>
              <th>Status</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="feature in features"
              :key="feature.id"
              class="hover-row"
            >
              <td>{{ feature.id }}</td>
              <td>{{ feature.ticket?.title ?? 'N/A' }}</td>

              <td>
                <span :class="['priority-badge', feature.business_value]">
                  {{ formatValue(feature.business_value) }}
                </span>
              </td>

              <td>{{ feature.estimated_effort }}</td>

              <td>
                {{ feature.target_release ?? 'N/A' }}
              </td>

              <td>
                <span :class="['status-badge', feature.approval_status]">
                  {{ formatStatus(feature.approval_status) }}
                </span>
              </td>

              <td class="text-center actions-cell">

                <!-- Edit -->
                <button
                  @click="editFeature(feature.id)"
                  class="btn-icon-action"
                  title="Edit"
                >
                  <svg class="action-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                  </svg>
                </button>

                <!-- Delete -->
                <button
                  @click="deleteFeature(feature.id)"
                  class="btn-icon-action btn-delete-icon"
                  title="Delete"
                >
                  <svg class="action-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z"/>
                  </svg>
                </button>

              </td>
            </tr>

            <tr v-if="!features || features.length === 0">
              <td colspan="7" class="text-center py-6 text-gray-500">
                No feature requests found
              </td>
            </tr>

          </tbody>
        </table>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script>
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {

  components: {
    AuthenticatedLayout
  },

  props: {
    features: Array
  },

  methods: {

    createFeature(){
      router.visit('/feature-requests/create')
    },

    editFeature(id){
      router.visit(`/feature-requests/${id}/edit`)
    },

    deleteFeature(id){

      if(confirm("Delete this feature request?")){

        router.delete(`/feature-requests/${id}`,{
          preserveScroll:true
        })

      }

    },

    formatValue(v){
      return v ? v.charAt(0).toUpperCase()+v.slice(1) : ''
    },

    formatStatus(s){

      const statuses = {

        proposed:'Proposed',
        approved:'Approved',
        planned:'Planned',
        delivered:'Delivered',
        rejected:'Rejected'

      }

      return statuses[s] || s

    }

  }

}
</script>

<style scoped>

/* reuse same styling from tickets page */

.tickets-container{
  padding:24px;
  min-height:100vh;
  background:#f0fff0;
  border-radius:12px;
}

.tickets-header{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:24px;
}

.tickets-title{
  font-size:28px;
  font-weight:600;
  color:#2c5e2c;
}

.btn-create{
  display:flex;
  align-items:center;
  gap:3px;
  background:#4fd14f;
  color:white;
  border:none;
  padding:4px 8px;
  border-radius:5px;
  cursor:pointer;
  font-size:13px;
}

.btn-create:hover{
  background:#1f4520;
}

.btn-icon{
  width:16px;
  height:16px;
}

.table-wrapper{
  overflow-x:auto;
  background:white;
  border-radius:12px;
  box-shadow:0 2px 8px rgba(44,94,44,0.1);
}

.tickets-table{
  width:100%;
  border-collapse:collapse;
}

.tickets-table th,
.tickets-table td{
  padding:12px 16px;
  border-bottom:1px solid #e0e8e0;
  font-size:14px;
}

.tickets-table th{
  background:#f3f4f6;
  color:#2c5e2c;
  font-weight:600;
}

.hover-row:hover{
  background:#f8fff8;
}

.actions-cell{
  white-space:nowrap;
  display:flex;
  justify-content:center;
  gap:4px;
}

.btn-icon-action{
  background:none;
  border:none;
  cursor:pointer;
  padding:4px;
  border-radius:6px;
}

.btn-icon-action:hover{
  background:#d1f0d1;
}

.btn-delete-icon:hover{
  background:#f8d6d6;
}

.action-icon{
  width:18px;
  height:18px;
}

.priority-badge.low{
  background:#d1f0d1;
  color:#1f4520;
  padding:2px 6px;
  border-radius:6px;
  font-size:12px;
}

.priority-badge.medium{
  background:#a0e0a0;
  color:#144d14;
  padding:2px 6px;
  border-radius:6px;
  font-size:12px;
}

.priority-badge.high{
  background:#58c958;
  color:white;
  padding:2px 6px;
  border-radius:6px;
  font-size:12px;
}

.status-badge.proposed{
  background:#d1f0d1;
  color:#1f4520;
  padding:2px 6px;
  border-radius:6px;
}

.status-badge.approved{
  background:#a0e0a0;
  color:#144d14;
  padding:2px 6px;
  border-radius:6px;
}

.status-badge.planned{
  background:#cce5ff;
  color:#004085;
  padding:2px 6px;
  border-radius:6px;
}

.status-badge.delivered{
  background:#d6d8d9;
  color:#1b1e21;
  padding:2px 6px;
  border-radius:6px;
}

.status-badge.rejected{
  background:#f8d7da;
  color:#721c24;
  padding:2px 6px;
  border-radius:6px;
}

.text-center{
  text-align:center;
}

</style>