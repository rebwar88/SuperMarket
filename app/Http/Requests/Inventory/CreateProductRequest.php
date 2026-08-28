<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'unit_id' => ['required', 'uuid', 'exists:units,id'],
            'retail_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'min_stock_level' => ['nullable', 'numeric', 'min:0'],
            'is_scale_item' => ['nullable', 'boolean'],
            'barcodes' => ['nullable', 'array'],
            'barcodes.*.code' => ['required', 'string', 'unique:barcodes,code'],
            'barcodes.*.type' => ['nullable', 'string', 'in:unit,carton,pack'],
            'barcodes.*.packing_qty' => ['nullable', 'numeric', 'min:1'],
        ];
    }
}