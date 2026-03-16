<template>
  <AuthenticatedLayout>
    <div class="tickets-container">
      <div class="tickets-header">
        <h1 class="tickets-title">Product Versions</h1>
        <button @click="createVersion" class="btn-create">Create Version</button>
      </div>

      <div class="table-wrapper">
        <table class="tickets-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Version Name</th>
              <th>Release Date</th>
              <th>Environment</th>
              <th>Status</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="version in productVersions" :key="version.id" class="hover-row">
              <td>{{ version.id }}</td>
              <td>{{ version.version_name }}</td>
              <td>{{ formatDate(version.release_date) }}</td>
              <td>{{ version.environment }}</td>
              <td>{{ version.is_active ? 'Active' : 'Inactive' }}</td>
              <td class="actions-cell">
                <button @click="editVersion(version.id)" class="btn-icon-action">Edit</button>
                <button @click="deleteVersion(version.id)" class="btn-icon-action btn-delete-icon">Delete</button>
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
  props: { productVersions: Array },
  components: { AuthenticatedLayout },
  methods: {
    createVersion() {
      router.visit('/product-versions/create')
    },
    editVersion(id) {
      router.visit(`/product-versions/${id}/edit`)
    },
    deleteVersion(id) {
      if (confirm('Delete this product version?')) {
        router.delete(`/product-versions/${id}`)
      }
    },
    formatDate(date) {
      return date ? new Date(date).toLocaleDateString() : 'N/A'
    }
  }
}
</script>