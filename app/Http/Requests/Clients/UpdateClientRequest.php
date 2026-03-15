<?php

namespace App\Http\Requests\Clients;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $clientId = $this->route('client')->id;

        return [

            'organization_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('clients', 'organization_name')->ignore($clientId)
            ],

            'organization_type_id' => 'nullable|exists:organization_types,id',

            // Primary Contact
            'primary_contact.name' => 'required|string|max:255',
            'primary_contact.email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('client_contacts', 'email')->whereNot('client_id', $clientId)
            ],
            'primary_contact.phone' => [
                'required',
                'string',
                'max:50',
                Rule::unique('client_contacts', 'phone')->whereNot('client_id', $clientId)
            ],
            'primary_contact.title' => 'nullable|string|max:255',

            // Additional contacts
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'required_with:contacts|string|max:255',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:50',
            'contacts.*.title' => 'nullable|string|max:255',

            'address_country' => 'nullable|string|max:255',
            'address_city' => 'nullable|string|max:255',
            'address_line' => 'nullable|string',

            'tax_reg_id' => 'nullable|string|max:255',

            'preferred_contact_channel' => 'required|in:email,sms,both',

            'note' => 'nullable|string'
        ];
    }
}