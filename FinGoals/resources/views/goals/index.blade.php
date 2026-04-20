@extends('layouts.app')

@section('title', 'Goals - Investment Planner Service')

@section('content')
    <section class="grid gap-6 lg:grid-cols-[1fr_1fr]">
        <article class="rounded-2xl border border-[#45475A]/60 bg-[#1E1E2E]/92 p-5 backdrop-blur-xl">
            <h2 class="font-display text-xl font-semibold text-white">Filter Goals per User</h2>
            <p class="mt-1 text-sm text-[#BAC2DE]">Pilih user agar goals yang tampil tetap terisolasi per user.</p>

            @if ($users->isEmpty())
                <div class="mt-5 rounded-xl border border-[#FAB387]/40 bg-[#FAB387]/10 p-4 text-sm text-[#FAB387]">
                    Belum ada user pada tabel users. Tambahkan user terlebih dahulu.
                </div>
            @else
                <form method="GET" action="{{ route('web.goals.index') }}" class="mt-5 flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-56">
                        <label class="mb-1 block text-sm text-[#BAC2DE]" for="user_id">User</label>
                        <select id="user_id" name="user_id" class="w-full rounded-lg border border-[#585B70]/75 bg-[#313244]/85 px-3 py-2 text-sm text-[#CDD6F4] focus:border-[#89B4FA] focus:outline-none">
                            <option value="">Pilih User</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected($selectedUserId === $user->id)>#{{ $user->id }} - {{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rounded-lg border border-[#585B70]/75 px-4 py-2 text-sm text-[#CDD6F4] hover:border-[#89B4FA]/50 hover:bg-[#313244]/80">Terapkan</button>
                </form>
            @endif
        </article>

        <article class="rounded-2xl border border-[#45475A]/60 bg-[#1E1E2E]/92 p-5 backdrop-blur-xl">
            <h2 class="font-display text-xl font-semibold text-white">Tambah Goal</h2>
            <p class="mt-1 text-sm text-[#BAC2DE]">Buat target finansial baru untuk user terpilih.</p>

            @if (! $selectedUserId)
                <div class="mt-5 rounded-xl border border-[#585B70]/60 bg-[#313244]/75 p-4 text-sm text-[#BAC2DE]">
                    Pilih user terlebih dahulu di panel kiri.
                </div>
            @else
                <form action="{{ route('web.goals.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $selectedUserId }}">

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

                    <button type="submit" class="inline-flex items-center rounded-lg bg-[#89B4FA] px-4 py-2 text-sm font-semibold text-[#11111B] transition hover:bg-[#74C7EC]">
                        Simpan Goal
                    </button>
                </form>
            @endif
        </article>
    </section>

    <section class="mt-6 rounded-2xl border border-[#45475A]/60 bg-[#1E1E2E]/92 p-5 backdrop-blur-xl">
        <h2 class="font-display text-xl font-semibold text-white">Daftar Goals</h2>

        @if (! $selectedUserId)
            <p class="mt-3 text-sm text-[#BAC2DE]">Pilih user terlebih dahulu untuk menampilkan daftar goals.</p>
        @elseif ($goals->isEmpty())
            <p class="mt-3 text-sm text-[#BAC2DE]">Belum ada goal untuk user ini.</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-white/10 text-sm">
                    <thead class="text-left text-[#A6ADC8]">
                        <tr>
                            <th class="px-3 py-2">Goal</th>
                            <th class="px-3 py-2">Target</th>
                            <th class="px-3 py-2">Deadline</th>
                            <th class="px-3 py-2">Status Waktu</th>
                            <th class="px-3 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-[#CDD6F4]">
                        @foreach ($goals as $goal)
                            @php
                                $daysLeft = now()->startOfDay()->diffInDays($goal->deadline, false);
                            @endphp
                            <tr>
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
                                        <a href="{{ route('web.goals.edit', ['goalId' => $goal->id, 'user_id' => $selectedUserId]) }}" class="rounded-lg border border-[#89B4FA]/45 px-3 py-1 text-xs text-[#B4BEFE] hover:bg-[#89B4FA]/12">Edit</a>
                                        <form method="POST" action="{{ route('web.goals.destroy', ['goalId' => $goal->id]) }}" onsubmit="return confirm('Hapus goal ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="user_id" value="{{ $selectedUserId }}">
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
