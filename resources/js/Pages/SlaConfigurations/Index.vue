<template>
<AuthenticatedLayout>

<div class="tickets-container">

<div class="tickets-header">
<h1 class="tickets-title">SLA Configurations</h1>

<button @click="createSla" class="btn-create">
Create SLA
</button>
</div>

<div class="table-wrapper">

<table class="tickets-table">

<thead>
<tr>
<th>ID</th>
<th>SLA Name</th>
<th>Priority</th>
<th>First Response (hrs)</th>
<th>Resolution (hrs)</th>
<th>Status</th>
<th class="text-center">Actions</th>
</tr>
</thead>

<tbody>

<tr v-for="sla in slas" :key="sla.id" class="hover-row">

<td>{{ sla.id }}</td>
<td>{{ sla.sla_name }}</td>

<td>
<span :class="['priority-badge', sla.priority]">
{{ formatPriority(sla.priority) }}
</span>
</td>

<td>{{ sla.first_response_hrs }}</td>
<td>{{ sla.resolution_hrs }}</td>

<td>
<span :class="['status-badge', sla.is_active ? 'active' : 'inactive']">
{{ sla.is_active ? 'Active' : 'Inactive' }}
</span>
</td>

<td class="actions-cell">

<button @click="editSla(sla.id)" class="btn-icon-action" title="Edit">
<svg class="action-icon" viewBox="0 0 20 20" fill="currentColor">
<path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
</svg>
</button>

<button @click="deleteSla(sla.id)" class="btn-icon-action btn-delete-icon" title="Delete">
<svg class="action-icon" viewBox="0 0 20 20" fill="currentColor">
<path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z"/>
</svg>
</button>

</td>

</tr>

<tr v-if="!slas || slas.length === 0">
<td colspan="7" class="text-center py-6 text-gray-500">
No SLA configurations found
</td>
</tr>

</tbody>

</table>

</div>

</div>

</AuthenticatedLayout>
</template>

<script>
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default{

components:{AuthenticatedLayout},

props:{
slas:Array
},

methods:{

createSla(){
router.visit('/sla-configurations/create')
},

editSla(id){
router.visit(`/sla-configurations/${id}/edit`)
},

deleteSla(id){

if(confirm("Delete this SLA configuration?")){
router.delete(`/sla-configurations/${id}`)
}

},

formatPriority(p){
return p ? p.charAt(0).toUpperCase()+p.slice(1) : ''
}

}

}
</script>

<style scoped>

.tickets-container{
padding:24px;
min-height:100vh;
background:#f0fff0;
border-radius:12px;
}

.tickets-header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:24px;
}

.tickets-title{
font-size:28px;
font-weight:600;
color:#2c5e2c;
}

.btn-create{
display:flex;
align-items:center;
gap:4px;
background:#4fd14f;
color:white;
border:none;
padding:6px 12px;
border-radius:6px;
cursor:pointer;
font-size:13px;
}

.btn-create:hover{
background:#1f4520;
}

.table-wrapper{
overflow-x:auto;
background:white;
border-radius:12px;
box-shadow:0 2px 8px rgba(44,94,44,0.1);
}

.tickets-table{
width:100%;
border-collapse:collapse;
}

.tickets-table th,
.tickets-table td{
padding:12px 16px;
border-bottom:1px solid #e0e8e0;
font-size:14px;
}

.tickets-table th{
background:#f3f4f6;
color:#2c5e2c;
font-weight:600;
text-align:left;
}

.hover-row:hover{
background:#f8fff8;
transition:0.2s;
}

.actions-cell{
white-space:nowrap;
display:flex;
justify-content:center;
gap:4px;
}

.btn-icon-action{
background:none;
border:none;
cursor:pointer;
padding:4px;
border-radius:6px;
transition:0.2s;
}

.btn-icon-action:hover{
background:#d1f0d1;
}

.btn-delete-icon:hover{
background:#f8d6d6;
}

.action-icon{
width:18px;
height:18px;
}

.priority-badge{
padding:3px 7px;
border-radius:6px;
font-size:12px;
font-weight:500;
}

.priority-badge.low{
background:#d1f0d1;
color:#1f4520;
}

.priority-badge.medium{
background:#a0e0a0;
color:#144d14;
}

.priority-badge.high{
background:#58c958;
color:white;
}

.priority-badge.critical{
background:#ff6961;
color:white;
}

.status-badge{
padding:3px 7px;
border-radius:6px;
font-size:12px;
}

.status-badge.active{
background:#d1f0d1;
color:#1f4520;
}

.status-badge.inactive{
background:#f8d7da;
color:#721c24;
}

.text-center{
text-align:center;
}

</style>