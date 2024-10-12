<?php

namespace App\Http\Requests\Task;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTaskRequest extends FormRequest
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
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['required', 'string', 'min:3', 'max:2000'],
            'type' => ['required', 'string', 'in:Bug,Feature,Improvement'],
            'priority' => ['required', 'string', 'in:Low,Medium,High'],
            'due_date' => ['required', 'date_format:d-m-Y'], 
            'assigned_to' => ['nullable', 'exists:users,id'], 
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
        'required' => 'الحقل :attribute مطلوب.',
        'string' => 'الحقل :attribute يجب أن يكون نص.',
        'min' => 'الحقل :attribute يجب أن يكون على الأقل :min حروف.',
        'title.max' => 'الحقل العنوان لا يجب أن يتجاوز :max حروف.',
        'description.max' => 'الحقل الوصف لا يجب أن يتجاوز :max حروف.',
        'type.in' => 'نوع الحقل :attribute يجب أن يكون إما خطأ، ميزة، أو تحسين.',
        'priority.in' => 'أولوية الحقل :attribute يجب أن تكون إما منخفضة، متوسطة، أو عالية.',
        'due_date.date_format' => 'الحقل :attribute يجب أن يكون بتنسيق يوم-شهر-سنة.',
        'assigned_to.exists' => 'المستخدم المحدد في الحقل :attribute غير موجود.',
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
          'title' => 'العنوان',
          'description' => 'الوصف',
          'type' => 'النوع',
          'priority' => 'الأولوية',
          'due_date' => 'التاريخ التسليم',
          'assigned_to' => 'المعين للعمل',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => ucwords($this->type),
            'priority' => ucwords($this->priority),
        ]);
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
