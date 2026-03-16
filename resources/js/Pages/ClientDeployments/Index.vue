<template>
  <AuthenticatedLayout>
    <div class="tickets-container">
      <div class="tickets-header">
        <h1 class="tickets-title">Client Deployments</h1>
        <button @click="createDeployment" class="btn-create">Create Deployment</button>
      </div>

      <div class="table-wrapper">
        <table class="tickets-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Client</th>
              <th>Version</th>
              <th>Deployed By</th>
              <th>Deployment Date</th>
              <th>Type</th>
              <th>Result</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in deployments" :key="d.id" class="hover-row">
              <td>{{ d.id }}</td>
              <td>{{ d.client ? d.client.organization_name : 'N/A' }}</td>
              <td>{{ d.version ? d.version.version_name : 'N/A' }}</td>
              <td>{{ d.deployedBy ? d.deployedBy.name : 'N/A' }}</td>
              <td>{{ d.deployment_date }}</td>
              <td>{{ d.deployment_type }}</td>
              <td>{{ d.result }}</td>
              <td class="actions-cell">
                <button @click="editDeployment(d.id)" class="btn-icon-action">Edit</button>
                <button @click="deleteDeployment(d.id)" class="btn-icon-action btn-delete-icon">Delete</button>
              </td>
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
  props: { deployments: Array },
  components: { AuthenticatedLayout },
  methods: {
    createDeployment() { router.visit('/client-deployments/create') },
    editDeployment(id) { router.visit(`/client-deployments/${id}/edit`) },
    deleteDeployment(id) {
      if(confirm("Delete this deployment?")) {
        router.delete(`/client-deployments/${id}`)
      }
    }
  }
}
</script>