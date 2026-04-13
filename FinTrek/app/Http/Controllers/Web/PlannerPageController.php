<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FinancialPlan;
use App\Models\User;
use App\Services\FinancialPlanningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlannerPageController extends Controller
{
    public function __construct(
        private readonly FinancialPlanningService $financialPlanningService
    ) {
    }

    public function index(Request $request): View
    {
        $selectedUserId = $this->resolveSelectedUserId($request);
        $users = User::query()
            ->select(['id', 'name', 'email'])
            ->orderBy('id')
            ->get();

        $recentPlans = collect();

        if ($selectedUserId !== null) {
            $recentPlans = FinancialPlan::query()
                ->where('user_id', $selectedUserId)
                ->latest()
                ->limit(8)
                ->get();
        }

        return view('planner.index', [
            'users' => $users,
            'selectedUserId' => $selectedUserId,
            'recentPlans' => $recentPlans,
            'planResult' => session('plan_result'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'total_income' => ['required', 'integer', 'min:0'],
            'total_expense' => ['required', 'integer', 'min:0'],
            'top_category' => ['required', 'string', 'max:255'],
            'insight' => ['required', 'string'],
            'saving_percentage' => ['nullable', 'numeric', 'min:1', 'max:100'],
        ]);

        $result = $this->financialPlanningService->createPlan($validated);

        return redirect()
            ->route('web.planner.index', ['user_id' => $validated['user_id']])
            ->with('status', 'Rencana keuangan berhasil dibuat.')
            ->with('plan_result', $result);
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
