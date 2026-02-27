<template>
  <div class="tickets-container">
    <div class="header-section">
      <h1 class="page-title">Edit Ticket Tag</h1>
    </div>

    <form @submit.prevent="submitTag">
      <div class="filters-section">
        <div class="filter-group-vertical">
          <label>
            Name
            <input type="text" v-model="form.name" required class="filter-input" />
          </label>

          <label>
            Color
            <input type="color" v-model="form.color" class="filter-input" />
          </label>

          <label class="checkbox-label">
            <input type="checkbox" v-model="form.is_active" /> Active
          </label>

          <button type="submit" class="btn-create">Update Tag</button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import { Inertia } from '@inertiajs/inertia';

export default {
  props: {
    tag: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      form: {
        name: this.tag.name || '',
        color: this.tag.color || '#3B82F6',
        is_active: this.tag.is_active ?? true
      }
    };
  },
  methods: {
    submitTag() {
      Inertia.put(`/ticket-tags/${this.tag.id}`, this.form, {
        onSuccess: () => {
          this.$inertia.visit('/ticket-tags');
        },
        onError: (errors) => {
          console.error(errors);
        }
      });
    }
  }
};
</script>

<style scoped>
/* You can reuse all your previous CSS from the index page */
.tickets-container { padding: 24px; background: #f0f7f0; min-height: 100vh; }
.header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.page-title { font-size: 28px; font-weight: 600; color: #2c5e2c; margin: 0; }
.btn-create { background: #69f069; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 16px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background 0.3s ease; }
.btn-create:hover { background: #69f369; }
.filters-section { background: white; border-radius: 12px; padding: 20px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(44, 94, 44, 0.1); }
.filter-group-vertical { display: flex; flex-direction: column; gap: 16px; }
.filter-input { padding: 10px 16px; border: 2px solid #e0e8e0; border-radius: 8px; font-size: 14px; transition: border-color 0.3s ease; }
.filter-input:focus { outline: none; border-color: #69d169; }
.checkbox-label { display: flex; align-items: center; gap: 8px; font-weight: 500; }
</style>