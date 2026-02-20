<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('record payments');
    }

    public function rules(): array
    {
        return [
            'subscription_id' => 'required|exists:client_subscriptions,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'nullable|date',
            'payment_method' => 'required|in:cash,bank,online,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:pending,completed,failed,refunded',
        ];
    }
}