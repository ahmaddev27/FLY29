<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class IngestTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled by middleware.
    }

    public function rules(): array
    {
        return [
            'agent_id'         => ['required', 'string', 'max:50'],
            'transaction_type' => ['required', 'in:package,service'],
            'amount_usd'       => ['required', 'numeric', 'gt:0'],
            'destination'      => ['nullable', 'string', 'max:100'],
            'transaction_date' => ['required', 'date'],
            'reference_id'     => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'agent_id.required'         => 'agent_id is required',
            'transaction_type.in'       => 'transaction_type must be either "package" or "service"',
            'amount_usd.gt'             => 'amount_usd must be greater than 0',
            'transaction_date.date'     => 'transaction_date must be a valid ISO 8601 datetime',
            'reference_id.required'     => 'reference_id is required (unique idempotency key)',
        ];
    }

    /**
     * Return JSON with our standard error envelope (matches API spec).
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'validation_failed',
            'errors' => $validator->errors()->toArray(),
        ], 422));
    }
}
