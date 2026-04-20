<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Investment Planner Service')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    @php
        $authMode = strtolower(trim((string) config('keycloak.auth_mode', 'legacy')));
        $oidcEnabled = (bool) config('keycloak.enabled', false);
        $authenticatedUser = auth()->user();
    @endphp

    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-24 -top-32 h-96 w-96 rounded-full bg-cyan-500/20 blur-3xl"></div>
        <div class="absolute -right-24 top-24 h-112 w-md rounded-full bg-indigo-500/20 blur-3xl"></div>
        <div class="absolute inset-x-0 bottom-0 h-52 bg-linear-to-t from-slate-950 to-transparent"></div>
    </div>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <header class="mb-8 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-xl">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-cyan-200/80">Microservice</p>
                    <h1 class="font-display text-2xl font-semibold text-white sm:text-3xl">Investment Planner Service</h1>
                    <p class="mt-2 text-sm text-slate-300">Perencanaan keuangan dan investasi sederhana berbasis data pengguna.</p>
                </div>
                <nav class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('web.planner.index') }}"
                        class="rounded-lg border px-4 py-2 text-sm font-medium transition {{ request()->routeIs('web.planner.*') ? 'border-cyan-300 bg-cyan-300/20 text-cyan-100' : 'border-white/15 bg-white/0 text-slate-200 hover:border-white/30 hover:bg-white/10' }}"
                    >
                        Planner
                    </a>
                    <a
                        href="{{ route('web.goals.index') }}"
                        class="rounded-lg border px-4 py-2 text-sm font-medium transition {{ request()->routeIs('web.goals.*') ? 'border-cyan-300 bg-cyan-300/20 text-cyan-100' : 'border-white/15 bg-white/0 text-slate-200 hover:border-white/30 hover:bg-white/10' }}"
                    >
                        Goals
                    </a>
                </nav>

                <div class="flex items-center gap-2">
                    @if ($authenticatedUser)
                        <span class="rounded-lg border border-emerald-300/30 bg-emerald-400/10 px-3 py-2 text-xs text-emerald-100">
                            Login: {{ $authenticatedUser->name }}
                        </span>
                        <form action="{{ route('oidc.logout') }}" method="POST">
                            @csrf
                            <button
                                type="submit"
                                class="rounded-lg border border-rose-300/40 px-4 py-2 text-sm font-medium text-rose-100 transition hover:bg-rose-300/10"
                            >
                                Logout
                            </button>
                        </form>
                    @elseif ($authMode !== 'legacy')
                        <a
                            href="{{ route('login') }}"
                            class="rounded-lg border border-cyan-300/40 bg-cyan-300/10 px-4 py-2 text-sm font-medium text-cyan-100 transition hover:bg-cyan-300/20"
                        >
                            Login
                        </a>
                    @endif
                </div>
            </div>
        </header>

        @if ($authMode !== 'legacy' && ! $oidcEnabled)
            <div class="mb-6 rounded-xl border border-amber-300/30 bg-amber-400/10 px-4 py-3 text-sm text-amber-100">
                OIDC mode aktif, tetapi KEYCLOAK_ENABLED masih false. Aktifkan KEYCLOAK_ENABLED=true di environment FinGoals.
            </div>
        @endif

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-300/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-rose-300/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-100">
                <p class="font-semibold">Validasi gagal:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
