<template>
    <div class="p-6">
        <!-- Header with Add Button -->
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-md font-semibold text-gray-700 dark:text-gray-300">Manage Roles</h4>
            <button
                @click="openCreateModal"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-teal-600 hover:from-primary-700 hover:to-teal-700 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Add Role
            </button>
        </div>

        <!-- Roles Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="role in roles" :key="role.id" class="card p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <h5 class="text-lg font-semibold text-gray-900 dark:text-white">{{ role.name }}</h5>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ role.users_count || 0 }} users assigned
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button
                            @click="editRole(role)"
                            class="p-1 text-teal-600 hover:text-teal-800 dark:text-teal-400"
                            title="Edit"
                        >
                            <PencilIcon class="w-4 h-4" />
                        </button>
                        <button
                            @click="confirmDeleteRole(role)"
                            class="p-1 text-red-600 hover:text-red-800 dark:text-red-400"
                            title="Delete"
                            :disabled="role.name === 'super-admin'"
                        >
                            <TrashIcon class="w-4 h-4" :class="{ 'opacity-50 cursor-not-allowed': role.name === 'super-admin' }" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Role Modal -->
        <Modal :show="showRoleModal" @close="closeModal">
            <div class="p-6 max-h-[80vh] overflow-y-auto">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ editingRole ? 'Edit Role' : 'Create New Role' }}
                </h3>
                <form @submit.prevent="saveRole">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role Name</label>
                            <input
                                v-model="roleForm.name"
                                type="text"
                                class="input-field"
                                :class="{ 'border-red-500': errors.name }"
                                :disabled="editingRole?.name === 'super-admin'"
                                required
                            />
                            <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Permissions</label>
                            <div class="space-y-4">
                                <div v-for="group in groupedPermissions" :key="group.name" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h6 class="text-sm font-medium text-gray-900 dark:text-white">{{ group.name }}</h6>
                                        <button
                                            type="button"
                                            @click="toggleGroup(group.permissions)"
                                            class="text-xs text-primary-600 hover:text-primary-700"
                                        >
                                            Select All
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div v-for="permission in group.permissions" :key="permission.id" class="flex items-center">
                                            <input
                                                :id="`perm-${permission.id}`"
                                                v-model="roleForm.permissions"
                                                type="checkbox"
                                                :value="permission.name"
                                                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                                            />
                                            <label :for="`perm-${permission.id}`" class="ml-2 text-xs text-gray-700 dark:text-gray-300">
                                                {{ formatPermissionName(permission.name) }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closeModal" class="btn-outline">Cancel</button>
                        <button type="submit" class="btn-primary" :disabled="saving">
                            {{ saving ? 'Saving...' : (editingRole ? 'Update' : 'Create') }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delete Role</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Are you sure you want to delete "{{ roleToDelete?.name }}"? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button @click="showDeleteModal = false" class="btn-outline">Cancel</button>
                    <button @click="deleteRole" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                        Delete Role
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import Modal from '@/Components/Modal.vue'
import {
    PlusIcon,
    PencilIcon,
    TrashIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    roles: Array,
    permissions: Array
})

const emit = defineEmits(['refresh'])

// API Base URL
const API_BASE = '/settings/api'

// Helper function to get CSRF token
const getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
}

const showRoleModal = ref(false)
const showDeleteModal = ref(false)
const editingRole = ref(null)
const roleToDelete = ref(null)
const saving = ref(false)
const errors = ref({})

const roleForm = reactive({
    name: '',
    permissions: []
})

const groupedPermissions = computed(() => {
    const groups = {}
    props.permissions?.forEach(perm => {
        const [group] = perm.name.split(' ')
        if (!groups[group]) {
            groups[group] = {
                name: group.charAt(0).toUpperCase() + group.slice(1),
                permissions: []
            }
        }
        groups[group].permissions.push(perm)
    })
    return Object.values(groups)
})

const formatPermissionName = (name) => {
    return name.split(' ').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ')
}

const toggleGroup = (permissions) => {
    const permNames = permissions.map(p => p.name)
    const allSelected = permNames.every(p => roleForm.permissions.includes(p))
    
    if (allSelected) {
        roleForm.permissions = roleForm.permissions.filter(p => !permNames.includes(p))
    } else {
        permNames.forEach(p => {
            if (!roleForm.permissions.includes(p)) {
                roleForm.permissions.push(p)
            }
        })
    }
}

const openCreateModal = () => {
    editingRole.value = null
    Object.assign(roleForm, {
        name: '',
        permissions: []
    })
    errors.value = {}
    showRoleModal.value = true
}

const editRole = (role) => {
    editingRole.value = role
    Object.assign(roleForm, {
        name: role.name,
        permissions: role.permissions?.map(p => p.name) || []
    })
    errors.value = {}
    showRoleModal.value = true
}

const saveRole = async () => {
    saving.value = true
    errors.value = {}
    
    try {
        const url = editingRole.value ? `${API_BASE}/roles/${editingRole.value.id}` : `${API_BASE}/roles`
        const method = editingRole.value ? 'PUT' : 'POST'
        
        const response = await fetch(url, {
            method,
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(roleForm)
        })
        
        const data = await response.json()
        
        if (data.success) {
            closeModal()
            emit('refresh')
        } else {
            errors.value = data.errors || {}
            if (data.error) {
                alert(data.error)
            }
        }
    } catch (error) {
        console.error('Error saving role:', error)
        alert('Failed to save role')
    } finally {
        saving.value = false
    }
}
const confirmDeleteRole = (role) => {
    if (role.name === 'super-admin') return
    roleToDelete.value = role
    showDeleteModal.value = true
}

const deleteRole = async () => {
    try {
        await fetch(`${API_BASE}/roles/${roleToDelete.value.id}`, { 
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() }
        })
        showDeleteModal.value = false
        roleToDelete.value = null
        emit('refresh')
    } catch (error) {
        console.error('Error deleting role:', error)
    }
}

const closeModal = () => {
    showRoleModal.value = false
    editingRole.value = null
}
</script>