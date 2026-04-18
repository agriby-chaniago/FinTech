<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OidcUserResolver
{
    /**
     * @param array<string, mixed> $userinfo
     */
    public function resolveFromUserinfo(array $userinfo): ?User
    {
        $keycloakSub = trim((string) data_get($userinfo, 'sub', ''));

        if ($keycloakSub === '') {
            return null;
        }

        $candidateEmails = $this->collectCandidateEmails($userinfo);
        $primaryEmail = $candidateEmails[0] ?? null;
        $name = $this->resolveName($userinfo, $primaryEmail);

        $user = User::query()
            ->where('keycloak_sub', $keycloakSub)
            ->first();

        if (! $user instanceof User && $candidateEmails !== []) {
            $user = User::query()
                ->whereIn('email', $candidateEmails)
                ->orderBy('id')
                ->first();
        }

        if ($user instanceof User) {
            $updates = [
                'name' => $name,
                'keycloak_sub' => $keycloakSub,
            ];

            if (
                $primaryEmail !== null
                && ! hash_equals(strtolower((string) $user->email), $primaryEmail)
                && ! User::query()
                    ->where('email', $primaryEmail)
                    ->where('id', '!=', $user->id)
                    ->exists()
            ) {
                $updates['email'] = $primaryEmail;
            }

            $user->forceFill($updates)->save();

            return $user;
        }

        if ($primaryEmail === null) {
            return null;
        }

        return User::create([
            'name' => $name,
            'email' => $primaryEmail,
            'keycloak_sub' => $keycloakSub,
            'password' => Hash::make(Str::random(40)),
        ]);
    }

    /**
     * @param array<string, mixed> $userinfo
     * @return array<int, string>
     */
    private function collectCandidateEmails(array $userinfo): array
    {
        $rawCandidates = [
            (string) data_get($userinfo, 'email', ''),
            (string) data_get($userinfo, 'preferred_username', ''),
            (string) data_get($userinfo, 'upn', ''),
        ];

        $normalized = [];

        foreach ($rawCandidates as $rawCandidate) {
            $candidate = strtolower(trim($rawCandidate));

            if ($candidate === '') {
                continue;
            }

            if (! filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $normalized[$candidate] = $candidate;
        }

        return array_values($normalized);
    }

    /**
     * @param array<string, mixed> $userinfo
     */
    private function resolveName(array $userinfo, ?string $primaryEmail): string
    {
        $name = trim((string) data_get($userinfo, 'name', ''));

        if ($name === '') {
            $name = trim((string) data_get($userinfo, 'preferred_username', ''));
        }

        if ($name === '' && $primaryEmail !== null) {
            $name = Str::before($primaryEmail, '@');
        }

        return $name !== '' ? $name : 'User';
    }
}
