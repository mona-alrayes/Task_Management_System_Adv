<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
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
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];
    }
    /**
     * Get the custom error messages for validation rules.
     *
     * This method returns an array of custom error messages for validation
     * rules. The array keys should correspond to the validation rule names,
     * and the values are the custom error messages.
     *
     * @return array<string, string> Array of custom error messages.
     */
    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب ',
            'string' => 'حقل :attribute يجب أن يكون نصا وليس اي نوع اخر',
            'email.required' => 'حقل :attribute مطلوب لا يمكن ان يكون فارغا',
            'email' => 'حقل :attribute يجب ان يكون بصيغة صحيحة مثل test@example.com',
        ];
    }

    /**
     * Get the custom attribute names for validator errors.
     *
     * This method returns an array of custom attribute names that should
     * be used in error messages. The keys are the input field names, and
     * the values are the custom names to be used in error messages.
     *
     * @return array<string, string> Array of custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'email' => 'البريد الالكتروني',
            'password' => 'كلمة المرور',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * This method is called when validation fails. It customizes the
     * response that is returned when validation fails, including the
     * status code and error messages.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'خطأ',
            'message' => 'فشلت المصادقة',
            'errors'  => $validator->errors(),
        ], 422));
    }
    
}