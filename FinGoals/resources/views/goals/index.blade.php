@extends('layouts.app')

@section('title', 'Goals - Investment Planner Service')

@section('content')
    <section class="grid gap-6 lg:grid-cols-[1fr_1fr]">
        <article class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-xl">
            <h2 class="font-display text-xl font-semibold text-white">Filter Goals per User</h2>
            <p class="mt-1 text-sm text-slate-300">Pilih user agar goals yang tampil tetap terisolasi per user.</p>

            @if ($users->isEmpty())
                <div class="mt-5 rounded-xl border border-amber-300/30 bg-amber-400/10 p-4 text-sm text-amber-100">
                    Belum ada user pada tabel users. Tambahkan user terlebih dahulu.
                </div>
            @else
                <form method="GET" action="{{ route('web.goals.index') }}" class="mt-5 flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-56">
                        <label class="mb-1 block text-sm text-slate-200" for="user_id">User</label>
                        <select id="user_id" name="user_id" class="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300 focus:outline-none">
                            <option value="">Pilih User</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected($selectedUserId === $user->id)>#{{ $user->id }} - {{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rounded-lg border border-white/20 px-4 py-2 text-sm text-slate-100 hover:bg-white/10">Terapkan</button>
                </form>
            @endif
        </article>

        <article class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-xl">
            <h2 class="font-display text-xl font-semibold text-white">Tambah Goal</h2>
            <p class="mt-1 text-sm text-slate-300">Buat target finansial baru untuk user terpilih.</p>

            @if (! $selectedUserId)
                <div class="mt-5 rounded-xl border border-slate-300/20 bg-slate-400/10 p-4 text-sm text-slate-200">
                    Pilih user terlebih dahulu di panel kiri.
                </div>
            @else
                <form action="{{ route('web.goals.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $selectedUserId }}">

                    <div>
                        <label class="mb-1 block text-sm text-slate-200" for="goal_name">Nama Goal</label>
                        <input id="goal_name" name="goal_name" type="text" maxlength="255" value="{{ old('goal_name') }}" class="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300 focus:outline-none" placeholder="Contoh: Dana darurat 6 bulan">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm text-slate-200" for="target_amount">Target Amount</label>
                            <input id="target_amount" name="target_amount" type="number" min="1" value="{{ old('target_amount') }}" class="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300 focus:outline-none" placeholder="5000000">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm text-slate-200" for="deadline">Deadline</label>
                            <input id="deadline" name="deadline" type="date" value="{{ old('deadline') }}" class="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300 focus:outline-none">
                        </div>
                    </div>

                    <button type="submit" class="inline-flex items-center rounded-lg bg-cyan-300 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-200">
                        Simpan Goal
                    </button>
                </form>
            @endif
        </article>
    </section>

    <section class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-xl">
        <h2 class="font-display text-xl font-semibold text-white">Daftar Goals</h2>

        @if (! $selectedUserId)
            <p class="mt-3 text-sm text-slate-300">Pilih user terlebih dahulu untuk menampilkan daftar goals.</p>
        @elseif ($goals->isEmpty())
            <p class="mt-3 text-sm text-slate-300">Belum ada goal untuk user ini.</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-white/10 text-sm">
                    <thead class="text-left text-slate-300">
                        <tr>
                            <th class="px-3 py-2">Goal</th>
                            <th class="px-3 py-2">Target</th>
                            <th class="px-3 py-2">Deadline</th>
                            <th class="px-3 py-2">Status Waktu</th>
                            <th class="px-3 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-slate-100">
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
                                        <span class="rounded-full border border-rose-300/40 bg-rose-400/10 px-2 py-1 text-xs text-rose-100">Lewat {{ abs($daysLeft) }} hari</span>
                                    @elseif ($daysLeft <= 14)
                                        <span class="rounded-full border border-amber-300/40 bg-amber-400/10 px-2 py-1 text-xs text-amber-100">{{ $daysLeft }} hari lagi</span>
                                    @else
                                        <span class="rounded-full border border-emerald-300/40 bg-emerald-400/10 px-2 py-1 text-xs text-emerald-100">{{ $daysLeft }} hari lagi</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('web.goals.edit', ['goalId' => $goal->id, 'user_id' => $selectedUserId]) }}" class="rounded-lg border border-cyan-300/40 px-3 py-1 text-xs text-cyan-100 hover:bg-cyan-300/10">Edit</a>
                                        <form method="POST" action="{{ route('web.goals.destroy', ['goalId' => $goal->id]) }}" onsubmit="return confirm('Hapus goal ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="user_id" value="{{ $selectedUserId }}">
                                            <button type="submit" class="rounded-lg border border-rose-300/40 px-3 py-1 text-xs text-rose-100 hover:bg-rose-300/10">Hapus</button>
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
