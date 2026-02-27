<template>
    <AuthenticatedLayout>
    <div>
        <h1 class="text-2xl font-bold mb-4">
            {{ client.organization_name }}
        </h1>

        <p><strong>Code:</strong> {{ client.client_code }}</p>
        <p><strong>Status:</strong> {{ client.status }}</p>
        <p><strong>Email:</strong> {{ client.primary_contact_email }}</p>
        <p><strong>Phone:</strong> {{ client.primary_contact_phone }}</p>

        <hr class="my-4" />

        <h2 class="text-xl font-bold">Contacts</h2>
        <ul>
            <li v-for="contact in client.contacts" :key="contact.id">
                {{ contact.name }} ({{ contact.email }})
            </li>
        </ul>
    </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    props: {
        client: Object
    }
}
</script>