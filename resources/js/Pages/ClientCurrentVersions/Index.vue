<template>
  <AuthenticatedLayout>
    <div class="container">
      <div class="header">
        <h1>Client Current Versions</h1>
        <button @click="createVersion" class="btn-create">Add Current Version</button>
      </div>

      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Client</th>
            <th>Version</th>
            <th>App Version</th>
            <th>DB Version</th>
            <th>Last Deployment</th>
            <th>Deployment Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in currentVersions" :key="c.id">
            <td>{{ c.id }}</td>
            <td>{{ c.client ? c.client.organization_name : 'N/A' }}</td>
            <td>{{ c.version ? c.version.version_name : 'N/A' }}</td>
            <td>{{ c.current_app_version }}</td>
            <td>{{ c.current_db_version }}</td>
            <td>{{ c.last_deployment ? c.last_deployment.id : 'N/A' }}</td>
            <td>{{ c.last_deployment_date ?? 'N/A' }}</td>
            <td>
              <button @click="editVersion(c.id)">Edit</button>
              <button @click="deleteVersion(c.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { router } from '@inertiajs/vue3'

export default {
  props: { currentVersions: Array },
  components: { AuthenticatedLayout },
  methods: {
    createVersion() { router.visit('/client-current-versions/create') },
    editVersion(id) { router.visit(`/client-current-versions/${id}/edit`) },
    deleteVersion(id) {
      if(confirm("Delete this record?")) {
        router.delete(`/client-current-versions/${id}`)
      }
    }
  }
}
</script>