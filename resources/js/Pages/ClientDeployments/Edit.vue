<template>
  <AuthenticatedLayout>
    <div class="tickets-container">
      <div class="tickets-header">
        <h1 class="tickets-title">Edit Deployment</h1>
        <button @click="cancel" class="btn-cancel">Cancel</button>
      </div>

      <form @submit.prevent="submitForm" class="ticket-form">
        <div class="form-grid">

          <!-- Client -->
          <div>
            <label class="form-label">Client</label>
            <select v-model="form.client_id" class="form-input">
              <option value="" disabled>Select Client</option>
              <option v-for="c in clients" :key="c.id" :value="c.id">
                {{ c.organization_name }}
              </option>
            </select>
          </div>

          <!-- Version -->
          <div>
            <label class="form-label">Version</label>
            <select v-model="form.version_id" class="form-input">
              <option value="" disabled>Select Version</option>
              <option v-for="v in versions" :key="v.id" :value="v.id">
                {{ v.version_name }}
              </option>
            </select>
          </div>

          <!-- Deployed By -->
          <div>
            <label class="form-label">Deployed By</label>
            <select v-model="form.deployed_by_user_id" class="form-input">
              <option value="" disabled>Select User</option>
              <option v-for="u in users" :key="u.id" :value="u.id">
                {{ u.name }}
              </option>
            </select>
          </div>

          <!-- Deployment Date -->
          <div>
            <label class="form-label">Deployment Date</label>
            <input type="date" v-model="form.deployment_date" class="form-input"/>
          </div>

          <!-- Deployment Type -->
          <div>
            <label class="form-label">Deployment Type</label>
            <select v-model="form.deployment_type" class="form-input">
              <option value="" disabled>Select Type</option>
              <option value="new_install">New Install</option>
              <option value="upgrade">Upgrade</option>
              <option value="hotfix">Hotfix</option>
              <option value="rollback">Rollback</option>
            </select>
          </div>

          <!-- Result -->
          <div>
            <label class="form-label">Result</label>
            <select v-model="form.result" class="form-input">
              <option value="success">Success</option>
              <option value="failed">Failed</option>
              <option value="partial">Partial</option>
            </select>
          </div>

          <!-- Notes -->
          <div class="full-width">
            <label class="form-label">Notes</label>
            <textarea v-model="form.notes" class="form-input"></textarea>
          </div>

        </div>

        <div class="form-actions">
          <button type="submit" class="btn-create">Update</button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { router } from '@inertiajs/vue3'

export default {
  props: {
    deployment: Object,
    clients: Array,
    versions: Array,
    users: Array
  },
  components: { AuthenticatedLayout },
  data() {
    return {
      form: {
        client_id: this.deployment?.client?.id || '',
        version_id: this.deployment?.version?.id || '',
        deployed_by_user_id: this.deployment?.deployedBy?.id || '',
        deployment_date: this.deployment?.deployment_date || '',
        deployment_type: this.deployment?.deployment_type || '',
        result: this.deployment?.result || 'success',
        notes: this.deployment?.notes || ''
      }
    }
  },
  methods: {
    submitForm() {
      router.put(`/client-deployments/${this.deployment.id}`, this.form)
    },
    cancel() {
      router.visit('/client-deployments')
    }
  }
}
</script>

<style scoped>
/* same styles as Create.vue */
.ticket-form { display: flex; flex-direction: column; gap: 16px; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
.form-label { font-weight: 500; margin-bottom: 4px; display: block; }
.form-input { padding: 8px; border-radius: 6px; border: 1px solid #ccc; width: 100%; }
.full-width { grid-column: 1 / -1; }
.form-actions { margin-top: 16px; }
.btn-create { background: #4fd14f; color: white; padding: 8px 16px; border-radius: 8px; cursor: pointer; }
.btn-cancel { background: #ccc; color: #333; padding: 8px 16px; border-radius: 8px; cursor: pointer; margin-left: 8px; }
</style>