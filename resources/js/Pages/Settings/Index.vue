<template>
    <AuthenticatedLayout>
        <template #breadcrumbs>
            <div class="flex items-center space-x-2 text-sm">
                <Link href="/dashboard" class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">Dashboard</Link>
                <span class="text-primary-400 dark:text-primary-600">/</span>
                <span class="text-primary-800 dark:text-primary-200 font-medium">Settings</span>
            </div>
        </template>

        <div class="space-y-5">
            <!-- User Management Accordion -->
            <div class="bg-white dark:bg-primary-900/50 rounded-xl border border-primary-200 dark:border-primary-800 overflow-hidden shadow-sm">
                <button
                    @click="toggleAccordion('users')"
                    class="w-full px-6 py-5 flex items-center justify-between bg-primary-600 dark:bg-primary-700 hover:bg-primary-700 dark:hover:bg-primary-600 transition-all duration-200"
                >
                    <div class="flex items-center space-x-4">
                        <div class="p-2.5 bg-white/20 rounded-lg">
                            <UserGroupIcon class="w-6 h-6 text-white" />
                        </div>
                        <div class="text-left">
                            <h3 class="text-lg font-semibold text-white">User</h3>
                            <p class="text-sm text-primary-100 dark:text-primary-300 mt-0.5">Manage system users and their roles</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="px-3 py-1.5 bg-white/20 text-white text-sm font-medium rounded-lg border border-white/30">
                            {{ users.length }} Users
                        </span>
                        <ChevronDownIcon 
                            class="w-5 h-5 text-white transition-transform duration-300"
                            :class="{ 'rotate-180': openAccordion === 'users' }"
                        />
                    </div>
                </button>
                
                <transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="max-h-0 opacity-0"
                    enter-to-class="max-h-[2000px] opacity-100"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="max-h-[2000px] opacity-100"
                    leave-to-class="max-h-0 opacity-0"
                >
                    <div v-show="openAccordion === 'users'" class="p-6 border-t border-primary-200 dark:border-primary-800">
                        <!-- Users Header with Add Button -->
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-base font-medium text-primary-700 dark:text-primary-400">System Users</h4>
                            <button
                                @click="openUserModal"
                                class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 dark:bg-primary-600 dark:hover:bg-primary-500 text-white font-medium rounded-lg transition-all duration-200 flex items-center shadow-md"
                            >
                                <PlusIcon class="w-4 h-4 mr-2" />
                                Add User
                            </button>
                        </div>

                        <!-- Users Grid - Square Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="user in users" :key="user.id" 
                                 class="bg-white dark:bg-primary-800/60 rounded-xl border border-primary-200 dark:border-primary-700 hover:border-primary-400 dark:hover:border-primary-500 transition-all duration-300 overflow-hidden shadow-sm">
                                
                                <!-- Card Header with Avatar -->
                                <div class="p-5">
                                    <div class="flex items-center space-x-4">
                                        <div class="relative">
                                            <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-primary-600 to-teal-600 flex items-center justify-center text-white text-xl font-bold shadow-md">
                                                {{ getUserInitials(user.name) }}
                                            </div>
                                            <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white dark:border-primary-800"
                                                 :class="user.is_active ? 'bg-green-500' : 'bg-gray-400'">
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-lg font-semibold text-primary-900 dark:text-white truncate">{{ user.name }}</h4>
                                            <p class="text-sm text-primary-600 dark:text-primary-400 truncate mt-1">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Roles Section -->
                                <div class="px-5 py-4 border-t border-primary-100 dark:border-primary-700">
                                    <p class="text-xs font-medium text-primary-600 dark:text-primary-400 mb-3">Assigned Roles</p>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="role in user.roles" :key="role"
                                              class="px-3 py-1.5 bg-primary-100 dark:bg-primary-700 text-primary-700 dark:text-primary-300 text-sm font-medium rounded-lg border border-primary-200 dark:border-primary-600">
                                            {{ role }}
                                        </span>
                                        <span v-if="!user.roles || user.roles.length === 0" 
                                              class="text-primary-500 dark:text-primary-400 text-sm italic">No roles assigned</span>
                                    </div>
                                </div>

                                <!-- Actions - Only Edit and Delete buttons -->
                                <div class="px-5 py-4 bg-primary-50/50 dark:bg-primary-900/30 border-t border-primary-100 dark:border-primary-700 flex items-center justify-end space-x-3">
                                    <button
                                        @click="editUser(user)"
                                        class="p-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-all duration-200 shadow-sm"
                                        title="Edit User"
                                    >
                                        <PencilIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="confirmDeleteUser(user)"
                                        class="p-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-200 shadow-sm"
                                        title="Delete User"
                                    >
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="!users || users.length === 0" class="text-center py-16">
                            <UserGroupIcon class="w-20 h-20 mx-auto text-primary-300 dark:text-primary-700 mb-4" />
                            <h3 class="text-xl font-medium text-primary-900 dark:text-white mb-3">No users found</h3>
                            <p class="text-primary-600 dark:text-primary-400 mb-6">Get started by creating your first user.</p>
                            <button
                                @click="openUserModal"
                                class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg inline-flex items-center shadow-md"
                            >
                                <PlusIcon class="w-5 h-5 mr-2" />
                                Create First User
                            </button>
                        </div>
                    </div>
                </transition>
            </div>

            <!-- Role Management Accordion -->
            <div class="bg-white dark:bg-primary-900/50 rounded-xl border border-primary-200 dark:border-primary-800 overflow-hidden shadow-sm">
                <button
                    @click="toggleAccordion('roles')"
                    class="w-full px-6 py-5 flex items-center justify-between bg-primary-600 dark:bg-primary-700 hover:bg-primary-700 dark:hover:bg-primary-600 transition-all duration-200"
                >
                    <div class="flex items-center space-x-4">
                        <div class="p-2.5 bg-white/20 rounded-lg">
                            <ShieldCheckIcon class="w-6 h-6 text-white" />
                        </div>
                        <div class="text-left">
                            <h3 class="text-lg font-semibold text-white">Role</h3>
                            <p class="text-sm text-primary-100 dark:text-primary-300 mt-0.5">Configure roles and permissions</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="px-3 py-1.5 bg-white/20 text-white text-sm font-medium rounded-lg border border-white/30">
                            {{ roles.length }} Roles
                        </span>
                        <ChevronDownIcon 
                            class="w-5 h-5 text-white transition-transform duration-300"
                            :class="{ 'rotate-180': openAccordion === 'roles' }"
                        />
                    </div>
                </button>
                
                <transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="max-h-0 opacity-0"
                    enter-to-class="max-h-[2000px] opacity-100"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="max-h-[2000px] opacity-100"
                    leave-to-class="max-h-0 opacity-0"
                >
                    <div v-show="openAccordion === 'roles'" class="p-6 border-t border-primary-200 dark:border-primary-800">
                        <!-- Roles Header with Add Button -->
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-base font-medium text-primary-700 dark:text-primary-400">System Roles</h4>
                            <button
                                @click="openRoleModal"
                                class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 dark:bg-primary-600 dark:hover:bg-primary-500 text-white font-medium rounded-lg transition-all duration-200 flex items-center shadow-md"
                            >
                                <PlusIcon class="w-4 h-4 mr-2" />
                                Add Role
                            </button>
                        </div>

                        <!-- Roles Table -->
                        <div class="bg-white dark:bg-primary-800/60 rounded-xl border border-primary-200 dark:border-primary-700 overflow-hidden">
                            <table class="min-w-full divide-y divide-primary-200 dark:divide-primary-700">
                                <thead class="bg-primary-50 dark:bg-primary-800">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-primary-700 dark:text-primary-400 uppercase tracking-wider">Role Name</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-primary-700 dark:text-primary-400 uppercase tracking-wider">Users</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-primary-700 dark:text-primary-400 uppercase tracking-wider">Permissions</th>
                                        <th class="px-6 py-4 text-right text-xs font-medium text-primary-700 dark:text-primary-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-primary-200 dark:divide-primary-700">
                                    <tr v-for="role in roles" :key="role.id" class="hover:bg-primary-50 dark:hover:bg-primary-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary-600 to-teal-600 flex items-center justify-center mr-3 shadow-sm">
                                                    <ShieldCheckIcon class="w-5 h-5 text-white" />
                                                </div>
                                                <span class="text-base font-medium text-primary-900 dark:text-white">{{ role.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1.5 bg-primary-100 dark:bg-primary-700 text-primary-700 dark:text-primary-300 text-sm font-medium rounded-lg border border-primary-200 dark:border-primary-600">{{ role.users_count || 0 }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1.5 max-w-xl">
                                                <span v-for="perm in role.permissions?.slice(0, 4)" :key="perm.id"
                                                      class="px-3 py-1.5 bg-primary-100 dark:bg-primary-700 text-primary-700 dark:text-primary-300 text-sm rounded-lg border border-primary-200 dark:border-primary-600">
                                                    {{ perm.name }}
                                                </span>
                                                <span v-if="role.permissions?.length > 4"
                                                      class="px-3 py-1.5 bg-primary-100 dark:bg-primary-700 text-primary-700 dark:text-primary-300 text-sm rounded-lg border border-primary-200 dark:border-primary-600">
                                                    +{{ role.permissions.length - 4 }} more
                                                </span>
                                                <span v-if="!role.permissions || role.permissions.length === 0"
                                                      class="text-primary-500 dark:text-primary-400 text-sm italic">No permissions</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end space-x-2">
                                                <button
                                                    @click="editRole(role)"
                                                    class="p-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-all duration-200 shadow-sm"
                                                    title="Edit Role"
                                                >
                                                    <PencilIcon class="w-4 h-4" />
                                                </button>
                                                <button
                                                    @click="confirmDeleteRole(role)"
                                                    class="p-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-200 shadow-sm"
                                                    title="Delete Role"
                                                >
                                                    <TrashIcon class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
                        <div v-if="!roles || roles.length === 0" class="text-center py-16 mt-4">
                            <ShieldCheckIcon class="w-20 h-20 mx-auto text-primary-300 dark:text-primary-700 mb-4" />
                            <h3 class="text-xl font-medium text-primary-900 dark:text-white mb-3">No roles found</h3>
                            <p class="text-primary-600 dark:text-primary-400 mb-6">Get started by creating your first role.</p>
                            <button
                                @click="openRoleModal"
                                class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg inline-flex items-center shadow-md"
                            >
                                <PlusIcon class="w-5 h-5 mr-2" />
                                Create First Role
                            </button>
                        </div>
                    </div>
                </transition>
            </div>

            <!-- Organization Types Accordion -->
            <div class="bg-white dark:bg-primary-900/50 rounded-xl border border-primary-200 dark:border-primary-800 overflow-hidden shadow-sm">
                <button
                    @click="toggleAccordion('orgTypes')"
                    class="w-full px-6 py-5 flex items-center justify-between bg-primary-600 dark:bg-primary-700 hover:bg-primary-700 dark:hover:bg-primary-600 transition-all duration-200"
                >
                    <div class="flex items-center space-x-4">
                        <div class="p-2.5 bg-white/20 rounded-lg">
                            <BuildingOfficeIcon class="w-6 h-6 text-white" />
                        </div>
                        <div class="text-left">
                            <h3 class="text-lg font-semibold text-white">Organization Types</h3>
                            <p class="text-sm text-primary-100 dark:text-primary-300 mt-0.5">Manage organization categories</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="px-3 py-1.5 bg-white/20 text-white text-sm font-medium rounded-lg border border-white/30">
                            {{ orgTypes.length }} Types
                        </span>
                        <ChevronDownIcon 
                            class="w-5 h-5 text-white transition-transform duration-300"
                            :class="{ 'rotate-180': openAccordion === 'orgTypes' }"
                        />
                    </div>
                </button>
                
                <transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="max-h-0 opacity-0"
                    enter-to-class="max-h-[2000px] opacity-100"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="max-h-[2000px] opacity-100"
                    leave-to-class="max-h-0 opacity-0"
                >
                    <div v-show="openAccordion === 'orgTypes'" class="p-6 border-t border-primary-200 dark:border-primary-800">
                        <!-- Organization Types Header with Add Button -->
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-base font-medium text-primary-700 dark:text-primary-400">Organization Types</h4>
                            <button
                                @click="openOrgTypeModal"
                                class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 dark:bg-primary-600 dark:hover:bg-primary-500 text-white font-medium rounded-lg transition-all duration-200 flex items-center shadow-md"
                            >
                                <PlusIcon class="w-4 h-4 mr-2" />
                                Add Type
                            </button>
                        </div>

                        <!-- Organization Types Grid - Square Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="type in orgTypes" :key="type.id" 
                                 class="bg-white dark:bg-primary-800/60 rounded-xl border border-primary-200 dark:border-primary-700 hover:border-primary-400 dark:hover:border-primary-500 transition-all duration-300 overflow-hidden shadow-sm">
                                
                                <div class="p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-600 to-teal-600 flex items-center justify-center shadow-md">
                                                <component :is="getTypeIcon(type.name)" class="w-7 h-7 text-white" />
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-primary-900 dark:text-white">{{ type.name }}</h4>
                                                <div class="flex items-center mt-2 space-x-2">
                                                    <span class="text-sm text-primary-600 dark:text-primary-400">{{ type.clients_count || 0 }} clients</span>
                                                    <span class="w-1.5 h-1.5 rounded-full bg-primary-300 dark:bg-primary-600"></span>
                                                    <span :class="type.is_active ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                                          class="px-3 py-1 text-xs font-medium rounded-lg border border-primary-200 dark:border-primary-600">
                                                        {{ type.is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <p v-if="type.description" class="mt-3 text-sm text-primary-600 dark:text-primary-400 leading-relaxed">
                                        {{ type.description }}
                                    </p>
                                </div>

                                <!-- Actions - Only Edit and Delete buttons -->
                                <div class="px-6 py-4 bg-primary-50/50 dark:bg-primary-900/30 border-t border-primary-100 dark:border-primary-700 flex items-center justify-end space-x-3">
                                    <button
                                        @click="editOrgType(type)"
                                        class="p-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-all duration-200 shadow-sm"
                                        title="Edit Organization Type"
                                    >
                                        <PencilIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="confirmDeleteOrgType(type)"
                                        class="p-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-200 shadow-sm"
                                        title="Delete Organization Type"
                                    >
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="!orgTypes || orgTypes.length === 0" class="text-center py-16 mt-4">
                            <BuildingOfficeIcon class="w-20 h-20 mx-auto text-primary-300 dark:text-primary-700 mb-4" />
                            <h3 class="text-xl font-medium text-primary-900 dark:text-white mb-3">No organization types found</h3>
                            <p class="text-primary-600 dark:text-primary-400 mb-6">Get started by creating your first type.</p>
                            <button
                                @click="openOrgTypeModal"
                                class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg inline-flex items-center shadow-md"
                            >
                                <PlusIcon class="w-5 h-5 mr-2" />
                                Create First Type
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </div>

        <!-- User Modal -->
        <Modal :show="showUserModal" @close="closeUserModal">
            <div class="p-6 bg-white dark:bg-primary-800 rounded-xl border border-primary-200 dark:border-primary-700 shadow-xl">
                <h3 class="text-xl font-semibold text-primary-900 dark:text-white mb-6">
                    {{ editingUser ? 'Edit User' : 'Create New User' }}
                </h3>
                <form @submit.prevent="saveUser" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-primary-700 dark:text-primary-400 mb-2">Name</label>
                        <input v-model="userForm.name" type="text" 
                               class="w-full px-4 py-3 bg-white dark:bg-primary-700 border border-primary-300 dark:border-primary-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-primary-900 dark:text-white placeholder-primary-400 dark:placeholder-primary-500"
                               placeholder="Enter full name"
                               required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-primary-700 dark:text-primary-400 mb-2">Email</label>
                        <input v-model="userForm.email" type="email" 
                               class="w-full px-4 py-3 bg-white dark:bg-primary-700 border border-primary-300 dark:border-primary-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-primary-900 dark:text-white placeholder-primary-400 dark:placeholder-primary-500"
                               placeholder="Enter email address"
                               required />
                    </div>
                    <div v-if="!editingUser">
                        <label class="block text-sm font-medium text-primary-700 dark:text-primary-400 mb-2">Password</label>
                        <input v-model="userForm.password" type="password" 
                               class="w-full px-4 py-3 bg-white dark:bg-primary-700 border border-primary-300 dark:border-primary-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-primary-900 dark:text-white placeholder-primary-400 dark:placeholder-primary-500"
                               placeholder="Enter password"
                               required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-primary-700 dark:text-primary-400 mb-2">Roles</label>
                        <div class="space-y-2 max-h-48 overflow-y-auto p-4 bg-primary-50 dark:bg-primary-700 border border-primary-200 dark:border-primary-600 rounded-lg">
                            <div v-for="role in roles" :key="role.id" class="flex items-center py-1">
                                <input type="checkbox" :value="role.name" v-model="userForm.roles" 
                                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-primary-300 dark:border-primary-500 rounded" />
                                <label class="ml-3 text-sm text-primary-900 dark:text-white">{{ role.name }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center py-2">
                        <input type="checkbox" v-model="userForm.is_active" 
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-primary-300 dark:border-primary-500 rounded" />
                        <label class="ml-3 text-sm text-primary-900 dark:text-white">Active</label>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="closeUserModal" 
                                class="px-5 py-2.5 bg-white dark:bg-primary-700 border border-primary-300 dark:border-primary-600 text-primary-700 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-600 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-md">
                            {{ editingUser ? 'Update User' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Role Modal -->
        <Modal :show="showRoleModal" @close="closeRoleModal">
            <div class="p-6 bg-white dark:bg-primary-800 rounded-xl border border-primary-200 dark:border-primary-700 shadow-xl max-h-[80vh] overflow-y-auto">
                <h3 class="text-xl font-semibold text-primary-900 dark:text-white mb-6">
                    {{ editingRole ? 'Edit Role' : 'Create New Role' }}
                </h3>
                <form @submit.prevent="saveRole" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-primary-700 dark:text-primary-400 mb-2">Role Name</label>
                        <input v-model="roleForm.name" type="text" 
                               class="w-full px-4 py-3 bg-white dark:bg-primary-700 border border-primary-300 dark:border-primary-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-primary-900 dark:text-white placeholder-primary-400 dark:placeholder-primary-500"
                               placeholder="Enter role name"
                               required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-primary-700 dark:text-primary-400 mb-3">Permissions</label>
                        <div class="space-y-4">
                            <div v-for="group in groupedPermissions" :key="group.name" 
                                 class="border border-primary-200 dark:border-primary-700 rounded-lg p-4 bg-primary-50 dark:bg-primary-700/50">
                                <h6 class="text-sm font-medium text-primary-700 dark:text-primary-400 mb-3">{{ group.name }}</h6>
                                <div class="grid grid-cols-2 gap-3">
                                    <div v-for="perm in group.permissions" :key="perm.id" class="flex items-center">
                                        <input type="checkbox" :value="perm.name" v-model="roleForm.permissions" 
                                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-primary-300 dark:border-primary-500 rounded" />
                                        <label class="ml-3 text-sm text-primary-900 dark:text-white">{{ formatPermissionName(perm.name) }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="closeRoleModal" 
                                class="px-5 py-2.5 bg-white dark:bg-primary-700 border border-primary-300 dark:border-primary-600 text-primary-700 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-600 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-md">
                            {{ editingRole ? 'Update Role' : 'Create Role' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Organization Type Modal -->
        <Modal :show="showOrgTypeModal" @close="closeOrgTypeModal">
            <div class="p-6 bg-white dark:bg-primary-800 rounded-xl border border-primary-200 dark:border-primary-700 shadow-xl">
                <h3 class="text-xl font-semibold text-primary-900 dark:text-white mb-6">
                    {{ editingOrgType ? 'Edit Organization Type' : 'Create Organization Type' }}
                </h3>
                <form @submit.prevent="saveOrgType" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-primary-700 dark:text-primary-400 mb-2">Name</label>
                        <input v-model="orgTypeForm.name" type="text" 
                               class="w-full px-4 py-3 bg-white dark:bg-primary-700 border border-primary-300 dark:border-primary-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-primary-900 dark:text-white placeholder-primary-400 dark:placeholder-primary-500"
                               placeholder="e.g., Hospital, Clinic, Pharmacy"
                               required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-primary-700 dark:text-primary-400 mb-2">Description</label>
                        <textarea v-model="orgTypeForm.description" rows="3" 
                                  class="w-full px-4 py-3 bg-white dark:bg-primary-700 border border-primary-300 dark:border-primary-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-primary-900 dark:text-white placeholder-primary-400 dark:placeholder-primary-500"
                                  placeholder="Optional description"></textarea>
                    </div>
                    <div class="flex items-center py-2">
                        <input type="checkbox" v-model="orgTypeForm.is_active" 
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-primary-300 dark:border-primary-500 rounded" />
                        <label class="ml-3 text-sm text-primary-900 dark:text-white">Active</label>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="closeOrgTypeModal" 
                                class="px-5 py-2.5 bg-white dark:bg-primary-700 border border-primary-300 dark:border-primary-600 text-primary-700 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-600 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-md">
                            {{ editingOrgType ? 'Update Type' : 'Create Type' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6 bg-white dark:bg-primary-800 rounded-xl border border-primary-200 dark:border-primary-700 shadow-xl">
                <h3 class="text-xl font-semibold text-primary-900 dark:text-white mb-4">Confirm Delete</h3>
                <p class="text-primary-600 dark:text-primary-400 mb-6 text-base">Are you sure you want to delete this item? This action cannot be undone.</p>
                <div class="flex justify-end space-x-3">
                    <button @click="showDeleteModal = false" 
                            class="px-5 py-2.5 bg-white dark:bg-primary-700 border border-primary-300 dark:border-primary-600 text-primary-700 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-600 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button @click="executeDelete" 
                            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-md">
                        Delete
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import {
    UserGroupIcon,
    ShieldCheckIcon,
    BuildingOfficeIcon,
    PlusIcon,
    PencilIcon,
    TrashIcon,
    ChevronDownIcon,
    BuildingLibraryIcon,
    BeakerIcon,
    HomeModernIcon,
    Cog6ToothIcon
} from '@heroicons/vue/24/outline'

// Helper function to get CSRF token
const getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
}

// Base URL for API calls
const API_BASE = '/settings/api'

// State
const openAccordion = ref('users')
const users = ref([])
const roles = ref([])
const permissions = ref([])
const orgTypes = ref([])

// Modal states
const showUserModal = ref(false)
const showRoleModal = ref(false)
const showOrgTypeModal = ref(false)
const showDeleteModal = ref(false)

// Edit states
const editingUser = ref(null)
const editingRole = ref(null)
const editingOrgType = ref(null)
const deleteItem = ref(null)
const deleteType = ref('')

// Forms
const userForm = ref({ name: '', email: '', password: '', roles: [], is_active: true })
const roleForm = ref({ name: '', permissions: [] })
const orgTypeForm = ref({ name: '', description: '', is_active: true })

// Organization type icons
const getTypeIcon = (name) => {
    const icons = {
        'Hospital': BuildingLibraryIcon,
        'Clinic': BuildingOfficeIcon,
        'Pharmacy': BeakerIcon,
        'Specialty Center': Cog6ToothIcon,
        'Diagnostic Center': BuildingLibraryIcon,
        'Dental': HomeModernIcon
    }
    return icons[name] || BuildingOfficeIcon
}

// Computed
const groupedPermissions = computed(() => {
    const groups = {}
    permissions.value.forEach(perm => {
        const [group] = perm.name.split(' ')
        const groupName = group.charAt(0).toUpperCase() + group.slice(1)
        if (!groups[groupName]) groups[groupName] = { name: groupName, permissions: [] }
        groups[groupName].permissions.push(perm)
    })
    return Object.values(groups)
})

// Helper functions
const getUserInitials = (name) => name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
const formatPermissionName = (name) => name.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')

// Toggle accordion
const toggleAccordion = (accordion) => {
    openAccordion.value = openAccordion.value === accordion ? null : accordion
}

// Fetch data with CSRF token
const fetchData = async () => {
    try {
        const headers = {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json'
        }
        
        const [usersRes, rolesRes, orgTypesRes] = await Promise.all([
            fetch(`${API_BASE}/users`, { headers }),
            fetch(`${API_BASE}/roles`, { headers }),
            fetch(`${API_BASE}/organization-types`, { headers })
        ])
        
        if (usersRes.ok) users.value = await usersRes.json()
        if (rolesRes.ok) {
            const data = await rolesRes.json()
            roles.value = data.roles
            permissions.value = data.permissions
        }
        if (orgTypesRes.ok) orgTypes.value = await orgTypesRes.json()
    } catch (error) {
        console.error('Error fetching data:', error)
    }
}

// User functions
const openUserModal = () => { 
    editingUser.value = null; 
    userForm.value = { name: '', email: '', password: '', roles: [], is_active: true }; 
    showUserModal.value = true 
}

const editUser = (user) => { 
    editingUser.value = user; 
    userForm.value = { ...user, password: '' }; 
    showUserModal.value = true 
}

const closeUserModal = () => { 
    showUserModal.value = false; 
    editingUser.value = null 
}

const saveUser = async () => {
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
            body: JSON.stringify(userForm.value)
        })
        
        const data = await response.json()
        
        if (response.ok) { 
            closeUserModal(); 
            await fetchData();
            alert(data.message || 'User saved successfully');
        } else {
            alert(data.error || 'Error saving user');
        }
    } catch (error) { 
        console.error('Error saving user:', error);
        alert('Network error - please try again');
    }
}

// Role functions
const openRoleModal = () => { 
    editingRole.value = null; 
    roleForm.value = { name: '', permissions: [] }; 
    showRoleModal.value = true 
}

const editRole = (role) => { 
    editingRole.value = role; 
    roleForm.value = { name: role.name, permissions: role.permissions?.map(p => p.name) || [] }; 
    showRoleModal.value = true 
}

const closeRoleModal = () => { 
    showRoleModal.value = false; 
    editingRole.value = null 
}

const saveRole = async () => {
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
            body: JSON.stringify(roleForm.value)
        })
        
        const data = await response.json()
        
        if (response.ok) { 
            closeRoleModal(); 
            await fetchData();
            alert(data.message || 'Role saved successfully');
        } else {
            alert(data.error || 'Error saving role');
        }
    } catch (error) { 
        console.error('Error saving role:', error);
        alert('Network error - please try again');
    }
}

// Organization Type functions
const openOrgTypeModal = () => { 
    editingOrgType.value = null; 
    orgTypeForm.value = { name: '', description: '', is_active: true }; 
    showOrgTypeModal.value = true 
}

const editOrgType = (type) => { 
    editingOrgType.value = type; 
    orgTypeForm.value = { ...type }; 
    showOrgTypeModal.value = true 
}

const closeOrgTypeModal = () => { 
    showOrgTypeModal.value = false; 
    editingOrgType.value = null 
}

const saveOrgType = async () => {
    try {
        const url = editingOrgType.value ? `${API_BASE}/organization-types/${editingOrgType.value.id}` : `${API_BASE}/organization-types`
        const method = editingOrgType.value ? 'PUT' : 'POST'
        
        const response = await fetch(url, {
            method,
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(orgTypeForm.value)
        })
        
        const data = await response.json()
        
        if (response.ok) { 
            closeOrgTypeModal(); 
            await fetchData();
            alert(data.message || 'Organization type saved successfully');
        } else {
            alert(data.error || 'Error saving organization type');
        }
    } catch (error) { 
        console.error('Error saving org type:', error);
        alert('Network error - please try again');
    }
}

// Delete functions
const confirmDeleteUser = (user) => { 
    deleteItem.value = user; 
    deleteType.value = 'user'; 
    showDeleteModal.value = true 
}

const confirmDeleteRole = (role) => { 
    deleteItem.value = role; 
    deleteType.value = 'role'; 
    showDeleteModal.value = true 
}

const confirmDeleteOrgType = (type) => { 
    deleteItem.value = type; 
    deleteType.value = 'orgType'; 
    showDeleteModal.value = true 
}

const executeDelete = async () => {
    try {
        let url = ''
        if (deleteType.value === 'user') url = `${API_BASE}/users/${deleteItem.value.id}`
        else if (deleteType.value === 'role') url = `${API_BASE}/roles/${deleteItem.value.id}`
        else if (deleteType.value === 'orgType') url = `${API_BASE}/organization-types/${deleteItem.value.id}`
        
        const response = await fetch(url, { 
            method: 'DELETE',
            headers: { 
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        
        if (response.ok) {
            showDeleteModal.value = false
            deleteItem.value = null
            await fetchData()
            alert('Item deleted successfully');
        } else {
            const data = await response.json()
            alert(data.error || 'Error deleting item');
        }
    } catch (error) { 
        console.error('Error deleting:', error);
        alert('Network error - please try again');
    }
}

onMounted(fetchData)
</script>