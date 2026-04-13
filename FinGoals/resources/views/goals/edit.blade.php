@extends('layouts.app')

@section('title', 'Edit Goal - Investment Planner Service')

@section('content')
    <section class="mx-auto max-w-2xl rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-xl">
        <h2 class="font-display text-xl font-semibold text-white">Edit Goal</h2>
        <p class="mt-1 text-sm text-slate-300">Perbarui target finansial sesuai rencana terbaru Anda.</p>

        <form action="{{ route('web.goals.update', ['goalId' => $goal->id]) }}" method="POST" class="mt-5 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="user_id" value="{{ $selectedUserId }}">

            <div>
                <label class="mb-1 block text-sm text-slate-200" for="goal_name">Nama Goal</label>
                <input id="goal_name" name="goal_name" type="text" maxlength="255" value="{{ old('goal_name', $goal->goal_name) }}" class="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300 focus:outline-none">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm text-slate-200" for="target_amount">Target Amount</label>
                    <input id="target_amount" name="target_amount" type="number" min="1" value="{{ old('target_amount', $goal->target_amount) }}" class="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-sm text-slate-200" for="deadline">Deadline</label>
                    <input id="deadline" name="deadline" type="date" value="{{ old('deadline', $goal->deadline->format('Y-m-d')) }}" class="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300 focus:outline-none">
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="inline-flex items-center rounded-lg bg-cyan-300 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-200">
                    Update Goal
                </button>
                <a href="{{ route('web.goals.index', ['user_id' => $selectedUserId]) }}" class="inline-flex items-center rounded-lg border border-white/20 px-4 py-2 text-sm text-slate-100 hover:bg-white/10">
                    Batal
                </a>
            </div>
        </form>
    </section>
@endsection
