<template>
    <div class="p-6">
        <!-- Header with Add Button -->
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-md font-semibold text-gray-700 dark:text-gray-300">Manage Users</h4>
            <button
                @click="openCreateModal"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Add User
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
        </div>

        <!-- Users Table -->
        <div v-else-if="localUsers.length > 0" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Roles</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                        <tr v-for="user in localUsers" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-teal-500 flex items-center justify-center text-white font-semibold text-sm">
                                        {{ getUserInitials(user.name) }}
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ user.email }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span 
                                        v-for="role in user.roles" 
                                        :key="role"
                                        class="px-2 py-1 text-xs bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-100 rounded-full"
                                    >
                                        {{ role }}
                                    </span>
                                    <span v-if="!user.roles || user.roles.length === 0" class="text-xs text-gray-500">
                                        No roles
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    :class="user.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100'"
                                    class="px-3 py-1 text-xs font-medium rounded-full"
                                >
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="editUser(user)"
                                        class="p-1 text-teal-600 hover:text-teal-800 dark:text-teal-400 transition-colors"
                                        title="Edit"
                                    >
                                        <PencilIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="toggleUserStatus(user)"
                                        class="p-1 text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 transition-colors"
                                        :title="user.is_active ? 'Deactivate' : 'Activate'"
                                    >
                                        <component :is="user.is_active ? 'PauseIcon' : 'PlayIcon'" class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="confirmDeleteUser(user)"
                                        class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 transition-colors"
                                        title="Delete"
                                        :disabled="user.id === currentUserId"
                                    >
                                        <TrashIcon class="w-4 h-4" :class="{ 'opacity-50 cursor-not-allowed': user.id === currentUserId }" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <UsersIcon class="w-16 h-16 mx-auto text-gray-400 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Users Found</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Get started by creating your first user.</p>
            <button
                @click="openCreateModal"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:shadow-lg transition-shadow"
            >
                <PlusIcon class="w-5 h-5 mr-2" />
                Create User
            </button>
        </div>

        <!-- Create/Edit User Modal -->
        <Modal :show="showUserModal" @close="closeModal" maxWidth="lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ editingUser ? 'Edit User' : 'Create New User' }}
                </h3>
                <form @submit.prevent="saveUser">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="userForm.name"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white"
                                :class="{ 'border-red-500': errors.name }"
                                required
                            />
                            <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name[0] || errors.name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="userForm.email"
                                type="email"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white"
                                :class="{ 'border-red-500': errors.email }"
                                required
                            />
                            <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email[0] || errors.email }}</p>
                        </div>
                        
                        <div v-if="!editingUser">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="userForm.password"
                                type="password"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white"
                                :class="{ 'border-red-500': errors.password }"
                                required
                            />
                            <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password[0] || errors.password }}</p>
                        </div>

                        <div v-if="editingUser">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                New Password (leave blank to keep current)
                            </label>
                            <input
                                v-model="userForm.password"
                                type="password"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white"
                                placeholder="Enter new password"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Roles
                            </label>
                            <div class="space-y-2 max-h-40 overflow-y-auto p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div v-if="allRoles.length === 0" class="text-sm text-gray-500 text-center py-2">
                                    Loading roles...
                                </div>
                                <div v-for="role in allRoles" :key="role.id" class="flex items-center">
                                    <input
                                        :id="`role-${role.id}`"
                                        v-model="userForm.roles"
                                        type="checkbox"
                                        :value="role.name"
                                        class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded"
                                    />
                                    <label :for="`role-${role.id}`" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ role.name }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <input
                                v-model="userForm.is_active"
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
                            <span v-else>{{ editingUser ? 'Update' : 'Create' }}</span>
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-white text-center mb-2">Delete User</h3>
                <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
                    Are you sure you want to delete "{{ userToDelete?.name }}"? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                        Cancel
                    </button>
                    <button @click="deleteUser" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                        Delete User
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
    PauseIcon,
    PlayIcon,
    UsersIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    users: Array
})

const emit = defineEmits(['refresh'])

// Local state
const localUsers = ref([])
const allRoles = ref([])
const loading = ref(false)
const showUserModal = ref(false)
const showDeleteModal = ref(false)
const editingUser = ref(null)
const userToDelete = ref(null)
const saving = ref(false)
const errors = ref({})
const errorMessage = ref('')
const currentUserId = ref(null)

// API Base URL
const API_BASE = '/settings/api'

// Helper function to get CSRF token
const getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
}

// Initialize local data
onMounted(() => {
    // Get current user ID from meta tag if available
    const userMeta = document.querySelector('meta[name="user-id"]')
    if (userMeta) {
        currentUserId.value = parseInt(userMeta.getAttribute('content'))
    }
    
    if (props.users && Array.isArray(props.users)) {
        localUsers.value = props.users
    }
    fetchRoles()
})

// Watch for prop changes
watch(() => props.users, (newVal) => {
    if (newVal && Array.isArray(newVal)) {
        localUsers.value = newVal
    }
}, { immediate: true, deep: true })

const userForm = reactive({
    name: '',
    email: '',
    password: '',
    roles: [],
    is_active: true
})

const getUserInitials = (name) => {
    if (!name) return '?'
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const fetchRoles = async () => {
    try {
        const response = await fetch(`${API_BASE}/roles/list`, {
            headers: { 
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        const data = await response.json()
        if (data.data) {
            allRoles.value = data.data
        } else if (Array.isArray(data)) {
            allRoles.value = data
        }
    } catch (error) {
        console.error('Error fetching roles:', error)
    }
}

const openCreateModal = async () => {
    await fetchRoles()
    editingUser.value = null
    Object.assign(userForm, {
        name: '',
        email: '',
        password: '',
        roles: [],
        is_active: true
    })
    errors.value = {}
    errorMessage.value = ''
    showUserModal.value = true
}

const editUser = async (user) => {
    await fetchRoles()
    editingUser.value = user
    Object.assign(userForm, {
        name: user.name,
        email: user.email,
        password: '',
        roles: user.roles || [],
        is_active: user.is_active
    })
    errors.value = {}
    errorMessage.value = ''
    showUserModal.value = true
}

const saveUser = async () => {
    saving.value = true
    errors.value = {}
    errorMessage.value = ''
    
    try {
        const url = editingUser.value ? `${API_BASE}/users/${editingUser.value.id}` : `${API_BASE}/users`
        const method = editingUser.value ? 'PUT' : 'POST'
        
        const response = await fetch(url, {
            method,
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(userForm)
        })
        
        const data = await response.json()
        
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
        console.error('Error saving user:', error)
        errorMessage.value = 'Network error. Please try again.'
    } finally {
        saving.value = false
    }
}

const toggleUserStatus = async (user) => {
    try {
        const response = await fetch(`${API_BASE}/users/${user.id}/toggle-status`, { 
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        
        const data = await response.json()
        
        if (response.ok) {
            emit('refresh')
        } else {
            alert(data.error || 'Failed to toggle user status')
        }
    } catch (error) {
        console.error('Error toggling user status:', error)
        alert('Network error. Please try again.')
    }
}

const confirmDeleteUser = (user) => {
    userToDelete.value = user
    showDeleteModal.value = true
}

const deleteUser = async () => {
    try {
        const response = await fetch(`${API_BASE}/users/${userToDelete.value.id}`, { 
            method: 'DELETE',
            headers: { 
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        
        const data = await response.json()
        
        if (response.ok) {
            showDeleteModal.value = false
            userToDelete.value = null
            emit('refresh')
        } else {
            alert(data.error || 'Failed to delete user')
        }
    } catch (error) {
        console.error('Error deleting user:', error)
        alert('Network error. Please try again.')
    }
}

const closeModal = () => {
    showUserModal.value = false
    editingUser.value = null
    errors.value = {}
    errorMessage.value = ''
}
</script>