<?php

namespace App\Http\Requests\API\Comment;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Comment;

class Store extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('create',[Comment::class, $this->post, $this->forum]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'parent_id' => 'integer',
            'body' => 'string|required'
        ];
    }
}
