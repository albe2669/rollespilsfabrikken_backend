<?php

namespace App\Http\Requests\API\Event;

use App\Http\Controllers\Helpers\Constants\EventConstants;
use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('update', $this->event);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $event = $this->route('event');
        $eventMetaData = $event->meta()->first();

        $rules = [
            'title' => 'required|string',
            'description' => 'string',
            'start' => 'required|date_format:Y-m-d\TH:i:s.v\Z',
            'end' => 'required|date_format:Y-m-d\TH:i:s.v\Z',
            'recurring' => 'required|boolean',
            'recurrence' => 'required_if:recurring,true',
            'recurrence.end' => 'date_format:Y-m-d\TH:i:s.v\Z',
            'recurrence.type' => 'required_if:recurring,true|in:'.implode(',', array_keys(EventConstants::$recurrenceIntervalLookup)),
            'resources' => 'array',
            'resources.*' => 'string|required',
        ];

        if ($eventMetaData['repeat_interval'] != 0) {
            $rules['recurrence.series'] = 'required|boolean'; // Updates the entire series
            $rules['recurrence.apply_to_all'] = 'required_if:series,false|boolean'; // Ends the series on the supplied date, and creates a new one
            // If false it just does the same as delete
        }

        return $rules;
    }
}
