<?php

namespace jeremykenedy\LaravelLogger\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivityLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'description' => ['nullable', 'string'],
            'user' => ['nullable', 'integer'],
            'method' => ['nullable', 'string'],
            'route' => ['nullable', 'string'],
            'ip_address' => ['nullable', 'string'],
            'period' => ['nullable', 'string'],
            'format' => ['nullable', 'string'],
        ];
        if (config('LaravelLogger.enableDateFiltering')) {
            $rules['date_from'] = ['nullable', 'date_format:Y-m-d'];
            $rules['date_to'] = ['nullable', 'date_format:Y-m-d'];
        }

        return $rules;
    }
}
