<?php

namespace App\Http\Requests\API\Event;

use Illuminate\Foundation\Http\FormRequest;

class Destroy extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('delete', $this->event);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        if ($this->route('event')->meta['repeat_interval'] != 0) {
            $rules = [
                'series' => 'boolean', // Deletes the entire series
                'apply_to_all' => 'boolean', // Ends the series on the supplied date, if false it deletes only the current one
                'only_this' => 'boolean', // Ends the series on the supplied date, if false it deletes only the current one
                'date' => 'required_if:series,false|date_format:Y-m-d\TH:i:s.v\Z',
            ];
        }

        return $rules;
    }
}
