<?php

declare(strict_types=1);

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class OpenShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'register_id' => ['required', 'string'],
            'opening_cash' => ['required', 'numeric', 'min:0'],
        ];
    }
}
