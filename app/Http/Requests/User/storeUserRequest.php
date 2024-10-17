<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class storeUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Change this to true if authorization is needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:12', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[@$!%*?&]/'], //password must be 12 charachter contains at least one uppercase letter, at least one lowercase letter , one number at least , one speical character at least
            // we need this form of password to void bruceForce attack 
            'role' => ['required', 'string', 'exists:roles,name'], // Validate role name
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب ',
            'string' => 'حقل :attribute يجب أن يكون نصا وليس اي نوع اخر',
            'max' => 'عدد محارف :attribute لا يجب ان تتجاوز 255 محرفا',
            'email' => 'حقل :attribute يجب ان يكون بصيغة صحيحة مثل test@example.com',
            'email.unique' => 'هذا :attribute موجود بالفعل في بياناتنا',
            'password.min' => 'حقل :attribute يجب ان يكون 12 محارف على الاقل',
            'password.regex' => [
                'regex:/[A-Z]/' => 'حقل :attribute يجب أن يحتوي على حرف كبير واحد على الأقل',
                'regex:/[a-z]/' => 'حقل :attribute يجب أن يحتوي على حرف صغير واحد على الأقل',
                'regex:/[0-9]/' => 'حقل :attribute يجب أن يحتوي على رقم واحد على الأقل',
                'regex:/[@$!%*?&]/' => 'حقل :attribute يجب أن يحتوي على رمز خاص واحد على الأقل مثل @$!%*?&',
            ],
            'role.exists' => 'حقل :attribute غير موجود في قاعدة البيانات',
        ];
    }

    /**
     * Get custom attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'الأسم',
            'email' => 'البريد الالكتروني',
            'password' => 'كلمة المرور',
            'role' => 'الصلاحية'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Prepare your data before validation here
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'خطأ',
            'message' => 'فشلت عملية التحقق من صحة البيانات.',
            'errors' => $validator->errors(),
        ], 422));
    }
}