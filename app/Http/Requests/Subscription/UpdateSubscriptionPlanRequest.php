<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('edit subscription plans');
    }

    public function rules(): array
    {
        return [
            'plan_name' => 'sometimes|string|max:255',
            'billing_cycle' => 'sometimes|in:monthly,quarterly,yearly,custom',
            'price' => 'sometimes|numeric|min:0',
            'modules_included' => 'nullable|json',
            'max_users' => 'nullable|integer|min:1',
            'max_branches' => 'nullable|integer|min:1',
            'support_level' => 'sometimes|in:standard,premium',
            'notes' => 'nullable|string',
            'trial_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }
}