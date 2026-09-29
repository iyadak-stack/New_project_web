<?php

namespace App\Domains\Scheduling\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tutor_id' => ['required', 'string', 'size:10'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
        ];
    }
}
