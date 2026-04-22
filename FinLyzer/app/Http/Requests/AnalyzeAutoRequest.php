<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AnalyzeAutoRequest extends FormRequest
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
        $defaultUserId = (int) config('services.fintrack_feed.default_user_id', 0);
        $emailRule = (Auth::check() || $defaultUserId > 0) ? 'nullable' : 'required';

        return [
            'email' => [$emailRule, 'string', 'email:rfc', 'max:255'],
            'since' => ['nullable', 'string', 'max:255'],
            'include_summary' => ['nullable', 'boolean'],
            'use_saved_since' => ['nullable', 'boolean'],
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
        ];
    }

    public function resolvedUserId(): int
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

        $requestedEmail = $this->normalizeEmail($this->validated('email'));

        if ($requestedEmail === '') {
            $defaultUserId = (int) config('services.fintrack_feed.default_user_id', 0);

            if ($defaultUserId > 0) {
                return $defaultUserId;
            }

            throw new AuthorizationException('email wajib diisi.');
        }

        $user = $this->resolveUserByEmail($requestedEmail);

        if (! $user instanceof User) {
            throw new AuthorizationException('email tidak ditemukan pada database user.');
        }

        return (int) $user->id;
    }

    public function resolvedEmail(): ?string
    {
        $authenticatedUser = Auth::user();

        if ($authenticatedUser instanceof User) {
            $email = $this->normalizeEmail($authenticatedUser->email);

            return $email !== '' ? $email : null;
        }

        $requestedEmail = $this->normalizeEmail($this->validated('email'));

        if ($requestedEmail !== '') {
            return $requestedEmail;
        }

        return null;
    }

    public function since(): ?string
    {
        $value = $this->validated('since');

        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : null;
    }

    public function includeSummary(): bool
    {
        $value = $this->validated('include_summary', false);

        if (is_bool($value)) {
            return $value;
        }

        return in_array($value, [1, '1', 'true', 'yes'], true);
    }

    public function useSavedSince(): bool
    {
        $value = $this->validated(
            'use_saved_since',
            (bool) config('services.fintrack_feed.use_saved_since', true)
        );

        if (is_bool($value)) {
            return $value;
        }

        return in_array($value, [1, '1', 'true', 'yes'], true);
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
}
