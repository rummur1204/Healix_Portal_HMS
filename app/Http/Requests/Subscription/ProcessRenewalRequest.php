<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class ProcessRenewalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('process renewals');
    }

    public function rules(): array
    {
        return [
            'payment_method' => 'nullable|in:cash,bank,online,other',
            'notes' => 'nullable|string|max:255',
        ];
    }
}