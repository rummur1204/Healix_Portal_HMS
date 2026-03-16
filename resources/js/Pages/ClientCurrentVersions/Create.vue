<template>
  <AuthenticatedLayout>
    <div class="container">
      <h1>{{ currentVersion ? 'Edit Current Version' : 'Add Current Version' }}</h1>

      <form @submit.prevent="submitForm">
        <div>
          <label>Client</label>
          <select v-model="form.client_id">
            <option value="" disabled>Select Client</option>
            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.organization_name }}</option>
          </select>
        </div>

        <div>
          <label>Version</label>
          <select v-model="form.version_id">
            <option value="" disabled>Select Version</option>
            <option v-for="v in versions" :key="v.id" :value="v.id">{{ v.version_name }}</option>
          </select>
        </div>

        <div>
          <label>Current App Version</label>
          <input type="text" v-model="form.current_app_version" />
        </div>

        <div>
          <label>Current DB Version</label>
          <input type="text" v-model="form.current_db_version" />
        </div>

        <div>
          <label>Last Deployment</label>
          <select v-model="form.last_deployment_id">
            <option value="">None</option>
            <option v-for="d in deployments" :key="d.id" :value="d.id">{{ d.id }} - {{ d.deployment_date }}</option>
          </select>
        </div>

        <div>
          <label>Last Deployment Date</label>
          <input type="date" v-model="form.last_deployment_date" />
        </div>

        <div>
          <button type="submit">{{ currentVersion ? 'Update' : 'Create' }}</button>
          <button type="button" @click="cancel">Cancel</button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { router } from '@inertiajs/vue3'

export default {
  props: { clients: Array, versions: Array, deployments: Array, currentVersion: Object },
  components: { AuthenticatedLayout },
  data() {
    return {
      form: {
        client_id: this.currentVersion ? this.currentVersion.client_id : '',
        version_id: this.currentVersion ? this.currentVersion.version_id : '',
        current_app_version: this.currentVersion ? this.currentVersion.current_app_version : '',
        current_db_version: this.currentVersion ? this.currentVersion.current_db_version : '',
        last_deployment_id: this.currentVersion ? this.currentVersion.last_deployment_id : '',
        last_deployment_date: this.currentVersion ? this.currentVersion.last_deployment_date : '',
      }
    }
  },
  methods: {
    submitForm() {
      if(this.currentVersion) {
        router.put(`/client-current-versions/${this.currentVersion.id}`, this.form)
      } else {
        router.post('/client-current-versions', this.form)
      }
    },
    cancel() {
      router.visit('/client-current-versions')
    }
  }
}
</script>