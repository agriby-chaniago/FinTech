<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Investment Planner Service')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-24 -top-32 h-96 w-96 rounded-full bg-cyan-500/20 blur-3xl"></div>
        <div class="absolute -right-24 top-24 h-[28rem] w-[28rem] rounded-full bg-indigo-500/20 blur-3xl"></div>
        <div class="absolute inset-x-0 bottom-0 h-52 bg-gradient-to-t from-slate-950 to-transparent"></div>
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
            </div>
        </header>

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
