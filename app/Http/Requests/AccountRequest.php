<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
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
            'name' => 'required',
            'nickname' => 'required|max:20'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_pathfinder' => $this->has('is_pathfinder') ?? false,
            'is_adventurer' => $this->has('is_adventurer') ?? false,
            'state' => strtoupper($this->input('state')),
            'change_data_at' => now(),
        ]);
    }
}
