<?php

declare(strict_types=1);

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class ProcessReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'original_order_id' => ['required', 'string'],
            'register_id' => ['required', 'string'],
            'register_shift_id' => ['required', 'string'],
            'refund_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
        ];
    }
}
