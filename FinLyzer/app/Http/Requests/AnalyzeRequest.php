<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
        $emailRule = Auth::check() ? 'nullable' : 'required';

        return [
            'email' => [$emailRule, 'string', 'email:rfc', 'max:255'],
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
            'email.required' => 'email wajib diisi.',
            'email.email' => 'email harus berformat email yang valid.',
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
        $authenticatedUser = Auth::user();

        if ($authenticatedUser instanceof User) {
            $requestedEmail = $this->normalizeEmail($this->input('email'));
            $authenticatedEmail = $this->normalizeEmail($authenticatedUser->email);

            if ($requestedEmail !== '' && $authenticatedEmail !== '' && ! hash_equals($authenticatedEmail, $requestedEmail)) {
                throw new AuthorizationException('email tidak sesuai dengan akun login.');
            }

            return (int) $authenticatedUser->id;
        }

        $email = $this->normalizeEmail($this->validated('email'));

        if ($email === '') {
            throw new AuthorizationException('email wajib diisi.');
        }

        $user = $this->resolveUserByEmail($email);

        if (! $user instanceof User) {
            throw new AuthorizationException('email tidak ditemukan pada database user.');
        }

        return (int) $user->id;
    }

    private function normalizeEmail(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }

    private function resolveUserByEmail(string $email): ?User
    {
        return User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();
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
