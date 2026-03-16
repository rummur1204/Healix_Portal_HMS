<template>
  <AuthenticatedLayout>
    <div class="tickets-container">
      <div class="form-header">
        <h1 class="form-title">Edit Product Version</h1>
      </div>

      <form @submit.prevent="submitForm" class="ticket-form">
        <div class="form-grid">

          <div class="form-field">
            <label>Version Name</label>
            <input v-model="form.version_name" type="text" />
            <span v-if="errors.version_name" class="error-text">{{ errors.version_name }}</span>
          </div>

          <div class="form-field">
            <label>Release Date</label>
            <input v-model="form.release_date" type="date" />
            <span v-if="errors.release_date" class="error-text">{{ errors.release_date }}</span>
          </div>

          <div class="form-field">
            <label>Environment</label>
            <select v-model="form.environment">
              <option value="production">Production</option>
              <option value="staging">Staging</option>
              <option value="development">Development</option>
            </select>
            <span v-if="errors.environment" class="error-text">{{ errors.environment }}</span>
          </div>

          <div class="form-field">
            <label>Release Notes</label>
            <textarea v-model="form.release_notes"></textarea>
          </div>

          <div class="form-field">
            <label>Build ID</label>
            <input v-model="form.build_id" type="text" />
          </div>

          <div class="form-field">
            <label>App Version</label>
            <input v-model="form.app_version" type="text" />
          </div>

          <div class="form-field">
            <label>DB Schema Version</label>
            <input v-model="form.db_schema_version" type="text" />
          </div>

          <div class="form-field">
            <label>Active</label>
            <input type="checkbox" v-model="form.is_active" />
          </div>

        </div>

        <div class="form-actions">
          <button type="submit" class="btn-create">Update</button>
          <button type="button" @click="cancel" class="btn-reset">Cancel</button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { router } from '@inertiajs/vue3'

export default {
  props: { productVersion: Object },
  components: { AuthenticatedLayout },
  data() {
    return {
      form: { ...this.productVersion },
      errors: {}
    }
  },
  methods: {
    submitForm() {
      router.put(`/product-versions/${this.form.id}`, this.form, {
        onError: (errors) => {
          this.errors = errors
        }
      })
    },
    cancel() {
      router.visit('/product-versions')
    }
  }
}
</script>

<style scoped>
/* Same styling as Create.vue */
.tickets-container { padding: 24px; background: #f0fff0; border-radius: 12px; }
.form-header { margin-bottom: 24px; }
.form-title { font-size: 28px; color: #2c5e2c; font-weight: 600; }
.form-grid { display: grid; gap: 16px; grid-template-columns: 1fr 1fr; }
.form-field { display: flex; flex-direction: column; }
.form-field label { font-weight: 600; margin-bottom: 4px; }
.form-field input, .form-field select, .form-field textarea { padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; }
.form-actions { margin-top: 20px; display: flex; gap: 12px; }
.btn-create { background: #4fd14f; color: white; padding: 8px 16px; border-radius: 8px; }
.btn-create:hover { background: #1f4520; }
.btn-reset { background: #ccc; color: #333; padding: 8px 16px; border-radius: 8px; }
.error-text { color: #dc3545; font-size: 13px; margin-top: 2px; }
</style>