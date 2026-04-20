<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogoutSyncController extends Controller
{
    public function revoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'keycloak_sub' => ['required', 'string', 'max:255'],
        ]);

        $keycloakSub = trim((string) $validated['keycloak_sub']);

        if ($keycloakSub === '') {
            return response()->json([
                'message' => 'keycloak_sub wajib diisi.',
                'revoked_sessions' => 0,
            ], 422);
        }

        $user = User::query()
            ->where('keycloak_sub', $keycloakSub)
            ->first();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Tidak ada user lokal yang cocok.',
                'revoked_sessions' => 0,
            ]);
        }

        $revokedSessions = DB::table('sessions')
            ->where('user_id', $user->getKey())
            ->delete();

        return response()->json([
            'message' => 'Sesi lokal berhasil dicabut.',
            'revoked_sessions' => $revokedSessions,
        ]);
    }
}
