<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class Service3PlanResultCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'min:1', 'exists:users,id', 'required_without_all:user_email,keycloak_sub'],
            'user_email' => ['nullable', 'string', 'email:rfc', 'max:255', 'required_without_all:user_id,keycloak_sub'],
            'keycloak_sub' => ['nullable', 'string', 'max:255', 'required_without_all:user_id,user_email'],
            'correlation_id' => ['nullable', 'string', 'max:255', 'required_without:analysis_id'],
            'analysis_id' => ['nullable', 'string', 'max:255', 'required_without:correlation_id'],
            'status' => ['required', 'in:success,failed'],
            'summary_text' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'array'],
            'goals' => ['nullable', 'array'],
            'raw_payload' => ['required', 'array'],
            'plan_period_start' => ['nullable', 'date'],
            'plan_period_end' => ['nullable', 'date', 'after_or_equal:plan_period_start'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Invalid request parameters.',
            'errors' => $validator->errors(),
        ], 400));
    }

    public function correlationId(): string
    {
        return (string) ($this->input('correlation_id') ?: $this->input('analysis_id'));
    }

    public function payloadForStorage(): array
    {
        return [
            'user_id' => $this->resolvedUserId(),
            'correlation_id' => $this->correlationId(),
            'analysis_id' => $this->input('analysis_id'),
            'status' => (string) $this->input('status'),
            'summary_text' => $this->input('summary_text'),
            'recommendations' => $this->has('recommendations') ? $this->input('recommendations') : null,
            'goals' => $this->has('goals') ? $this->input('goals') : null,
            'raw_payload' => $this->input('raw_payload'),
            'plan_period_start' => $this->input('plan_period_start'),
            'plan_period_end' => $this->input('plan_period_end'),
            'last_attempted_at' => now(),
        ];
    }

    private function resolvedUserId(): int
    {
        $keycloakSub = trim((string) $this->input('keycloak_sub', ''));
        $userEmail = strtolower(trim((string) $this->input('user_email', '')));

        if ($keycloakSub !== '') {
            $resolvedBySub = User::query()
                ->where('keycloak_sub', $keycloakSub)
                ->first();

            if ($resolvedBySub instanceof User) {
                return (int) $resolvedBySub->id;
            }

            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Invalid request parameters.',
                'errors' => [
                    'keycloak_sub' => ['keycloak_sub tidak ditemukan pada user Service 1.'],
                ],
            ], 400));
        }

        if ($userEmail !== '') {
            $resolvedByEmail = User::query()
                ->whereRaw('LOWER(email) = ?', [$userEmail])
                ->first();

            if ($resolvedByEmail instanceof User) {
                return (int) $resolvedByEmail->id;
            }

            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Invalid request parameters.',
                'errors' => [
                    'user_email' => ['user_email tidak ditemukan pada user Service 1.'],
                ],
            ], 400));
        }

        return (int) $this->input('user_id');
    }
}
