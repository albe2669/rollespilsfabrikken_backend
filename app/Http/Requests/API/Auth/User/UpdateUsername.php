<?php

namespace App\Http\Requests\API\Auth\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUsername extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('changeUsername', [User::class, auth()->user()]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'string|required|confirmed',
        ];
    }
}
