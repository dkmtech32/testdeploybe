<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupTrainingUserRequest extends FormRequest
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
    public function rules()
    {
        return [
            'acconpanion' => 'max:4',
          
        
        ];
    }
    
    public function messages()
    {
        return [
            'acconpanion.max' => __('Acconpanion must not exceed 4 people'),
        ];
    }
}
