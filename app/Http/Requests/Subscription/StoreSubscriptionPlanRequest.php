<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('create subscription plans');
    }

    public function rules(): array
    {
        return [
            'plan_name' => 'required|string|max:255',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly,custom',
            'price' => 'required|numeric|min:0',
            'modules_included' => 'nullable|json',
            'max_users' => 'nullable|integer|min:1',
            'max_branches' => 'nullable|integer|min:1',
            'support_level' => 'required|in:standard,premium',
            'notes' => 'nullable|string',
            'trial_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'plan_name.required' => 'Plan name is required',
            'billing_cycle.required' => 'Billing cycle is required',
            'price.required' => 'Price is required',
            'price.min' => 'Price must be greater than 0',
        ];
    }
}