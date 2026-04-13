@extends('layouts.app')

@section('title', 'Planner - Investment Planner Service')

@section('content')
    <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <article class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-xl">
            <h2 class="font-display text-xl font-semibold text-white">Buat Rencana Keuangan</h2>
            <p class="mt-1 text-sm text-slate-300">Masukkan data pemasukan dan pengeluaran untuk menghasilkan saran investasi.</p>

            @if ($users->isEmpty())
                <div class="mt-5 rounded-xl border border-amber-300/30 bg-amber-400/10 p-4 text-sm text-amber-100">
                    Belum ada user pada tabel users. Tambahkan minimal 1 user terlebih dahulu agar planner bisa digunakan.
                </div>
            @else
                <form action="{{ route('web.planner.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label class="mb-1 block text-sm text-slate-200" for="user_id">User</label>
                        <select id="user_id" name="user_id" class="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300 focus:outline-none">
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((int) old('user_id', $selectedUserId) === $user->id)>
                                    #{{ $user->id }} - {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm text-slate-200" for="total_income">Total Income</label>
                            <input id="total_income" name="total_income" type="number" min="0" value="{{ old('total_income', 3000000) }}" class="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm text-slate-200" for="total_expense">Total Expense</label>
                            <input id="total_expense" name="total_expense" type="number" min="0" value="{{ old('total_expense', 2000000) }}" class="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm text-slate-200" for="top_category">Top Category</label>
                            <input id="top_category" name="top_category" type="text" maxlength="255" value="{{ old('top_category', 'food') }}" class="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm text-slate-200" for="saving_percentage">Saving Percentage (%)</label>
                            <input id="saving_percentage" name="saving_percentage" type="number" min="1" max="100" step="0.01" value="{{ old('saving_percentage', 20) }}" class="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm text-slate-200" for="insight">Insight</label>
                        <textarea id="insight" name="insight" rows="3" class="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300 focus:outline-none">{{ old('insight', 'pengeluaran terlalu tinggi') }}</textarea>
                    </div>

                    <button type="submit" class="inline-flex items-center rounded-lg bg-cyan-300 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-200">
                        Generate Plan
                    </button>
                </form>
            @endif
        </article>

        <article class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-xl">
            <h2 class="font-display text-xl font-semibold text-white">Hasil Terbaru</h2>

            @if ($planResult)
                @php
                    $riskStyles = [
                        'low' => 'border-emerald-300/40 bg-emerald-400/10 text-emerald-100',
                        'medium' => 'border-amber-300/40 bg-amber-400/10 text-amber-100',
                        'high' => 'border-rose-300/40 bg-rose-400/10 text-rose-100',
                    ];
                    $riskStyle = $riskStyles[$planResult['risk_level']] ?? 'border-slate-300/40 bg-slate-400/10 text-slate-100';
                @endphp

                <div class="mt-4 space-y-4">
                    <div class="rounded-xl border border-cyan-300/30 bg-cyan-300/10 p-4">
                        <p class="text-xs uppercase tracking-wide text-cyan-100/90">Saving Plan</p>
                        <p class="mt-1 text-2xl font-semibold text-white">Rp {{ number_format($planResult['saving_plan'], 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-slate-900/60 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-300">Investment Recommendation</p>
                        <p class="mt-1 text-sm text-slate-100">{{ $planResult['investment_recommendation'] }}</p>
                    </div>

                    <div class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $riskStyle }}">
                        Risk: {{ $planResult['risk_level'] }}
                    </div>
                </div>
            @else
                <div class="mt-4 rounded-xl border border-white/10 bg-slate-900/60 p-4 text-sm text-slate-300">
                    Belum ada hasil planning pada sesi ini. Isi form di sebelah kiri lalu klik Generate Plan.
                </div>
            @endif
        </article>
    </section>

    <section class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-xl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-semibold text-white">Riwayat Planning</h2>
                <p class="text-sm text-slate-300">Menampilkan data plan terbaru per user.</p>
            </div>
            <form method="GET" action="{{ route('web.planner.index') }}" class="flex items-center gap-2">
                <label class="text-sm text-slate-200" for="filter_user_id">User</label>
                <select id="filter_user_id" name="user_id" class="rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300 focus:outline-none">
                    <option value="">Pilih User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected($selectedUserId === $user->id)>#{{ $user->id }} - {{ $user->name }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg border border-white/20 px-3 py-2 text-sm text-slate-100 hover:bg-white/10" type="submit">Filter</button>
            </form>
        </div>

        @if ($recentPlans->isEmpty())
            <p class="mt-4 text-sm text-slate-300">Belum ada data planning untuk user yang dipilih.</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-white/10 text-sm">
                    <thead class="text-left text-slate-300">
                        <tr>
                            <th class="px-3 py-2">Tanggal</th>
                            <th class="px-3 py-2">Income</th>
                            <th class="px-3 py-2">Expense</th>
                            <th class="px-3 py-2">Saving Plan</th>
                            <th class="px-3 py-2">Risk</th>
                            <th class="px-3 py-2">Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-slate-100">
                        @foreach ($recentPlans as $plan)
                            <tr>
                                <td class="px-3 py-2">{{ $plan->created_at->format('d M Y H:i') }}</td>
                                <td class="px-3 py-2">Rp {{ number_format($plan->total_income, 0, ',', '.') }}</td>
                                <td class="px-3 py-2">Rp {{ number_format($plan->total_expense, 0, ',', '.') }}</td>
                                <td class="px-3 py-2">Rp {{ number_format($plan->saving_plan, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 uppercase">{{ $plan->risk_level }}</td>
                                <td class="px-3 py-2">{{ $plan->investment_recommendation }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
