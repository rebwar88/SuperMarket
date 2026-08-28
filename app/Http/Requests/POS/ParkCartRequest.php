<?php

declare(strict_types=1);

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class ParkCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'register_id' => ['required', 'string'],
            'cart_data' => ['required', 'array'],
        ];
    }
}
