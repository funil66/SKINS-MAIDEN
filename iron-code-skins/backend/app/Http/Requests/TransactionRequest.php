<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Allow all users to make transaction requests
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|string|in:buy,sell',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'The user ID is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'transaction_type.required' => 'The transaction type is required.',
            'transaction_type.in' => 'The transaction type must be either buy or sell.',
            'description.max' => 'The description may not be greater than 255 characters.',
        ];
    }
}