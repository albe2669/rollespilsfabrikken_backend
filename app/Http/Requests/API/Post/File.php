<?php

namespace App\Http\Requests\API\Post;

use Illuminate\Foundation\Http\FormRequest;

class File extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('addFile', [$this->post]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'files' => 'array',
            'files.*' => 'file|required',
            'file_changes' => 'array',
            'file_changes.*.id' => 'string|required',
            'file_changes.*.change' => 'string|in:delete|required',
        ];
    }
}
