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
        <article class="rounded-2xl border border-[#45475A]/60 bg-[#1E1E2E]/92 p-6 shadow-xl">
            <h2 class="font-display text-2xl font-semibold text-white">Rencana Otomatis Anda</h2>
            <p class="mt-2 text-sm text-[#BAC2DE]/85">
                Data pemasukan dan pengeluaran diambil otomatis dari Service B. Anda cukup klik sekali untuk membuat plan.
            </p>

            @if ($analysisSnapshot)
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-[#585B70]/55 bg-[#313244]/85 p-4">
                        <p class="text-xs uppercase tracking-wide text-[#A6ADC8]">Total Income</p>
                        <p class="mt-1 text-lg font-semibold text-white">Rp {{ number_format($analysisSnapshot['total_income'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-[#585B70]/55 bg-[#313244]/85 p-4">
                        <p class="text-xs uppercase tracking-wide text-[#A6ADC8]">Total Expense</p>
                        <p class="mt-1 text-lg font-semibold text-white">Rp {{ number_format($analysisSnapshot['total_expense'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-[#585B70]/55 bg-[#313244]/85 p-4">
                        <p class="text-xs uppercase tracking-wide text-[#A6ADC8]">Net Balance</p>
                        <p class="mt-1 text-lg font-semibold text-white">Rp {{ number_format($analysisSnapshot['net_balance'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-[#585B70]/55 bg-[#313244]/85 p-4">
                        <p class="text-xs uppercase tracking-wide text-[#A6ADC8]">Savings Rate</p>
                        <p class="mt-1 text-lg font-semibold text-white">{{ number_format((float) $analysisSnapshot['savings_rate'], 2, ',', '.') }}%</p>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-[#89B4FA]/40 bg-[#89B4FA]/15 p-4">
                    <p class="text-xs uppercase tracking-wide text-[#B4BEFE]">Insight Otomatis</p>
                    <p class="mt-2 text-sm text-[#CDD6F4]">{{ $analysisSnapshot['summary'] }}</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-[#BAC2DE]">
                        <span class="rounded-full border border-[#B4BEFE]/35 px-2 py-1">Top Category: {{ $analysisSnapshot['top_category'] }}</span>
                        <span class="rounded-full border border-[#B4BEFE]/35 px-2 py-1">Kondisi: {{ $analysisSnapshot['financial_health'] }}</span>
                        <span class="rounded-full border border-[#B4BEFE]/35 px-2 py-1">{{ $analysisSnapshot['transaction_count'] }} transaksi</span>
                    </div>
                </div>

                <form action="{{ route('web.planner.store') }}" method="POST" class="mt-5">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-lg bg-[#89B4FA] px-5 py-2.5 text-sm font-semibold text-[#11111B] transition hover:bg-[#74C7EC]">
                        Generate Plan Otomatis
                    </button>
                </form>

                @if ($executedAtLabel)
                    <p class="mt-3 text-xs text-[#A6ADC8]">Data terakhir diperbarui: {{ $executedAtLabel }}</p>
                @endif
            @else
                <div class="mt-5 rounded-xl border border-[#F9E2AF]/40 bg-[#F9E2AF]/10 p-4 text-sm text-[#F9E2AF]">
                    {{ $analysisMessage ?? 'Data analisis terbaru belum tersedia di Service B.' }}
                    <div class="mt-3">
                        <a href="{{ route('web.planner.index') }}" class="inline-flex items-center rounded-lg border border-[#F9E2AF]/45 px-3 py-1.5 text-xs font-semibold text-[#F9E2AF] hover:bg-[#F9E2AF]/10">
                            Muat Ulang Data
                        </a>
                    </div>
                </div>
            @endif
        </article>

        <article class="rounded-2xl border border-[#45475A]/60 bg-[#1E1E2E]/92 p-6 shadow-xl">
            <h2 class="font-display text-2xl font-semibold text-white">Hasil Plan Terbaru</h2>

            @if ($planResult)
                @php
                    $riskStyles = [
                        'low' => 'border-[#A6E3A1]/45 bg-[#A6E3A1]/12 text-[#A6E3A1]',
                        'medium' => 'border-[#F9E2AF]/45 bg-[#F9E2AF]/12 text-[#F9E2AF]',
                        'high' => 'border-[#F38BA8]/45 bg-[#F38BA8]/12 text-[#F5C2E7]',
                    ];

                    $riskStyle = $riskStyles[$planResult['risk_level']] ?? 'border-[#B4BEFE]/45 bg-[#CBA6F7]/16 text-[#E8D8FF]';
                @endphp

                <div class="mt-4 space-y-4">
                    <div class="rounded-xl border border-[#89B4FA]/40 bg-[#89B4FA]/15 p-4">
                        <p class="text-xs uppercase tracking-wide text-[#B4BEFE]">Saving Plan</p>
                        <p class="mt-1 text-3xl font-semibold text-white">Rp {{ number_format($planResult['saving_plan'], 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-xl border border-[#585B70]/55 bg-[#313244]/85 p-4">
                        <p class="text-xs uppercase tracking-wide text-[#A6ADC8]">Investment Recommendation</p>
                        <p class="mt-2 text-sm text-[#CDD6F4]">{{ $planResult['investment_recommendation'] }}</p>
                    </div>

                    <div class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $riskStyle }}">
                        Risk: {{ $planResult['risk_level'] }}
                    </div>
                </div>
            @else
                <div class="mt-4 rounded-xl border border-[#585B70]/55 bg-[#313244]/85 p-4 text-sm text-[#BAC2DE]">
                    Belum ada hasil plan pada sesi ini. Klik <strong>Generate Plan Otomatis</strong> untuk membuat plan dari data terbaru.
                </div>
            @endif
        </article>
    </section>

    <section class="mt-6 rounded-2xl border border-[#45475A]/60 bg-[#1E1E2E]/92 p-5 shadow-xl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-semibold text-white">Riwayat Planning Anda</h2>
                <p class="text-sm text-[#BAC2DE]">Menampilkan history plan terbaru dari akun yang sedang login.</p>
            </div>
        </div>

        @if ($recentPlans->isEmpty())
            <p class="mt-4 text-sm text-[#BAC2DE]">Belum ada riwayat planning untuk akun Anda.</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-white/10 text-sm">
                    <thead class="text-left text-[#A6ADC8]">
                        <tr>
                            <th class="px-3 py-2">Tanggal</th>
                            <th class="px-3 py-2">Income</th>
                            <th class="px-3 py-2">Expense</th>
                            <th class="px-3 py-2">Saving Plan</th>
                            <th class="px-3 py-2">Risk</th>
                            <th class="px-3 py-2">Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-[#CDD6F4]">
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
