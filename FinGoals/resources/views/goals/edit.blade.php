@extends('layouts.app')

@section('title', 'Edit Goal - Investment Planner Service')

@section('content')
    <section class="mx-auto max-w-2xl rounded-2xl border border-[#45475A]/60 bg-[#1E1E2E]/92 p-6 backdrop-blur-xl">
        <h2 class="font-display text-xl font-semibold text-white">Edit Goal</h2>
        <p class="mt-1 text-sm text-[#BAC2DE]">Perbarui target finansial Anda tanpa perlu memilih user lagi.</p>

        <form action="{{ route('web.goals.update', ['goalId' => $goal->id]) }}" method="POST" class="mt-5 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1 block text-sm text-[#BAC2DE]" for="goal_name">Nama Goal</label>
                <input id="goal_name" name="goal_name" type="text" maxlength="255" value="{{ old('goal_name', $goal->goal_name) }}" class="w-full rounded-lg border border-[#585B70]/75 bg-[#313244]/85 px-3 py-2 text-sm text-[#CDD6F4] focus:border-[#89B4FA] focus:outline-none">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm text-[#BAC2DE]" for="target_amount">Target Amount</label>
                    <input id="target_amount" name="target_amount" type="number" min="1" value="{{ old('target_amount', $goal->target_amount) }}" class="w-full rounded-lg border border-[#585B70]/75 bg-[#313244]/85 px-3 py-2 text-sm text-[#CDD6F4] focus:border-[#89B4FA] focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-sm text-[#BAC2DE]" for="deadline">Deadline</label>
                    <input id="deadline" name="deadline" type="date" value="{{ old('deadline', $goal->deadline->format('Y-m-d')) }}" class="w-full rounded-lg border border-[#585B70]/75 bg-[#313244]/85 px-3 py-2 text-sm text-[#CDD6F4] focus:border-[#89B4FA] focus:outline-none">
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="inline-flex items-center rounded-lg bg-[#89B4FA] px-4 py-2 text-sm font-semibold text-[#11111B] transition hover:bg-[#74C7EC]">
                    Update Goal
                </button>
                <a href="{{ route('web.goals.index') }}" class="inline-flex items-center rounded-lg border border-[#585B70]/75 px-4 py-2 text-sm text-[#CDD6F4] hover:border-[#89B4FA]/50 hover:bg-[#313244]/80">
                    Batal
                </a>
            </div>
        </form>
    </section>
@endsection
