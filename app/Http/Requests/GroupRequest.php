<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;


class GroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', Rule::unique('groups')->ignore($this->id)],
            'image' => 'image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'required',
            'number_of_members' => 'required',
            'location' => 'required',
            'status' => 'required',
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => __('Name is required'),
            'name.unique' => __('Name invalid'),
            'image.image' => __('Incorrect image format'),
            'image.mimes' => __('Incorrect image format'),
            'image.max' => __('Image size is maximum'),
            'description.required' => __('Description is required'),
            'location.required' => __('Description is required'),
            'number_of_members.required' => __('Members is required'),
            'status.required' => __('Status is required'),
           
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors()
        ]));
    }
}
