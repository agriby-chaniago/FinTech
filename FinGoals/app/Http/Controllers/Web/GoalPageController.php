<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GoalPageController extends Controller
{
    public function index(): View
    {
        $authenticatedUserId = $this->authenticatedUserIdOrFail();

        $goals = Goal::query()
            ->where('user_id', $authenticatedUserId)
            ->orderBy('deadline')
            ->get();

        return view('goals.index', [
            'goals' => $goals,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'goal_name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'deadline' => ['required', 'date'],
        ]);

        $validated['user_id'] = $this->authenticatedUserIdOrFail();

        Goal::create($validated);

        return redirect()
            ->route('web.goals.index')
            ->with('status', 'Goal berhasil ditambahkan.');
    }

    public function edit(string $goalId): View
    {
        $goal = $this->findGoalForAuthenticatedUser($goalId);

        return view('goals.edit', [
            'goal' => $goal,
        ]);
    }

    public function update(Request $request, string $goalId): RedirectResponse
    {
        $validated = $request->validate([
            'goal_name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'deadline' => ['required', 'date'],
        ]);

        $goal = $this->findGoalForAuthenticatedUser($goalId);
        $goal->update(Arr::only($validated, ['goal_name', 'target_amount', 'deadline']));

        return redirect()
            ->route('web.goals.index')
            ->with('status', 'Goal berhasil diperbarui.');
    }

    public function destroy(string $goalId): RedirectResponse
    {
        $goal = $this->findGoalForAuthenticatedUser($goalId);
        $goal->delete();

        return redirect()
            ->route('web.goals.index')
            ->with('status', 'Goal berhasil dihapus.');
    }

    private function findGoalForAuthenticatedUser(string $goalId): Goal
    {
        $authenticatedUserId = $this->authenticatedUserIdOrFail();

        return Goal::query()
            ->whereKey($goalId)
            ->where('user_id', $authenticatedUserId)
            ->firstOrFail();
    }

    private function authenticatedUserIdOrFail(): int
    {
        $authenticatedUserId = Auth::id();

        if (! is_numeric($authenticatedUserId)) {
            abort(401, 'Sesi login tidak valid.');
        }

        return (int) $authenticatedUserId;
    }
}
