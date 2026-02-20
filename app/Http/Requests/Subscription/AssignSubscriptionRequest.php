<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class AssignSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('assign subscriptions');
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'plan_id' => 'required|exists:subscription_plans,id',
            'start_date' => 'nullable|date',
            'payment_method' => 'nullable|in:cash,bank,online,other',
            'payment_status' => 'nullable|in:paid,unpaid,partial',
            'discount' => 'nullable|numeric|min:0',
            'invoice_reference' => 'nullable|string|max:255',
            'internal_note' => 'nullable|string',
            'is_trial' => 'boolean',
            'custom_days' => 'nullable|integer|min:1',
            'status' => 'nullable|in:trial,active,past_due,cancelled,suspended',
        ];
    }
}