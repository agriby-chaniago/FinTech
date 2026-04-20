<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Investment Planner Service')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#11111B] text-[#CDD6F4] antialiased">
    @php
        $authenticatedUser = auth()->user();
    @endphp

    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-24 -top-32 h-96 w-96 rounded-full bg-[#CBA6F7]/22 blur-3xl"></div>
        <div class="absolute -right-24 top-24 h-112 w-md rounded-full bg-[#89B4FA]/18 blur-3xl"></div>
        <div class="absolute inset-x-0 bottom-0 h-52 bg-linear-to-t from-[#11111B] to-transparent"></div>
    </div>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <header class="mb-8 rounded-2xl border border-[#45475A]/60 bg-[#1E1E2E]/90 p-5 backdrop-blur-xl">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-[#B4BEFE]">Microservice</p>
                    <h1 class="font-display text-2xl font-semibold text-white sm:text-3xl">Investment Planner Service</h1>
                    <p class="mt-2 text-sm text-[#BAC2DE]/85">Perencanaan keuangan dan investasi sederhana berbasis data pengguna.</p>
                </div>
                <nav class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('web.planner.index') }}"
                        class="rounded-lg border px-4 py-2 text-sm font-medium transition {{ request()->routeIs('web.planner.*') ? 'border-[#89B4FA] bg-[#313244] text-[#CDD6F4]' : 'border-[#45475A]/70 bg-transparent text-[#A6ADC8] hover:border-[#89B4FA]/50 hover:bg-[#313244]/80 hover:text-[#CDD6F4]' }}"
                    >
                        Planner
                    </a>
                    <a
                        href="{{ route('web.goals.index') }}"
                        class="rounded-lg border px-4 py-2 text-sm font-medium transition {{ request()->routeIs('web.goals.*') ? 'border-[#89B4FA] bg-[#313244] text-[#CDD6F4]' : 'border-[#45475A]/70 bg-transparent text-[#A6ADC8] hover:border-[#89B4FA]/50 hover:bg-[#313244]/80 hover:text-[#CDD6F4]' }}"
                    >
                        Goals
                    </a>
                </nav>

                <div class="flex items-center gap-2">
                    @if ($authenticatedUser)
                        <span class="rounded-lg border border-[#A6E3A1]/45 bg-[#A6E3A1]/12 px-3 py-2 text-xs text-[#A6E3A1]">
                            Login: {{ $authenticatedUser->name }}
                        </span>
                        <form action="{{ route('oidc.logout') }}" method="POST">
                            @csrf
                            <button
                                type="submit"
                                class="rounded-lg border border-[#585B70]/70 px-4 py-2 text-sm font-medium text-[#CDD6F4] transition hover:border-[#89B4FA]/50 hover:bg-[#313244]/80"
                            >
                                Logout
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </header>

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-[#89B4FA]/40 bg-[#89B4FA]/15 px-4 py-3 text-sm text-[#CDD6F4]">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-[#F38BA8]/40 bg-[#F38BA8]/12 px-4 py-3 text-sm text-[#F5C2E7]">
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
