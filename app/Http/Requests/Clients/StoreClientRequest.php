<?php

namespace App\Http\Requests\Clients;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Organization
            'organization_name' => [
                'required',
                'string',
                'max:255',
                'unique:clients,organization_name'
            ],

            'organization_type_id' => 'nullable|exists:organization_types,id',

            // Primary Contact
            'primary_contact.name' => 'required|string|max:255',
            'primary_contact.email' => 'required|email|max:255|unique:client_contacts,email',
            'primary_contact.phone' => 'required|string|max:50|unique:client_contacts,phone',
            'primary_contact.title' => 'nullable|string|max:255',

            // Additional contacts
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'required_with:contacts|string|max:255',
            'contacts.*.email' => 'nullable|email|max:255|unique:client_contacts,email',
            'contacts.*.phone' => 'nullable|string|max:50|unique:client_contacts,phone',
            'contacts.*.title' => 'nullable|string|max:255',

            // Address
            'address_country' => 'nullable|string|max:255',
            'address_city' => 'nullable|string|max:255',
            'address_line' => 'nullable|string',

            'tax_reg_id' => 'nullable|string|max:255',

            'preferred_contact_channel' => 'required|in:email,sms,both',

            'note' => 'nullable|string'
        ];
    }
}