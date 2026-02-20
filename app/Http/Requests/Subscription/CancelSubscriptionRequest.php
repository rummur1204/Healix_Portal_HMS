<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class CancelSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('cancel subscriptions');
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|min:5',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Cancellation reason is required',
            'reason.min' => 'Please provide a more detailed reason (at least 5 characters)',
        ];
    }
}