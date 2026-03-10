<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Toggle Ticket Status
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Flash Message -->
        <div
          v-if="$page.props.flash?.success"
          class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded"
        >
          {{ $page.props.flash.success }}
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <!-- Ticket Status Card -->
            <div class="border rounded-lg p-6 mb-6">
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">
                  Ticket #{{ ticket.id }}: {{ ticket.title }}
                </h3>
                <span
                  class="px-3 py-1 rounded-full text-sm font-semibold"
                  :class="ticket.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                >
                  {{ ticket.active ? 'Active' : 'Inactive' }}
                </span>
              </div>

              <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <span class="text-sm text-gray-600">Client:</span>
                  <p class="font-medium">{{ ticket.client?.name || 'N/A' }}</p>
                </div>
                <div>
                  <span class="text-sm text-gray-600">Priority:</span>
                  <p class="font-medium">
                    <span
                      class="px-2 py-1 rounded-full text-xs"
                      :class="{
                        'bg-green-100 text-green-800': ticket.priority === 'low',
                        'bg-yellow-100 text-yellow-800': ticket.priority === 'medium',
                        'bg-orange-100 text-orange-800': ticket.priority === 'high',
                        'bg-red-100 text-red-800': ticket.priority === 'critical'
                      }"
                    >
                      {{ ticket.priority?.toUpperCase() }}
                    </span>
                  </p>
                </div>
                <div>
                  <span class="text-sm text-gray-600">Assigned To:</span>
                  <p class="font-medium">{{ ticket.assigned_to?.name || 'Unassigned' }}</p>
                </div>
                <div>
                  <span class="text-sm text-gray-600">Comments:</span>
                  <p class="font-medium">{{ ticket.comments_count || 0 }} comments</p>
                </div>
              </div>

              <!-- Toggle Button -->
              <div class="flex justify-center mt-6">
                <button
                  @click="toggleStatus"
                  :disabled="processing"
                  class="px-6 py-3 rounded-lg font-semibold text-white transition-all transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2"
                  :class="[
                    ticket.active
                      ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
                      : 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
                    processing && 'opacity-50 cursor-not-allowed'
                  ]"
                >
                  <span v-if="processing" class="flex items-center">
                    <svg
                      class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                    >
                      <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                      ></circle>
                      <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                      ></path>
                    </svg>
                    Processing...
                  </span>
                  <span v-else>
                    {{ ticket.active ? 'Deactivate Ticket' : 'Activate Ticket' }}
                  </span>
                </button>
              </div>
            </div>

            <!-- History Timeline -->
            <div v-if="ticket.histories && ticket.histories.length > 0" class="mt-8">
              <h3 class="text-lg font-medium mb-4">Status History</h3>
              <div class="relative">
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                <div class="space-y-6">
                  <div
                    v-for="history in ticket.histories"
                    :key="history.id"
                    class="relative pl-10"
                    :class="{ 'opacity-75': history.changed_field !== 'active' }"
                  >
                    <div
                      class="absolute left-2 w-4 h-4 rounded-full border-2 border-white shadow"
                      :class="history.changed_field === 'active' ? 'bg-blue-500' : 'bg-gray-400'"
                    ></div>
                    <div class="bg-gray-50 rounded-lg p-4">
                      <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-900">
                          {{ history.changed_field === 'active' ? 'Status Changed' : history.changed_field.replace('_', ' ').toUpperCase() }}
                        </span>
                        <span class="text-xs text-gray-500">
                          {{ formatDate(history.created_at) }}
                        </span>
                      </div>
                      <p class="text-sm text-gray-600">
                        <span class="font-medium">From:</span>
                        <span :class="history.old_value === 'Active' ? 'text-green-600' : 'text-red-600'">
                          {{ history.old_value || 'N/A' }}
                        </span>
                      </p>
                      <p class="text-sm text-gray-600">
                        <span class="font-medium">To:</span>
                        <span :class="history.new_value === 'Active' ? 'text-green-600' : 'text-red-600'">
                          {{ history.new_value }}
                        </span>
                      </p>
                      <p class="text-xs text-gray-500 mt-2">
                        Changed by: {{ history.changed_by?.name || 'System' }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between mt-8 pt-6 border-t">
              <Link :href="route('tickets.edit', ticket.id)" class="text-blue-600 hover:text-blue-900">
                ← Edit Ticket
              </Link>
              <Link :href="route('tickets.index')" class="text-gray-600 hover:text-gray-900">
                Back to List
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
  components: { AuthenticatedLayout, Link },
  props: { ticket: Object },
  data() {
    return { processing: false };
  },
  methods: {
    toggleStatus() {
      this.processing = true;

      // Use Inertia router PUT
      router.put(
        route('tickets.toggle.active', this.ticket.id),
        {},
        {
          preserveScroll: true,
          onSuccess: () => {
            this.ticket.active = !this.ticket.active;
            this.processing = false;
          },
          onError: () => {
            alert('Failed to toggle ticket status.');
            this.processing = false;
          },
        }
      );
    },

    formatDate(date) {
      return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    },
  },
};
</script>