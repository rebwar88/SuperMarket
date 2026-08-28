<?php

declare(strict_types=1);

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class ScanBarcodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barcode' => ['required', 'string'],
        ];
    }
}
