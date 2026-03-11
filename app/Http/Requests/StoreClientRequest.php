<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    

    public function authorize()
{
    return true; // later we add role check
}

public function rules()
{
    return [
        'organization_name' => 'required|string|max:255',
        'organization_type_id' => 'nullable|exists:organization_types,id',

        'primary_contact_name' => 'required|string|max:255',
        'primary_contact_email' => 'required|email|max:255',
        'primary_contact_phone' => 'required|string|max:50',

        'address_country' => 'nullable|string|max:255',
        'address_city' => 'nullable|string|max:255',
        'address_line' => 'nullable|string',

        'tax_reg_id' => 'nullable|string|max:255',
        'preferred_contact_channel' => 'required|in:email,sms,both',

        'note' => 'nullable|string'
    ];
}

}
