<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'user_id' => $this->userIdRules(),
        ]);

        $userId = $this->resolveUserId($request, $validated['user_id'] ?? null);

        $goals = Goal::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($goals);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => $this->userIdRules(),
            'goal_name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'deadline' => ['required', 'date'],
        ]);

        $validated['user_id'] = $this->resolveUserId($request, $validated['user_id'] ?? null);

        $goal = Goal::create($validated);

        return response()->json($goal, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $validated = $request->validate([
            'user_id' => $this->userIdRules(),
        ]);

        $userId = $this->resolveUserId($request, $validated['user_id'] ?? null);

        $goal = Goal::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        return response()->json($goal);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'user_id' => $this->userIdRules(),
            'goal_name' => ['sometimes', 'required', 'string', 'max:255'],
            'target_amount' => ['sometimes', 'required', 'integer', 'min:1'],
            'deadline' => ['sometimes', 'required', 'date'],
        ]);

        $userId = $this->resolveUserId($request, $validated['user_id'] ?? null);

        $goal = Goal::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $goal->update(Arr::except($validated, ['user_id']));

        return response()->json($goal);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $validated = $request->validate([
            'user_id' => $this->userIdRules(),
        ]);

        $userId = $this->resolveUserId($request, $validated['user_id'] ?? null);

        $goal = Goal::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $goal->delete();

        return response()->json([
            'message' => 'Goal berhasil dihapus.',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function userIdRules(): array
    {
        $requiredRule = Auth::check() ? 'nullable' : 'required';

        return [$requiredRule, 'integer', 'exists:users,id'];
    }

    private function resolveUserId(Request $request, mixed $requestedUserId): int
    {
        $authenticatedUserId = Auth::id();

        if (is_numeric($authenticatedUserId)) {
            $resolvedUserId = (int) $authenticatedUserId;

            if (is_numeric($requestedUserId) && (int) $requestedUserId !== $resolvedUserId) {
                abort(403, 'user_id tidak sesuai dengan akun login.');
            }

            return $resolvedUserId;
        }

        if (! is_numeric($requestedUserId)) {
            $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
            ]);
        }

        return (int) $requestedUserId;
    }
}
