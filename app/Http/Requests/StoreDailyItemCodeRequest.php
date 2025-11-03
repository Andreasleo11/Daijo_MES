<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyItemCodeRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'schedule_date' => 'required|date',
            'machine_id' => 'required|exists:users,id', // machine_id uses users table
            'shifts' => 'required|array|min:1|max:3',
            'shifts.*' => 'required|in:1,2,3|distinct',
            'item_codes' => 'required|array|min:1|max:3',
            'item_codes.*' => 'required|array|min:1',
            'item_codes.*.*' => 'required|string',
            'quantities' => 'required|array|min:1|max:3',
            'quantities.*' => 'required|array|min:0',
            'quantities.*.*' => 'required|integer|min:0',
            'start_dates' => 'required|array|min:1|max:3',
            'start_dates.*' => 'required|array|min:1',
            'start_dates.*.*' => 'required|date',
            'end_dates' => 'required|array|min:1|max:3',
            'end_dates.*' => 'required|array|min:1',
            'end_dates.*.*' => 'required|date|after_or_equal:start_dates.*.*',
            'start_times' => 'required|array|min:1|max:3',
            'start_times.*' => 'required|array|min:1',
            'start_times.*.*' => 'required|date_format:H:i',
            'end_times' => 'required|array|min:1|max:3',
            'end_times.*' => 'required|array|min:1',
            'end_times.*.*' => 'required|date_format:H:i',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'schedule_date.required' => 'The schedule date is required.',
            'schedule_date.date' => 'The schedule date must be a valid date.',
            'machine_id.required' => 'Please select a machine.',
            'machine_id.exists' => 'The selected machine does not exist.',
            'shifts.required' => 'At least one shift must be selected.',
            'shifts.*.in' => 'Shift numbers must be 1, 2, or 3.',
            'shifts.*.distinct' => 'Shift numbers must be unique across shifts.',
            'item_codes.required' => 'At least one item code is required.',
            'item_codes.*.required' => 'Each shift must have at least one item code.',
            'item_codes.*.*.required' => 'Each item code must be a valid string.',
            'quantities.required' => 'Quantities are required for all shifts.',
            'quantities.*.required' => 'Each shift must have quantities.',
            'quantities.*.*.integer' => 'Quantities must be numeric.',
            'quantities.*.*.min' => 'Quantities must be at least 1.',
            'start_dates.required' => 'Start dates are required for all shifts.',
            'start_dates.*.required' => 'Each shift must have start dates.',
            'start_dates.*.*.date' => 'Start dates must be valid dates.',
            'end_dates.required' => 'End dates are required for all shifts.',
            'end_dates.*.required' => 'Each shift must have end dates.',
            'end_dates.*.*.date' => 'End dates must be valid dates.',
            'end_dates.*.*.after_or_equal' => 'End dates must be on or after start dates.',
            'start_times.required' => 'Start times are required for all shifts.',
            'start_times.*.required' => 'Each shift must have start times.',
            'start_times.*.*.date_format' => 'Start times must be in the format HH:mm.',
            'end_times.required' => 'End times are required for all shifts.',
            'end_times.*.required' => 'Each shift must have end times.',
            'end_times.*.*.date_format' => 'End times must be in the format HH:mm.',
        ];
    }
}
