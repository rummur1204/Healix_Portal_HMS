<template>
    <div class="p-6">
        <!-- Header with Add Button -->
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-md font-semibold text-gray-700 dark:text-gray-300">Manage Organization Types</h4>
            <button
                @click="openCreateModal"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Add Type
            </button>
        </div>

        <!-- Debug Info (remove in production) -->
        <div v-if="localOrgTypes.length === 0 && !loading" class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <p class="text-sm text-yellow-600 dark:text-yellow-400">
                No organization types found. Click "Add Type" to create one.
            </p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
        </div>

        <!-- Organization Types Grid -->
        <div v-else-if="localOrgTypes.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="type in localOrgTypes" :key="type.id" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-4 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-gradient-to-br from-green-100 to-teal-100 dark:from-green-900 dark:to-teal-900 rounded-lg">
                            <BuildingOfficeIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h5 class="text-lg font-semibold text-gray-900 dark:text-white">{{ type.name }}</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">ID: {{ type.id }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button
                            @click="editType(type)"
                            class="p-1 text-teal-600 hover:text-teal-800 dark:text-teal-400"
                            title="Edit"
                        >
                            <PencilIcon class="w-4 h-4" />
                        </button>
                        <button
                            @click="confirmDeleteType(type)"
                            class="p-1 text-red-600 hover:text-red-800 dark:text-red-400"
                            title="Delete"
                        >
                            <TrashIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                <p v-if="type.description" class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ type.description }}</p>
                <div class="mt-3 flex items-center justify-between">
                    <span class="text-xs text-gray-500 dark:text-gray-500">
                        {{ type.clients_count || 0 }} clients
                    </span>
                    <span 
                        :class="type.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100'"
                        class="px-2 py-1 text-xs font-medium rounded-full"
                    >
                        {{ type.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <BuildingOfficeIcon class="w-16 h-16 mx-auto text-gray-400 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Organization Types</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Get started by creating your first organization type.</p>
            <button
                @click="openCreateModal"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:shadow-lg transition-shadow"
            >
                <PlusIcon class="w-5 h-5 mr-2" />
                Create Organization Type
            </button>
        </div>

        <!-- Create/Edit Organization Type Modal -->
        <Modal :show="showTypeModal" @close="closeModal" maxWidth="lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ editingType ? 'Edit Organization Type' : 'Create Organization Type' }}
                </h3>
                <form @submit.prevent="saveType">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="typeForm.name"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white"
                                :class="{ 'border-red-500': errors.name }"
                                placeholder="e.g., Hospital, Clinic, Pharmacy"
                                required
                            />
                            <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name[0] || errors.name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Description
                            </label>
                            <textarea
                                v-model="typeForm.description"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white"
                                placeholder="Optional description"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Color
                                </label>
                                <input
                                    v-model="typeForm.color"
                                    type="color"
                                    class="w-full h-10 rounded border border-gray-300 dark:border-gray-600"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Display Order
                                </label>
                                <input
                                    v-model="typeForm.display_order"
                                    type="number"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white"
                                    placeholder="0"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Icon
                            </label>
                            <select v-model="typeForm.icon" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                                <option value="building">Building</option>
                                <option value="hospital">Hospital</option>
                                <option value="clinic">Clinic</option>
                                <option value="pharmacy">Pharmacy</option>
                                <option value="lab">Laboratory</option>
                                <option value="doctor">Doctor</option>
                            </select>
                        </div>

                        <div class="flex items-center">
                            <input
                                v-model="typeForm.is_active"
                                type="checkbox"
                                class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded"
                            />
                            <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</label>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="errorMessage" class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <p class="text-sm text-red-600 dark:text-red-400">{{ errorMessage }}</p>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closeModal" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:shadow-lg transition-all disabled:opacity-50" :disabled="saving">
                            <span v-if="saving" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                            <span v-else>{{ editingType ? 'Update' : 'Create' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false" maxWidth="md">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
                    <ExclamationTriangleIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white text-center mb-2">Delete Organization Type</h3>
                <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
                    Are you sure you want to delete "{{ typeToDelete?.name }}"? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                        Cancel
                    </button>
                    <button @click="deleteType" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                        Delete
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import Modal from '@/Components/Modal.vue'
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    BuildingOfficeIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    orgTypes: Array
})

const emit = defineEmits(['refresh'])

// Local state
const localOrgTypes = ref([])
const loading = ref(false)
const showTypeModal = ref(false)
const showDeleteModal = ref(false)
const editingType = ref(null)
const typeToDelete = ref(null)
const saving = ref(false)
const errors = ref({})
const errorMessage = ref('')

// API Base URL
const API_BASE = '/settings/api'

// Helper function to get CSRF token
const getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
}

// Initialize local data
onMounted(() => {
    console.log('OrganizationType mounted with props:', props.orgTypes)
    if (props.orgTypes && Array.isArray(props.orgTypes)) {
        localOrgTypes.value = props.orgTypes
        console.log('Local orgTypes set to:', localOrgTypes.value)
    } else {
        console.log('props.orgTypes is not an array:', props.orgTypes)
    }
})

// Watch for prop changes
watch(() => props.orgTypes, (newVal) => {
    console.log('orgTypes prop changed:', newVal)
    if (newVal && Array.isArray(newVal)) {
        localOrgTypes.value = newVal
    }
}, { immediate: true, deep: true })

const typeForm = reactive({
    name: '',
    description: '',
    color: '#14b8a6',
    icon: 'building',
    is_active: true,
    display_order: 0
})

const openCreateModal = () => {
    editingType.value = null
    Object.assign(typeForm, {
        name: '',
        description: '',
        color: '#14b8a6',
        icon: 'building',
        is_active: true,
        display_order: 0
    })
    errors.value = {}
    errorMessage.value = ''
    showTypeModal.value = true
}

const editType = (type) => {
    editingType.value = type
    Object.assign(typeForm, {
        name: type.name,
        description: type.description || '',
        color: type.color || '#14b8a6',
        icon: type.icon || 'building',
        is_active: type.is_active,
        display_order: type.display_order || 0
    })
    errors.value = {}
    errorMessage.value = ''
    showTypeModal.value = true
}

const saveType = async () => {
    saving.value = true
    errors.value = {}
    errorMessage.value = ''
    
    try {
        const url = editingType.value ? `${API_BASE}/organization-types/${editingType.value.id}` : `${API_BASE}/organization-types`
        const method = editingType.value ? 'PUT' : 'POST'
        
        const response = await fetch(url, {
            method,
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(typeForm)
        })
        
        const data = await response.json()
        console.log('Save response:', data)
        
        if (response.ok) {
            closeModal()
            emit('refresh')
        } else {
            if (data.errors) {
                errors.value = data.errors
            }
            if (data.error) {
                errorMessage.value = data.error
            }
        }
    } catch (error) {
        console.error('Error saving organization type:', error)
        errorMessage.value = 'Network error. Please try again.'
    } finally {
        saving.value = false
    }
}

const confirmDeleteType = (type) => {
    typeToDelete.value = type
    showDeleteModal.value = true
}

const deleteType = async () => {
    try {
        const response = await fetch(`${API_BASE}/organization-types/${typeToDelete.value.id}`, { 
            method: 'DELETE',
            headers: { 
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        
        const data = await response.json()
        
        if (response.ok) {
            showDeleteModal.value = false
            typeToDelete.value = null
            emit('refresh')
        } else {
            alert(data.error || 'Failed to delete organization type')
        }
    } catch (error) {
        console.error('Error deleting organization type:', error)
        alert('Network error. Please try again.')
    }
}

const closeModal = () => {
    showTypeModal.value = false
    editingType.value = null
    errors.value = {}
    errorMessage.value = ''
}
</script>