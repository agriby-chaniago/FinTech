<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class GoalPageController extends Controller
{
    public function index(Request $request): View
    {
        $selectedUserId = $this->resolveSelectedUserId($request);

        $users = User::query()
            ->select(['id', 'name', 'email'])
            ->orderBy('id')
            ->get();

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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'goal_name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'deadline' => ['required', 'date'],
        ]);

        Goal::create($validated);

        return redirect()
            ->route('web.goals.index', ['user_id' => $validated['user_id']])
            ->with('status', 'Goal berhasil ditambahkan.');
    }

    public function edit(Request $request, string $goalId): View
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $selectedUserId = (int) $validated['user_id'];
        $goal = $this->findGoalForUser($goalId, $selectedUserId);

        return view('goals.edit', [
            'goal' => $goal,
            'selectedUserId' => $selectedUserId,
        ]);
    }

    public function update(Request $request, string $goalId): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'goal_name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'deadline' => ['required', 'date'],
        ]);

        $goal = $this->findGoalForUser($goalId, (int) $validated['user_id']);
        $goal->update(Arr::only($validated, ['goal_name', 'target_amount', 'deadline']));

        return redirect()
            ->route('web.goals.index', ['user_id' => $validated['user_id']])
            ->with('status', 'Goal berhasil diperbarui.');
    }

    public function destroy(Request $request, string $goalId): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

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
        if (! $request->filled('user_id')) {
            return null;
        }

        $validated = $request->validate([
            'user_id' => ['integer', 'exists:users,id'],
        ]);

        return (int) $validated['user_id'];
    }
}
