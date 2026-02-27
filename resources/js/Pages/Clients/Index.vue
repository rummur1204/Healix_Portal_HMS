<template>
    <AuthenticatedLayout>
        <div class="bg-white dark:bg-primary-900 p-6 rounded-xl shadow">

            <!-- Top Controls -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex gap-3">

                    <input
                        v-model="filters.search"
                        @input="applyFilters"
                        placeholder="Search clients..."
                        class="border px-3 py-2 rounded-lg"
                    />

                    <select
                        v-model="filters.status"
                        @change="applyFilters"
                        class="border px-3 py-2 rounded-lg"
                    >
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="onboarding">Onboarding</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="rejected">Rejected</option>
                        <option value="churned">Churned</option>
                    </select>

                    <select
                        v-model="filters.organization_type_id"
                        @change="applyFilters"
                        class="border px-3 py-2 rounded-lg"
                    >
                        <option value="">All Types</option>
                        <option
                            v-for="type in organizationTypes"
                            :key="type.id"
                            :value="type.id"
                        >
                            {{ type.name }}
                        </option>
                    </select>

                </div>

                <button
                    @click="openCreate"
                    class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg"
                >
                    + Create Client
                </button>
            </div>

            <!-- Table -->
            <table class="w-full border">
                <thead>
                    <tr class="bg-gray-100 dark:bg-primary-800">
                        <th class="p-3 border">Code</th>
                        <th class="p-3 border">Organization Type</th>
                        <th class="p-3 border">Organization</th>
                        <th class="p-3 border">Primary Contact</th>
                        <th class="p-3 border">Status</th>
                        <th class="p-3 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="client in clients.data" :key="client.id">
                        <td class="p-3 border">{{ client.client_code }}</td>
                        <td class="p-3 border">
                            {{ client.organization_type?.name }}
                        </td>
                        <td class="p-3 border">{{ client.organization_name }}</td>
                        <td class="p-3 border">
                            {{ client.primary_contact?.name }}
                            <span v-if="client.primary_contact?.title" class="text-xs text-gray-500 block">
                                {{ client.primary_contact.title }}
                            </span>
                        </td>
                        <td class="p-3 border capitalize">
                            {{ client.status }}
                        </td>
                        <td class="p-3 border space-x-3">

                            <Link
                                :href="route('clients.show', client.id)"
                                class="text-blue-600 hover:underline"
                            >
                                View
                            </Link>

                            <button
                                @click="openStatusModal(client)"
                                class="text-purple-600 hover:underline"
                            >
                                Change Status
                            </button>

                            <button
                                @click="openEdit(client)"
                                class="text-yellow-600 hover:underline"
                            >
                                Edit
                            </button>

                            <button
                                @click="destroy(client.id)"
                                class="text-red-600 hover:underline"
                            >
                                Delete
                            </button>

                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="clients.links" class="mt-6 flex gap-2 flex-wrap">
                <Link
                    v-for="link in clients.links"
                    :key="link.label"
                    :href="link.url || ''"
                    v-html="link.label"
                    class="px-3 py-1 border rounded"
                    :class="{ 'bg-gray-300': link.active }"
                />
            </div>

        </div>

        <!-- CREATE / EDIT MODAL -->
        <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-primary-900 p-6 rounded-xl w-[800px] max-h-[90vh] overflow-y-auto">

                <h3 class="text-xl font-bold mb-6">
                    {{ isEditing ? 'Edit Client' : 'Create Client' }}
                </h3>

                <div class="grid grid-cols-2 gap-4 mb-6">

                      <div>
    <input
        v-model="form.organization_name"
        placeholder="Organization Name"
        class="border p-2 rounded w-full"
        :class="{ 'border-red-500': form.errors.organization_name }"
    />
    <p v-if="form.errors.organization_name" class="text-red-500 text-sm mt-1">
        {{ form.errors.organization_name }}
    </p>
</div>
                    <select v-model="form.organization_type_id" class="border p-2 rounded">
                        <option value="">Select Organization Type</option>
                        <option
                            v-for="type in organizationTypes"
                            :key="type.id"
                            :value="type.id"
                        >
                            {{ type.name }}
                        </option>
                    </select>

                    <input v-model="form.address_country" placeholder="Country" class="border p-2 rounded" />
                    <input v-model="form.address_city" placeholder="City" class="border p-2 rounded" />
                    <input v-model="form.address_line" placeholder="Address Line (Google Map Link)" class="border p-2 rounded col-span-2" />
                    <input v-model="form.tax_reg_id" placeholder="Tax/Registration ID" class="border p-2 rounded" />

                    <select v-model="form.preferred_contact_channel" class="border p-2 rounded">
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                        <option value="both">Both</option>
                    </select>
                </div>

                <!-- Primary Contact with Title -->
                <h4 class="font-semibold mb-2">Primary Contact</h4>
<div class="grid grid-cols-4 gap-4 mb-6">

    <!-- Name -->
    <div>
        <input
            v-model="form.primary_contact.name"
            placeholder="Name *"
            class="border p-2 rounded w-full"
            :class="{ 'border-red-500': form.errors['primary_contact.name'] }"
        />
        <p v-if="form.errors['primary_contact.name']" class="text-red-500 text-sm">
            {{ form.errors['primary_contact.name'] }}
        </p>
    </div>

    <!-- Title -->
    <div>
        <input
            v-model="form.primary_contact.title"
            placeholder="Title/Role"
            class="border p-2 rounded w-full"
        />
    </div>

    <!-- Email -->
    <div>
        <input
            v-model="form.primary_contact.email"
            placeholder="Email *"
            class="border p-2 rounded w-full"
            :class="{ 'border-red-500': form.errors['primary_contact.email'] }"
        />
        <p v-if="form.errors['primary_contact.email']" class="text-red-500 text-sm">
            {{ form.errors['primary_contact.email'] }}
        </p>
    </div>

    <!-- Phone -->
    <div>
        <input
            v-model="form.primary_contact.phone"
            placeholder="Phone *"
            class="border p-2 rounded w-full"
            :class="{ 'border-red-500': form.errors['primary_contact.phone'] }"
        />
        <p v-if="form.errors['primary_contact.phone']" class="text-red-500 text-sm">
            {{ form.errors['primary_contact.phone'] }}
        </p>
    </div>

</div>

                <!-- Additional Contacts with Title -->
                <h4 class="font-semibold mb-2">Additional Contacts</h4>

                <div
    v-for="(contact, index) in form.contacts"
    :key="index"
    class="grid grid-cols-4 gap-4 mb-3 items-start"
>

    <!-- Name -->
    <div>
        <input
            v-model="contact.name"
            placeholder="Name"
            class="border p-2 rounded w-full"
            :class="{ 'border-red-500': form.errors[`contacts.${index}.name`] }"
        />
        <p v-if="form.errors[`contacts.${index}.name`]" class="text-red-500 text-sm">
            {{ form.errors[`contacts.${index}.name`] }}
        </p>
    </div>

    <!-- Title -->
    <div>
        <input
            v-model="contact.title"
            placeholder="Title/Role"
            class="border p-2 rounded w-full"
        />
    </div>

    <!-- Email -->
    <div>
        <input
            v-model="contact.email"
            placeholder="Email"
            class="border p-2 rounded w-full"
            :class="{ 'border-red-500': form.errors[`contacts.${index}.email`] }"
        />
        <p v-if="form.errors[`contacts.${index}.email`]" class="text-red-500 text-sm">
            {{ form.errors[`contacts.${index}.email`] }}
        </p>
    </div>

    <!-- Phone -->
    <div class="flex gap-2">
        <div class="flex-1">
            <input
                v-model="contact.phone"
                placeholder="Phone"
                class="border p-2 rounded w-full"
                :class="{ 'border-red-500': form.errors[`contacts.${index}.phone`] }"
            />
            <p v-if="form.errors[`contacts.${index}.phone`]" class="text-red-500 text-sm">
                {{ form.errors[`contacts.${index}.phone`] }}
            </p>
        </div>

        <button 
            @click="removeContact(index)" 
            class="text-red-600 hover:text-red-800 px-2"
            title="Remove contact"
        >
            ×
        </button>
    </div>

</div>

                <button
                    type="button"
                    @click="addContact"
                    class="text-teal-600 mb-6"
                >
                    + Add Contact
                </button>

                <div class="flex justify-end gap-3">
                    <button @click="closeModal" class="px-4 py-2 border rounded">
                        Cancel
                    </button>
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-teal-600 text-white rounded disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save' }}
                    </button>
                </div>

            </div>
        </div>

        <!-- STATUS CHANGE MODAL -->
        <div v-if="showStatusModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-xl w-[400px]">

                <h3 class="text-lg font-bold mb-4">Change Status</h3>

                <select v-model="statusForm.status" class="border p-2 rounded w-full mb-4">
                    <option value="pending">Pending</option>
                    <option value="onboarding">Onboarding</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="rejected">Rejected</option>
                    <option value="churned">Churned</option>
                </select>

                <textarea
                    v-model="statusForm.reason"
                    placeholder="Reason (optional)"
                    class="border p-2 rounded w-full mb-4"
                />

                <div class="flex justify-end gap-3">
                    <button @click="showStatusModal=false" class="border px-4 py-2 rounded">
                        Cancel
                    </button>
                    <button
                        @click="submitStatus"
                        :disabled="statusForm.processing"
                        class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50"
                    >
                        {{ statusForm.processing ? 'Updating...' : 'Update' }}
                    </button>
                </div>

            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    clients: Object,
    filters: Object,
    organizationTypes: Array
})

/* Filters */
const filters = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    organization_type_id: props.filters?.organization_type_id || ''
})

const applyFilters = () => {
    router.get(route('clients.index'), filters, { preserveState: true })
}

/* Create / Edit */
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

// Base empty form structure
const getEmptyForm = () => ({
    organization_name: '',
    organization_type_id: '',
    address_country: '',
    address_city: '',
    address_line: '',
    tax_reg_id: '',
    preferred_contact_channel: 'email',
    note: '',
    primary_contact: { name: '', title: '', email: '', phone: '' },
    contacts: []
})

const form = useForm(getEmptyForm())

// Reset form to empty state
const resetForm = () => {
    form.reset()
    // Manually reset nested objects since reset() might not handle them deeply
    form.organization_name = ''
    form.organization_type_id = ''
    form.address_country = ''
    form.address_city = ''
    form.address_line = ''
    form.tax_reg_id = ''
    form.preferred_contact_channel = 'email'
    form.note = ''
    form.primary_contact = { name: '', title: '', email: '', phone: '' }
    form.contacts = []
    form.clearErrors()
}

// Open modal for CREATE (empty form)
const openCreate = () => {
    isEditing.value = false
    editingId.value = null
    resetForm()
    showModal.value = true
}

// Open modal for EDIT (prefilled form)
const openEdit = (client) => {
    isEditing.value = true
    editingId.value = client.id
    
    // Transform client data to match form structure
    form.organization_name = client.organization_name || ''
    form.organization_type_id = client.organization_type_id || ''
    form.address_country = client.address_country || ''
    form.address_city = client.address_city || ''
    form.address_line = client.address_line || ''
    form.tax_reg_id = client.tax_reg_id || ''
    form.preferred_contact_channel = client.preferred_contact_channel || 'email'
    form.note = client.note || ''

    // Primary contact
    form.primary_contact = {
        name: client.primary_contact?.name || '',
        title: client.primary_contact?.title || '',
        email: client.primary_contact?.email || '',
        phone: client.primary_contact?.phone || ''
    }

    // Additional contacts (filter out primary and map to form structure)
    form.contacts = client.contacts
        ? client.contacts
            .filter(c => !c.is_primary)
            .map(c => ({
                name: c.name || '',
                title: c.title || '',
                email: c.email || '',
                phone: c.phone || ''
            }))
        : []

    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    form.clearErrors() // Clear any validation errors
}

const addContact = () => {
    form.contacts.push({ name: '', title: '', email: '', phone: '' })
}

const removeContact = (index) => {
    form.contacts.splice(index, 1)
}

const submit = () => {
    if (isEditing.value) {
        form.put(route('clients.update', editingId.value), {
            onSuccess: () => {
                closeModal()
                // Optional: Show success message
            },
            onError: (errors) => {
                console.error('Update failed:', errors)
                // Errors will be automatically available in form.errors
            }
        })
    } else {
        form.post(route('clients.store'), {
            onSuccess: () => {
                closeModal()
                // Optional: Show success message
            },
            onError: (errors) => {
                console.error('Store failed:', errors)
                // Errors will be automatically available in form.errors
            }
        })
    }
}

const destroy = (id) => {
    if (confirm('Delete this client?')) {
        router.delete(route('clients.destroy', id))
    }
}

/* Status Change */
const showStatusModal = ref(false)
const statusClient = ref(null)

const statusForm = useForm({
    status: '',
    reason: ''
})

const openStatusModal = (client) => {
    statusClient.value = client
    statusForm.status = client.status
    statusForm.reason = ''
    showStatusModal.value = true
}

const submitStatus = () => {
    statusForm.post(route('clients.change-status', statusClient.value.id), {
        onSuccess: () => {
            showStatusModal.value = false
            statusForm.reset()
        }
    })
}
</script>