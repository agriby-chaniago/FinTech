<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'user_id' => ['nullable', 'integer', 'min:1'],
            'since' => ['nullable', 'string', 'max:255'],
            'include_summary' => ['nullable', 'boolean'],
            'use_saved_since' => ['nullable', 'boolean'],
        ];
    }

    public function resolvedUserId(): int
    {
        $userId = $this->validated('user_id');

        if (is_numeric($userId)) {
            return (int) $userId;
        }

        return (int) config('services.fintrack_feed.default_user_id', 2);
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
}
