@extends('layouts.app')

@section('title', 'Planner - Investment Planner Service')

@section('content')
    @php
        $executedAtLabel = null;

        if (! empty($analysisSnapshot['executed_at'])) {
            try {
                $executedAtLabel = \Illuminate\Support\Carbon::parse($analysisSnapshot['executed_at'])->format('d M Y H:i');
            } catch (\Throwable $exception) {
                $executedAtLabel = (string) $analysisSnapshot['executed_at'];
            }
        }
    @endphp

    <section class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <article class="rounded-2xl border border-white/10 bg-[#23232E]/85 p-6 shadow-xl">
            <h2 class="font-display text-2xl font-semibold text-white">Rencana Otomatis Anda</h2>
            <p class="mt-2 text-sm text-[#DDDDE5]/80">
                Data pemasukan dan pengeluaran diambil otomatis dari Service B. Anda cukup klik sekali untuk membuat plan.
            </p>

            @if ($analysisSnapshot)
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-white/10 bg-[#15151B]/70 p-4">
                        <p class="text-xs uppercase tracking-wide text-[#DDDDE5]/60">Total Income</p>
                        <p class="mt-1 text-lg font-semibold text-white">Rp {{ number_format($analysisSnapshot['total_income'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-[#15151B]/70 p-4">
                        <p class="text-xs uppercase tracking-wide text-[#DDDDE5]/60">Total Expense</p>
                        <p class="mt-1 text-lg font-semibold text-white">Rp {{ number_format($analysisSnapshot['total_expense'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-[#15151B]/70 p-4">
                        <p class="text-xs uppercase tracking-wide text-[#DDDDE5]/60">Net Balance</p>
                        <p class="mt-1 text-lg font-semibold text-white">Rp {{ number_format($analysisSnapshot['net_balance'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-[#15151B]/70 p-4">
                        <p class="text-xs uppercase tracking-wide text-[#DDDDE5]/60">Savings Rate</p>
                        <p class="mt-1 text-lg font-semibold text-white">{{ number_format((float) $analysisSnapshot['savings_rate'], 2, ',', '.') }}%</p>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-[#3B59DD]/40 bg-[#3B59DD]/15 p-4">
                    <p class="text-xs uppercase tracking-wide text-[#B9C5FF]">Insight Otomatis</p>
                    <p class="mt-2 text-sm text-[#E4E8FF]">{{ $analysisSnapshot['summary'] }}</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-[#D2D8FF]/80">
                        <span class="rounded-full border border-[#637BFF]/30 px-2 py-1">Top Category: {{ $analysisSnapshot['top_category'] }}</span>
                        <span class="rounded-full border border-[#637BFF]/30 px-2 py-1">Kondisi: {{ $analysisSnapshot['financial_health'] }}</span>
                        <span class="rounded-full border border-[#637BFF]/30 px-2 py-1">{{ $analysisSnapshot['transaction_count'] }} transaksi</span>
                    </div>
                </div>

                <form action="{{ route('web.planner.store') }}" method="POST" class="mt-5">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-lg bg-[#3B59DD] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#324ECC]">
                        Generate Plan Otomatis
                    </button>
                </form>

                @if ($executedAtLabel)
                    <p class="mt-3 text-xs text-[#DDDDE5]/60">Data terakhir diperbarui: {{ $executedAtLabel }}</p>
                @endif
            @else
                <div class="mt-5 rounded-xl border border-amber-300/30 bg-amber-400/10 p-4 text-sm text-amber-100">
                    {{ $analysisMessage ?? 'Data analisis terbaru belum tersedia di Service B.' }}
                    <div class="mt-3">
                        <a href="{{ route('web.planner.index') }}" class="inline-flex items-center rounded-lg border border-amber-200/40 px-3 py-1.5 text-xs font-semibold text-amber-50 hover:bg-amber-300/10">
                            Muat Ulang Data
                        </a>
                    </div>
                </div>
            @endif
        </article>

        <article class="rounded-2xl border border-white/10 bg-[#23232E]/85 p-6 shadow-xl">
            <h2 class="font-display text-2xl font-semibold text-white">Hasil Plan Terbaru</h2>

            @if ($planResult)
                @php
                    $riskStyles = [
                        'low' => 'border-emerald-300/40 bg-emerald-400/10 text-emerald-100',
                        'medium' => 'border-amber-300/40 bg-amber-400/10 text-amber-100',
                        'high' => 'border-rose-300/40 bg-rose-400/10 text-rose-100',
                    ];

                    $riskStyle = $riskStyles[$planResult['risk_level']] ?? 'border-[#637BFF]/40 bg-[#3B59DD]/20 text-[#D5DEFF]';
                @endphp

                <div class="mt-4 space-y-4">
                    <div class="rounded-xl border border-[#637BFF]/40 bg-[#3B59DD]/15 p-4">
                        <p class="text-xs uppercase tracking-wide text-[#CED6FF]">Saving Plan</p>
                        <p class="mt-1 text-3xl font-semibold text-white">Rp {{ number_format($planResult['saving_plan'], 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-[#15151B]/70 p-4">
                        <p class="text-xs uppercase tracking-wide text-[#DDDDE5]/60">Investment Recommendation</p>
                        <p class="mt-2 text-sm text-[#F1F2F7]">{{ $planResult['investment_recommendation'] }}</p>
                    </div>

                    <div class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $riskStyle }}">
                        Risk: {{ $planResult['risk_level'] }}
                    </div>
                </div>
            @else
                <div class="mt-4 rounded-xl border border-white/10 bg-[#15151B]/70 p-4 text-sm text-[#DDDDE5]/75">
                    Belum ada hasil plan pada sesi ini. Klik <strong>Generate Plan Otomatis</strong> untuk membuat plan dari data terbaru.
                </div>
            @endif
        </article>
    </section>

    <section class="mt-6 rounded-2xl border border-white/10 bg-[#23232E]/85 p-5 shadow-xl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-semibold text-white">Riwayat Planning Anda</h2>
                <p class="text-sm text-[#DDDDE5]/75">Menampilkan history plan terbaru dari akun yang sedang login.</p>
            </div>
        </div>

        @if ($recentPlans->isEmpty())
            <p class="mt-4 text-sm text-[#DDDDE5]/75">Belum ada riwayat planning untuk akun Anda.</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-white/10 text-sm">
                    <thead class="text-left text-[#DDDDE5]/70">
                        <tr>
                            <th class="px-3 py-2">Tanggal</th>
                            <th class="px-3 py-2">Income</th>
                            <th class="px-3 py-2">Expense</th>
                            <th class="px-3 py-2">Saving Plan</th>
                            <th class="px-3 py-2">Risk</th>
                            <th class="px-3 py-2">Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-[#F4F5FA]">
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
