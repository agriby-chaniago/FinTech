<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class GoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $goals = Goal::query()
            ->where('user_id', $validated['user_id'])
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'goal_name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'deadline' => ['required', 'date'],
        ]);

        $goal = Goal::create($validated);

        return response()->json($goal, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $goal = Goal::query()
            ->where('id', $id)
            ->where('user_id', $validated['user_id'])
            ->firstOrFail();

        return response()->json($goal);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'goal_name' => ['sometimes', 'required', 'string', 'max:255'],
            'target_amount' => ['sometimes', 'required', 'integer', 'min:1'],
            'deadline' => ['sometimes', 'required', 'date'],
        ]);

        $goal = Goal::query()
            ->where('id', $id)
            ->where('user_id', $validated['user_id'])
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $goal = Goal::query()
            ->where('id', $id)
            ->where('user_id', $validated['user_id'])
            ->firstOrFail();

        $goal->delete();

        return response()->json([
            'message' => 'Goal berhasil dihapus.',
        ]);
    }
}
