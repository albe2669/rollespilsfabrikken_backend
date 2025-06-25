<?php

namespace App\Http\Requests\API\Comment;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;

class Store extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('create', [Comment::class, $this->forum]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'parent_id' => 'string',
            'body' => 'string|required',
        ];
    }
}
