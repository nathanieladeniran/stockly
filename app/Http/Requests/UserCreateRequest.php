<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'policy_agreement' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email is required to create new account',
            'email.unique' => 'The email already exists',
            'password.confirmed' => 'Password confirmation does not match',
            'policy_agreement' => 'Please agree to the terms and policy'
        ];
    }
}
