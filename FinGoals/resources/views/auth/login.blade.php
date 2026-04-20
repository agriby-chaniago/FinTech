@extends('layouts.app')

@section('title', 'Login - Investment Planner Service')

@section('content')
    @php
        $oidcEnabled = (bool) config('keycloak.enabled', false);
    @endphp

    <section class="mx-auto max-w-2xl rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-xl">
        <h2 class="font-display text-2xl font-semibold text-white">Login FinGoals</h2>
        <p class="mt-2 text-sm text-slate-300">
            Gunakan akun Keycloak untuk mengakses Planner dan Goals dengan data user yang terisolasi.
        </p>

        @if (! $oidcEnabled)
            <div class="mt-6 rounded-xl border border-rose-300/30 bg-rose-400/10 p-4 text-sm text-rose-100">
                KEYCLOAK_ENABLED masih false. Aktifkan Keycloak di .env FinGoals sebelum login.
            </div>

            <a
                href="{{ route('web.planner.index') }}"
                class="mt-6 inline-flex items-center rounded-lg border border-white/20 px-4 py-2 text-sm font-medium text-slate-100 transition hover:bg-white/10"
            >
                Kembali
            </a>
        @else
            <a
                href="{{ route('oidc.redirect') }}"
                class="mt-6 inline-flex items-center rounded-lg bg-cyan-300 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-200"
            >
                Masuk dengan Keycloak
            </a>
        @endif
    </section>
@endsection
