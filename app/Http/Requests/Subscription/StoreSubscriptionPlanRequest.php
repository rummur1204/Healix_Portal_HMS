<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('create subscription plans');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plan_name' => 'required|string|max:255|unique:subscription_plans,plan_name',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly,custom',
            'price' => 'required|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'support_level' => 'nullable|in:standard,premium',
            'max_users' => 'nullable|integer|min:0',
            'max_branches' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'plan_name.required' => 'The plan name is required.',
            'plan_name.unique' => 'A plan with this name already exists.',
            'billing_cycle.required' => 'Please select a billing cycle.',
            'price.required' => 'The price is required.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price cannot be negative.',
        ];
    }
}