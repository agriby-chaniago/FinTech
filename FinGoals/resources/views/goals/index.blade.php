@extends('layouts.app')

@section('title', 'Goals - Investment Planner Service')

@section('content')
    @php
        $authenticatedUser = auth()->user();
    @endphp

    <section class="rounded-2xl border border-[#45475A]/60 bg-[#1E1E2E]/92 p-5 shadow-xl">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="font-display text-2xl font-semibold text-white">Goals Anda</h2>
                <p class="mt-1 text-sm text-[#BAC2DE]/90">
                    Semua goals otomatis terikat ke akun login Anda. Tidak perlu pilih user secara manual.
                </p>
            </div>

            @if ($authenticatedUser)
                <span class="rounded-full border border-[#89B4FA]/45 bg-[#89B4FA]/15 px-3 py-1 text-xs font-medium text-[#B4BEFE]">
                    Akun aktif: {{ $authenticatedUser->name }}
                </span>
            @endif
        </div>

        <form action="{{ route('web.goals.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf

            <div>
                <label class="mb-1 block text-sm text-[#BAC2DE]" for="goal_name">Nama Goal</label>
                <input id="goal_name" name="goal_name" type="text" maxlength="255" value="{{ old('goal_name') }}" class="w-full rounded-lg border border-[#585B70]/75 bg-[#313244]/85 px-3 py-2 text-sm text-[#CDD6F4] focus:border-[#89B4FA] focus:outline-none" placeholder="Contoh: Dana darurat 6 bulan">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm text-[#BAC2DE]" for="target_amount">Target Amount</label>
                    <input id="target_amount" name="target_amount" type="number" min="1" value="{{ old('target_amount') }}" class="w-full rounded-lg border border-[#585B70]/75 bg-[#313244]/85 px-3 py-2 text-sm text-[#CDD6F4] focus:border-[#89B4FA] focus:outline-none" placeholder="5000000">
                </div>
                <div>
                    <label class="mb-1 block text-sm text-[#BAC2DE]" for="deadline">Deadline</label>
                    <input id="deadline" name="deadline" type="date" value="{{ old('deadline') }}" class="w-full rounded-lg border border-[#585B70]/75 bg-[#313244]/85 px-3 py-2 text-sm text-[#CDD6F4] focus:border-[#89B4FA] focus:outline-none">
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="inline-flex items-center rounded-lg bg-[#89B4FA] px-4 py-2 text-sm font-semibold text-[#11111B] transition hover:bg-[#74C7EC]">
                    Simpan Goal
                </button>
                <p class="text-xs text-[#A6ADC8]">Tip: gunakan nama goal yang spesifik agar tracking lebih jelas.</p>
            </div>
        </form>
    </section>

    <section class="mt-6 rounded-2xl border border-[#45475A]/60 bg-[#1E1E2E]/92 p-5 shadow-xl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-semibold text-white">Daftar Goals Anda</h2>
                <p class="text-sm text-[#BAC2DE]">Semua data di bawah ini milik akun yang sedang login.</p>
            </div>
            <span class="rounded-full border border-[#585B70]/60 px-3 py-1 text-xs text-[#A6ADC8]">
                Total goals: {{ $goals->count() }}
            </span>
        </div>

        @if ($goals->isEmpty())
            <div class="mt-4 rounded-xl border border-[#585B70]/60 bg-[#313244]/75 p-4 text-sm text-[#BAC2DE]">
                Belum ada goal. Mulai dengan menambahkan target finansial pertama Anda.
            </div>
        @else
            <div class="mt-4 overflow-x-auto rounded-xl border border-[#585B70]/45">
                <table class="min-w-full divide-y divide-white/10 text-sm">
                    <thead class="bg-[#313244]/80 text-left text-[#A6ADC8]">
                        <tr>
                            <th class="px-3 py-2">Goal</th>
                            <th class="px-3 py-2">Target</th>
                            <th class="px-3 py-2">Deadline</th>
                            <th class="px-3 py-2">Status Waktu</th>
                            <th class="px-3 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 bg-[#1E1E2E]/70 text-[#CDD6F4]">
                        @foreach ($goals as $goal)
                            @php
                                $daysLeft = now()->startOfDay()->diffInDays($goal->deadline, false);
                            @endphp
                            <tr class="transition hover:bg-[#313244]/65">
                                <td class="px-3 py-2">{{ $goal->goal_name }}</td>
                                <td class="px-3 py-2">Rp {{ number_format($goal->target_amount, 0, ',', '.') }}</td>
                                <td class="px-3 py-2">{{ $goal->deadline->format('d M Y') }}</td>
                                <td class="px-3 py-2">
                                    @if ($daysLeft < 0)
                                        <span class="rounded-full border border-[#F38BA8]/45 bg-[#F38BA8]/12 px-2 py-1 text-xs text-[#F5C2E7]">Lewat {{ abs($daysLeft) }} hari</span>
                                    @elseif ($daysLeft <= 14)
                                        <span class="rounded-full border border-[#F9E2AF]/45 bg-[#F9E2AF]/12 px-2 py-1 text-xs text-[#F9E2AF]">{{ $daysLeft }} hari lagi</span>
                                    @else
                                        <span class="rounded-full border border-[#A6E3A1]/45 bg-[#A6E3A1]/12 px-2 py-1 text-xs text-[#A6E3A1]">{{ $daysLeft }} hari lagi</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('web.goals.edit', ['goalId' => $goal->id]) }}" class="rounded-lg border border-[#89B4FA]/45 px-3 py-1 text-xs text-[#B4BEFE] hover:bg-[#89B4FA]/12">Edit</a>
                                        <form method="POST" action="{{ route('web.goals.destroy', ['goalId' => $goal->id]) }}" onsubmit="return confirm('Hapus goal ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-[#F38BA8]/45 px-3 py-1 text-xs text-[#F5C2E7] hover:bg-[#F38BA8]/12">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
