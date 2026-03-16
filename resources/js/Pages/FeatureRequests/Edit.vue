<template>
  <AuthenticatedLayout>
    <div class="tickets-container">

      <div class="tickets-header">
        <h1 class="tickets-title">Edit Feature Request</h1>

        <button @click="cancel" class="btn-cancel">
          Cancel
        </button>
      </div>

      <form @submit.prevent="submitForm" class="feature-form">

        <div class="form-grid">

          <!-- Business Value -->
          <div class="form-group">
            <label>Business Value</label>
            <select v-model="form.business_value" class="form-input">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
          </div>

          <!-- Estimated Effort -->
          <div class="form-group">
            <label>Estimated Effort</label>
            <select v-model="form.estimated_effort" class="form-input">
              <option value="small">Small</option>
              <option value="medium">Medium</option>
              <option value="large">Large</option>
              <option value="xlarge">XLarge</option>
            </select>
          </div>

          <!-- Target Release -->
          <div class="form-group">
            <label>Target Release</label>
            <input v-model="form.target_release" class="form-input"/>
          </div>

          <!-- Approval Status -->
          <div class="form-group">
            <label>Approval Status</label>
            <select v-model="form.approval_status" class="form-input">
              <option value="proposed">Proposed</option>
              <option value="approved">Approved</option>
              <option value="planned">Planned</option>
              <option value="delivered">Delivered</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>

          <!-- External Link -->
          <div class="form-group">
            <label>External Link</label>
            <input v-model="form.external_link" class="form-input"/>
          </div>

        </div>

        <div class="form-actions">
          <button type="submit" class="btn-create">
            Update Feature Request
          </button>
        </div>

      </form>

    </div>
  </AuthenticatedLayout>
</template>

<script>
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {

  components:{ AuthenticatedLayout },

  props:{
    feature:Object
  },

  setup(props){

    const form = useForm({

      business_value: props.feature.business_value,
      estimated_effort: props.feature.estimated_effort,
      target_release: props.feature.target_release,
      approval_status: props.feature.approval_status,
      external_link: props.feature.external_link

    })

    function submitForm(){
      form.put(`/feature-requests/${props.feature.id}`)
    }

    function cancel(){
      router.visit('/feature-requests')
    }

    return { form, submitForm, cancel }

  }

}
</script>