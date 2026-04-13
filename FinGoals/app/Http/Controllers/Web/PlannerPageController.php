<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FinancialPlan;
use App\Models\User;
use App\Services\FinancialPlanningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $authenticatedUserId = $this->authenticatedUserId();

        $usersQuery = User::query()->select(['id', 'name', 'email'])->orderBy('id');

        if ($authenticatedUserId !== null) {
            $usersQuery->whereKey($authenticatedUserId);
        }

        $users = $usersQuery->get();

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
            'user_id' => $this->userIdRules(),
            'total_income' => ['required', 'integer', 'min:0'],
            'total_expense' => ['required', 'integer', 'min:0'],
            'top_category' => ['required', 'string', 'max:255'],
            'insight' => ['required', 'string'],
            'saving_percentage' => ['nullable', 'numeric', 'min:1', 'max:100'],
        ]);

        $validated['user_id'] = $this->resolveRequestUserId($request, $validated['user_id'] ?? null);

        $result = $this->financialPlanningService->createPlan($validated);

        return redirect()
            ->route('web.planner.index', ['user_id' => $validated['user_id']])
            ->with('status', 'Rencana keuangan berhasil dibuat.')
            ->with('plan_result', $result);
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
