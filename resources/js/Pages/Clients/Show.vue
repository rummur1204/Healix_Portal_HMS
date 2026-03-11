<template>
<AuthenticatedLayout>

<div class="flex gap-6">

<!-- ================= MAIN CONTENT ================= -->
<div class="flex-1 bg-white dark:bg-primary-900 p-6 rounded-xl shadow">

<!-- Header -->
<div class="mb-6">
<h2 class="text-2xl font-bold">
{{ client.organization_name }}
</h2>
<p class="text-sm text-gray-500">
Code: {{ client.client_code }}
</p>
</div>

<!-- Tabs -->
<div class="border-b mb-6 flex gap-6">
<button
v-for="tab in tabs"
:key="tab"
@click="activeTab = tab"
class="pb-2"
:class="activeTab === tab ? 'border-b-2 border-teal-600 font-semibold' : ''"
>
{{ tab }}
</button>
</div>

<!-- ================= PROFILE TAB ================= -->
<div v-if="activeTab === 'Profile'">
<!-- (UNCHANGED PROFILE CONTENT) -->
<div class="grid grid-cols-2 gap-6">
<div>
<h4 class="font-semibold mb-2">Basic Info</h4>
<p><strong>Type:</strong> {{ client.organization_type?.name }}</p>
<p><strong>Status:</strong> {{ client.status }}</p>
<p><strong>Tax ID:</strong> {{ client.tax_reg_id }}</p>
<p><strong>Preferred Channel:</strong> {{ client.preferred_contact_channel }}</p>
</div>

<div>
<h4 class="font-semibold mb-2">Address</h4>
<p>{{ client.address_line }}</p>
<p>{{ client.address_city }}</p>
<p>{{ client.address_country }}</p>
</div>
</div>

<div class="mt-8">
<h4 class="font-semibold mb-3">Contacts</h4>
<div
v-for="contact in client.contacts"
:key="contact.id"
class="border p-4 rounded mb-3"
>
<p class="font-medium">
{{ contact.name }}
<span v-if="contact.is_primary" class="text-xs text-teal-600">
(Primary)
</span>
</p>
<p v-if="contact.title">{{ contact.title }}</p>
<p>{{ contact.email }}</p>
<p>{{ contact.phone }}</p>
</div>
</div>
</div>

<!-- ================= NOTES TAB ================= -->
<div v-if="activeTab === 'Notes'">

<!-- ADD / EDIT FORM -->
<form @submit.prevent="saveNote" class="mb-6 border p-4 rounded">

<div class="grid grid-cols-2 gap-4">

<div>
<label class="block text-sm font-medium">Title</label>
<input v-model="noteForm.title" class="input"/>
</div>

<div>
<label class="block text-sm font-medium">Priority</label>
<select v-model="noteForm.priority" class="input">
<option value="low">Low</option>
<option value="medium">Medium</option>
<option value="high">High</option>
<option value="critical">Critical</option>
</select>
</div>

<div class="col-span-2">
<label class="block text-sm font-medium">Content</label>
<textarea v-model="noteForm.content" class="input"></textarea>
</div>

</div>

<div class="flex justify-end gap-3 mt-4">
<button class="bg-teal-600 text-white px-4 py-2 rounded">
{{ editingNote ? 'Update Note' : 'Add Note' }}
</button>

<button
v-if="editingNote"
type="button"
@click="cancelEditNote"
class="border px-4 py-2 rounded"
>
Cancel
</button>
</div>

</form>

<!-- NOTES LIST -->
<div v-if="client.notes.length">

<div
v-for="note in client.notes"
:key="note.id"
class="border rounded p-4 mb-4"
>
<div class="flex justify-between items-start mb-2">

<div>
<p class="font-semibold">
{{ note.title }}
<span
:class="priorityBadge(note.priority)"
class="ml-2 text-xs px-2 py-1 rounded text-white"
>
{{ note.priority }}
</span>
</p>

<p class="text-xs text-gray-500">
{{ note.author?.name }} • {{ formatDate(note.created_at) }}
</p>
</div>

<div class="flex gap-3">
<button
@click="editNote(note)"
class="text-blue-600 text-sm"
>
Edit
</button>

<button
@click="deleteNote(note.id)"
class="text-red-500 text-sm"
>
Delete
</button>
</div>

</div>

<p class="text-gray-800 whitespace-pre-line">
{{ note.content }}
</p>

</div>

</div>

<div v-else class="text-gray-500">
No notes yet.
</div>

</div>



<!-- ================= TECH INFO TAB (UNCHANGED) ================= -->
<div v-if="activeTab === 'Tech Info'">

<div class="flex justify-end mb-4">
<button
v-if="!editingTech"
@click="startEdit"
class="bg-blue-600 text-white px-4 py-2 rounded"
>
{{ client.technical_info ? 'Edit Technical Info' : 'Add Technical Info' }}
</button>
</div>

<!-- ===== VIEW MODE ===== -->
<div v-if="!editingTech && client.technical_info" class="space-y-8">

<!-- Remote -->
<div>
<h4 class="font-semibold mb-2">Remote Access</h4>
<p><strong>Enabled:</strong> {{ yesNo(client.technical_info.remote_access_enabled) }}</p>
<p><strong>AnyDesk Address:</strong> {{ client.technical_info.anydesk_address }}</p>
<p><strong>Password:</strong> ••••••••</p>
</div>

<!-- Server -->
<div>
<h4 class="font-semibold mb-2">Server</h4>
<p><strong>Setup Date:</strong> {{ client.technical_info.server_setup_date }}</p>
<p><strong>Activation Date:</strong> {{ client.technical_info.server_activation_date }}</p>
<p><strong>Expiry Date:</strong> {{ client.technical_info.activation_expiry_date }}</p>
<p><strong>Windows Rearm:</strong> {{ client.technical_info.windows_rearm_count }}</p>
</div>

<!-- Network -->
<div>
<h4 class="font-semibold mb-2">Network</h4>
<p><strong>IP:</strong> {{ client.technical_info.ip_address }}</p>
<p><strong>Subnet:</strong> {{ client.technical_info.subnet_mask }}</p>
<p><strong>Gateway:</strong> {{ client.technical_info.gateway }}</p>
<p><strong>DNS 1:</strong> {{ client.technical_info.primary_dns }}</p>
<p><strong>DNS 2:</strong> {{ client.technical_info.secondary_dns }}</p>
<p><strong>Internet:</strong> {{ yesNo(client.technical_info.internet_available) }}</p>
</div>

<!-- Hardware -->
<div>
<h4 class="font-semibold mb-2">Hardware</h4>
<p><strong>Firewall:</strong> {{ yesNo(client.technical_info.firewall_in_use) }}</p>
<p><strong>Firewall Brand:</strong> {{ client.technical_info.firewall_brand }}</p>
<p><strong>Router:</strong> {{ client.technical_info.router_model }}</p>
<p><strong>Router Password:</strong> ••••••••</p>
</div>

<!-- Backup -->
<div>
<h4 class="font-semibold mb-2">Backup</h4>
<p><strong>Configured:</strong> {{ yesNo(client.technical_info.backup_configured) }}</p>
<p><strong>Type:</strong> {{ client.technical_info.backup_type }}</p>
<p><strong>Frequency:</strong> {{ client.technical_info.backup_frequency }}</p>
<p><strong>Free Space:</strong> {{ client.technical_info.available_free_space_gb }} GB</p>
<p><strong>Disk Config:</strong> {{ client.technical_info.disk_configuration }}</p>
</div>

</div>

<div v-if="!editingTech && !client.technical_info" class="text-gray-500">
No technical information added yet.
</div>

<!-- ===== EDIT MODE ===== -->
<div v-if="editingTech">

<form @submit.prevent="saveTechInfo" class="space-y-8">

<!-- Remote Section -->
<div>
<h4 class="font-semibold mb-3">Remote Access</h4>
<div class="grid grid-cols-2 gap-4">
<label class="flex items-center gap-2 col-span-2">
<input type="checkbox" v-model="techForm.remote_access_enabled" />
<span>Remote Access Enabled</span>
</label>

<div>
<label class="block text-sm font-medium">AnyDesk Address</label>
<input v-model="techForm.anydesk_address" class="input" />
</div>

<div>
<label class="block text-sm font-medium">New AnyDesk Password</label>
<input type="password" v-model="techForm.anydesk_password" class="input" />
</div>
</div>
</div>

<!-- Server Section -->
<div>
<h4 class="font-semibold mb-3">Server</h4>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-sm font-medium">Setup Date</label>
<input type="date" v-model="techForm.server_setup_date" class="input" />
</div>

<div>
<label class="block text-sm font-medium">Activation Date</label>
<input type="date" v-model="techForm.server_activation_date" class="input" />
</div>

<div>
<label class="block text-sm font-medium">Expiry Date</label>
<input type="date" v-model="techForm.activation_expiry_date" class="input" />
</div>

<div>
<label class="block text-sm font-medium">Windows Rearm Count</label>
<input type="number" v-model="techForm.windows_rearm_count" class="input" />
</div>
</div>
</div>

<!-- Network Section -->
<div>
<h4 class="font-semibold mb-3">Network</h4>
<div class="grid grid-cols-2 gap-4">
<div><label class="block text-sm font-medium">IP Address</label><input v-model="techForm.ip_address" class="input"/></div>
<div><label class="block text-sm font-medium">Subnet Mask</label><input v-model="techForm.subnet_mask" class="input"/></div>
<div><label class="block text-sm font-medium">Gateway</label><input v-model="techForm.gateway" class="input"/></div>
<div><label class="block text-sm font-medium">Primary DNS</label><input v-model="techForm.primary_dns" class="input"/></div>
<div><label class="block text-sm font-medium">Secondary DNS</label><input v-model="techForm.secondary_dns" class="input"/></div>

<label class="flex items-center gap-2 col-span-2">
<input type="checkbox" v-model="techForm.internet_available" />
<span>Internet Available</span>
</label>
</div>
</div>

<!-- Hardware Section -->
<div>
<h4 class="font-semibold mb-3">Hardware</h4>
<div class="grid grid-cols-2 gap-4">
<label class="flex items-center gap-2 col-span-2">
<input type="checkbox" v-model="techForm.firewall_in_use" />
<span>Firewall In Use</span>
</label>

<div><label class="block text-sm font-medium">Firewall Brand</label><input v-model="techForm.firewall_brand" class="input"/></div>
<div><label class="block text-sm font-medium">Router Model</label><input v-model="techForm.router_model" class="input"/></div>

<div class="col-span-2">
<label class="block text-sm font-medium">New Router Admin Password</label>
<input type="password" v-model="techForm.router_admin_password" class="input"/>
</div>
</div>
</div>

<!-- Backup Section -->
<div>
<h4 class="font-semibold mb-3">Backup</h4>
<div class="grid grid-cols-2 gap-4">
<label class="flex items-center gap-2 col-span-2">
<input type="checkbox" v-model="techForm.backup_configured" />
<span>Backup Configured</span>
</label>

<div>
<label class="block text-sm font-medium">Backup Type</label>
<select v-model="techForm.backup_type" class="input">
<option value="">Select</option>
<option value="local">Local</option>
<option value="cloud">Cloud</option>
<option value="hybrid">Hybrid</option>
</select>
</div>

<div>
<label class="block text-sm font-medium">Backup Frequency</label>
<select v-model="techForm.backup_frequency" class="input">
<option value="">Select</option>
<option value="daily">Daily</option>
<option value="weekly">Weekly</option>
<option value="monthly">Monthly</option>
<option value="manual">Manual</option>
<option value="automated">Automated</option>
</select>
</div>

<div>
<label class="block text-sm font-medium">Available Free Space (GB)</label>
<input type="number" step="0.01" v-model="techForm.available_free_space_gb" class="input"/>
</div>

<div class="col-span-2">
<label class="block text-sm font-medium">Disk Configuration</label>
<textarea v-model="techForm.disk_configuration" class="input"></textarea>
</div>
</div>
</div>

<div class="flex gap-3">
<button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded">
Save
</button>
<button type="button" @click="cancelEdit" class="border px-4 py-2 rounded">
Cancel
</button>
</div>

</form>
</div>

</div>

<!-- ================= DOCS TAB ================= -->
<div v-if="activeTab === 'Docs'">

<div class="flex justify-between mb-6">

<h4 class="font-semibold text-lg">
Client Documents
</h4>

<button
@click="showUpload = !showUpload"
class="bg-teal-600 text-white px-4 py-2 rounded"
>
Upload Document
</button>

</div>

<!-- Upload Form -->

<div v-if="showUpload" class="border p-4 rounded mb-6">

<form @submit.prevent="uploadDoc" class="space-y-4">

<div>
<label class="block text-sm font-medium">File Name</label>
<input v-model="docForm.file_name" class="input"/>
</div>

<div>
<label class="block text-sm font-medium">Description</label>
<textarea v-model="docForm.description" class="input"/>
</div>

<div>
<label class="block text-sm font-medium">File</label>
<input type="file" @change="handleFile"/>
</div>

<button
type="submit"
class="bg-blue-600 text-white px-4 py-2 rounded"
>
Upload
</button>

</form>

</div>

<!-- Documents List -->

<!-- <div v-if="client.documents.length === 0" class="text-gray-500">
No documents uploaded.
</div> -->
<div v-if="!client.documents || client.documents.length === 0" class="text-gray-500">
No documents uploaded.
</div>

<div
v-for="doc in client.documents"
:key="doc.id"
class="border p-4 rounded mb-3 flex justify-between items-center"
>


<div>

<p class="font-medium">
{{ doc.file_name }}
</p>

<p class="text-xs text-gray-500">
Uploaded by {{ doc.uploaded_by?.name }} • {{ formatDate(doc.created_at) }}
</p>

</div>

<div class="flex gap-4">

<a
:href="`/storage/${doc.file_path}`"
target="_blank"
class="text-blue-600"
>
Download
</a>

<button
@click="deleteDoc(doc.id)"
class="text-red-600"
>
Delete
</button>

</div>

</div>

</div>

<!-- ================= TASKS TAB ================= -->
<div v-if="activeTab === 'Tasks'">

<div class="flex justify-between mb-6">
<h4 class="font-semibold text-lg">Client Tasks</h4>
</div>

<!-- Task Form -->
<form @submit.prevent="saveTask" class="border p-4 rounded mb-6">

<div class="grid grid-cols-2 gap-4">

<div>
<label class="block text-sm font-medium">Title</label>
<input v-model="taskForm.title" class="input"/>
</div>

<div>
<label class="block text-sm font-medium">Due Date</label>
<input type="date" v-model="taskForm.due_date" class="input"/>
</div>

<div>
<label class="block text-sm font-medium">Assign To</label>
<select v-model="taskForm.assigned_to_user_id" class="input">
<option value="">Unassigned</option>
<option v-for="user in users" :key="user.id" :value="user.id">
{{ user.name }}
</option>
</select>
</div>

<div>
<label class="block text-sm font-medium">Reminder</label>
<input type="datetime-local" v-model="taskForm.reminder_at" class="input"/>
</div>

<div class="col-span-2">
<label class="block text-sm font-medium">Description</label>
<textarea v-model="taskForm.description" class="input"></textarea>
</div>

</div>

<button class="bg-teal-600 text-white px-4 py-2 rounded mt-4">
Create Task
</button>

</form>


<!-- Tasks List -->

<div v-if="client.tasks.length === 0" class="text-gray-500">
No tasks yet.
</div>

<div
v-for="task in client.tasks"
:key="task.id"
class="border p-4 rounded mb-3 flex justify-between items-center"
>

<div>

<p class="font-medium">
{{ task.title }}
</p>

<p class="text-xs text-gray-500">
Assigned to {{ task.assigned_to?.name || 'Unassigned' }}
</p>

<p class="text-xs text-gray-500">
Due {{ formatDate(task.due_date) }}
</p>

</div>

<div class="flex gap-4">

<span
v-if="task.status === 'open'"
class="text-yellow-600 text-sm font-medium"
>
Open
</span>

<span
v-if="task.status === 'done'"
class="text-green-600 text-sm font-medium"
>
Done
</span>

<button
v-if="task.status === 'open'"
@click="markDone(task.id)"
class="text-green-600 text-sm"
>
Mark Done
</button>

<button
@click="deleteTask(task.id)"
class="text-red-600 text-sm"
>
Delete
</button>

</div>

</div>

</div>
</div>




<!-- ================= SIDEBAR ================= -->
<div class="w-[350px] bg-white dark:bg-primary-900 p-6 rounded-xl shadow">

<h4 class="font-semibold mb-4">Timeline</h4>

<div
v-for="event in client.timeline_events"
:key="event.id"
class="border-l-2 border-teal-600 pl-4 mb-4"
>
<p class="text-sm font-medium capitalize">
{{ event.event_type.replace('_', ' ') }}
</p>
<p class="text-xs text-gray-500">
{{ event.description }}
</p>
<p class="text-xs text-gray-400">
{{ formatDate(event.created_at) }}
</p>
</div>

</div>

</div>

</AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ client: Object, users:Array })

const tabs = ['Profile', 'Tech Info', 'Tasks', 'Notes', 'Docs']
const activeTab = ref('Profile')
const editingTech = ref(false)

/* ================= NOTES ================= */

const editingNote = ref(false)
const editingNoteId = ref(null)

const noteForm = useForm({
title: '',
content: '',
priority: 'medium',
note_type: 'internal'
})

const saveNote = () => {
if (editingNote.value) {
noteForm.put(route('clients.notes.update', [props.client.id, editingNoteId.value]), {
preserveScroll: true,
onSuccess: () => resetNoteForm()
})
} else {
noteForm.post(route('clients.notes.store', props.client.id), {
preserveScroll: true,
onSuccess: () => resetNoteForm()
})
}
}

const editNote = (note) => {
editingNote.value = true
editingNoteId.value = note.id
noteForm.title = note.title
noteForm.content = note.content
noteForm.priority = note.priority
}

const cancelEditNote = () => {
resetNoteForm()
}

const resetNoteForm = () => {
editingNote.value = false
editingNoteId.value = null
noteForm.reset()
noteForm.priority = 'medium'
noteForm.note_type = 'internal'
}

const deleteNote = (noteId) => {
if (!confirm('Delete this note?')) return

noteForm.delete(route('clients.notes.destroy', [props.client.id, noteId]), {
preserveScroll: true
})
}

const priorityBadge = (priority) => {
switch(priority) {
case 'low': return 'bg-gray-400'
case 'medium': return 'bg-blue-500'
case 'high': return 'bg-orange-500'
case 'critical': return 'bg-red-600'
default: return 'bg-gray-400'
}
}

/* ================= COMMON ================= */

const techForm = useForm({
remote_access_enabled: props.client.technical_info?.remote_access_enabled || false,
anydesk_address: props.client.technical_info?.anydesk_address || '',
anydesk_password: '',
server_setup_date: props.client.technical_info?.server_setup_date || '',
server_activation_date: props.client.technical_info?.server_activation_date || '',
activation_expiry_date: props.client.technical_info?.activation_expiry_date || '',
windows_rearm_count: props.client.technical_info?.windows_rearm_count || 0,
ip_address: props.client.technical_info?.ip_address || '',
subnet_mask: props.client.technical_info?.subnet_mask || '',
gateway: props.client.technical_info?.gateway || '',
primary_dns: props.client.technical_info?.primary_dns || '',
secondary_dns: props.client.technical_info?.secondary_dns || '',
internet_available: props.client.technical_info?.internet_available || false,
firewall_in_use: props.client.technical_info?.firewall_in_use || false,
firewall_brand: props.client.technical_info?.firewall_brand || '',
router_model: props.client.technical_info?.router_model || '',
router_admin_password: '',
backup_configured: props.client.technical_info?.backup_configured || false,
backup_type: props.client.technical_info?.backup_type || '',
backup_frequency: props.client.technical_info?.backup_frequency || '',
available_free_space_gb: props.client.technical_info?.available_free_space_gb || '',
disk_configuration: props.client.technical_info?.disk_configuration || '',
})

const startEdit = () => editingTech.value = true
const cancelEdit = () => editingTech.value = false

const saveTechInfo = () => {
techForm.post(route('clients.technical-info.save', props.client.id), {
onSuccess: () => editingTech.value = false
})
}

const yesNo = (val) => val ? 'Yes' : 'No'



const showUpload = ref(false)

const docForm = useForm({
file_name: '',
description: '',
file: null
})

const handleFile = (e) => {
docForm.file = e.target.files[0]
}

const uploadDoc = () => {
docForm.post(route('clients.docs.store', props.client.id), {
forceFormData: true,
onSuccess: () => {
showUpload.value = false
docForm.reset()
}
})
}

const deleteDoc = (id) => {

if(confirm('Delete document?')){

router.delete(route('clients.docs.destroy', id))

}

}

const taskForm = useForm({
title: '',
due_date: '',
description: '',
assigned_to_user_id: '',
reminder_at: ''
})

const saveTask = () => {
taskForm.post(route('clients.tasks.store', props.client.id), {
onSuccess: () => taskForm.reset()
})
}

const markDone = (id) => {
router.patch(route('clients.tasks.update', id))
}

const deleteTask = (id) => {
router.delete(route('clients.tasks.destroy', id))
}



const formatDate = (date) => new Date(date).toLocaleString()
</script>

