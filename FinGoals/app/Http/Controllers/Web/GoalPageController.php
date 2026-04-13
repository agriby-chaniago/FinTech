<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GoalPageController extends Controller
{
    public function index(Request $request): View
    {
        $selectedUserId = $this->resolveSelectedUserId($request);
        $authenticatedUserId = $this->authenticatedUserId();

        $usersQuery = User::query()->select(['id', 'name', 'email'])->orderBy('id');

        if ($authenticatedUserId !== null) {
            $usersQuery->whereKey($authenticatedUserId);
        }

        $users = $usersQuery->get();

        $goals = collect();

        if ($selectedUserId !== null) {
            $goals = Goal::query()
                ->where('user_id', $selectedUserId)
                ->orderBy('deadline')
                ->get();
        }

        return view('goals.index', [
            'users' => $users,
            'selectedUserId' => $selectedUserId,
            'goals' => $goals,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => $this->userIdRules(),
            'goal_name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'deadline' => ['required', 'date'],
        ]);

        $validated['user_id'] = $this->resolveRequestUserId($request, $validated['user_id'] ?? null);

        Goal::create($validated);

        return redirect()
            ->route('web.goals.index', ['user_id' => $validated['user_id']])
            ->with('status', 'Goal berhasil ditambahkan.');
    }

    public function edit(Request $request, string $goalId): View
    {
        $validated = $request->validate([
            'user_id' => $this->userIdRules(),
        ]);

        $selectedUserId = $this->resolveRequestUserId($request, $validated['user_id'] ?? null);
        $goal = $this->findGoalForUser($goalId, $selectedUserId);

        return view('goals.edit', [
            'goal' => $goal,
            'selectedUserId' => $selectedUserId,
        ]);
    }

    public function update(Request $request, string $goalId): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => $this->userIdRules(),
            'goal_name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'deadline' => ['required', 'date'],
        ]);

        $validated['user_id'] = $this->resolveRequestUserId($request, $validated['user_id'] ?? null);

        $goal = $this->findGoalForUser($goalId, (int) $validated['user_id']);
        $goal->update(Arr::only($validated, ['goal_name', 'target_amount', 'deadline']));

        return redirect()
            ->route('web.goals.index', ['user_id' => $validated['user_id']])
            ->with('status', 'Goal berhasil diperbarui.');
    }

    public function destroy(Request $request, string $goalId): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => $this->userIdRules(),
        ]);

        $validated['user_id'] = $this->resolveRequestUserId($request, $validated['user_id'] ?? null);

        $goal = $this->findGoalForUser($goalId, (int) $validated['user_id']);
        $goal->delete();

        return redirect()
            ->route('web.goals.index', ['user_id' => $validated['user_id']])
            ->with('status', 'Goal berhasil dihapus.');
    }

    private function findGoalForUser(string $goalId, int $userId): Goal
    {
        return Goal::query()
            ->whereKey($goalId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    private function resolveSelectedUserId(Request $request): ?int
    {
        $authenticatedUserId = $this->authenticatedUserId();

        if ($authenticatedUserId !== null) {
            return $authenticatedUserId;
        }

        if (! $request->filled('user_id')) {
            return null;
        }

        $validated = $request->validate([
            'user_id' => ['integer', 'exists:users,id'],
        ]);

        return (int) $validated['user_id'];
    }

    /**
     * @return array<int, string>
     */
    private function userIdRules(): array
    {
        $requiredRule = Auth::check() ? 'nullable' : 'required';

        return [$requiredRule, 'integer', 'exists:users,id'];
    }

    private function resolveRequestUserId(Request $request, mixed $requestedUserId): int
    {
        $authenticatedUserId = $this->authenticatedUserId();

        if ($authenticatedUserId !== null) {
            if (is_numeric($requestedUserId) && (int) $requestedUserId !== $authenticatedUserId) {
                abort(403, 'user_id tidak sesuai dengan akun login.');
            }

            return $authenticatedUserId;
        }

        if (! is_numeric($requestedUserId)) {
            $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
            ]);
        }

        return (int) $requestedUserId;
    }

    private function authenticatedUserId(): ?int
    {
        $authenticatedUserId = Auth::id();

        return is_numeric($authenticatedUserId) ? (int) $authenticatedUserId : null;
    }
}
