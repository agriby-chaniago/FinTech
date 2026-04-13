<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'min:1'],
            'transactions' => ['required', 'array', 'min:1'],
            'transactions.*.amount' => ['required', 'numeric', 'min:0'],
            'transactions.*.category' => ['required', 'string', 'max:255'],
            'transactions.*.type' => ['required', 'string', 'in:income,expense'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'user_id wajib diisi.',
            'user_id.integer' => 'user_id harus berupa angka bulat.',
            'transactions.required' => 'transactions wajib diisi.',
            'transactions.array' => 'transactions harus berupa array.',
            'transactions.min' => 'Minimal harus ada 1 transaksi.',
            'transactions.*.amount.required' => 'amount transaksi wajib diisi.',
            'transactions.*.category.required' => 'category transaksi wajib diisi.',
            'transactions.*.type.in' => 'type transaksi hanya boleh income atau expense.',
        ];
    }

    public function userId(): int
    {
        return (int) $this->validated('user_id');
    }

    /**
     * @return array<int, array{amount: mixed, category: mixed, type: mixed}>
     */
    public function transactions(): array
    {
        /** @var array<int, array{amount: mixed, category: mixed, type: mixed}> $transactions */
        $transactions = $this->validated('transactions', []);

        return $transactions;
    }
}
