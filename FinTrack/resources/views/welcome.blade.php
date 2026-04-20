<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-night text-platinum">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FinTrack</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-gradient-to-br from-night via-raisin to-raisin2">

    <div class="min-h-screen flex items-center px-8 sm:px-16 py-12">
        <div class="space-y-10 animate-fadeIn max-w-3xl">
            <h1 class="text-5xl sm:text-6xl font-extrabold text-byzantine">
                FinTrack
            </h1>
            <p class="text-xl sm:text-2xl text-platinum/80 leading-relaxed">
                Aplikasi manajemen keuangan minimalis. <br> Login menggunakan akun Keycloak untuk mulai menggunakan.
            </p>

            <div class="flex flex-col sm:flex-row gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="px-6 py-3 text-lg bg-byzantine text-night rounded-lg font-semibold hover:bg-byzantine/90 transition">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('oidc.redirect') }}"
                       class="px-6 py-3 text-lg bg-byzantine text-night rounded-lg font-semibold hover:bg-byzantine/90 transition">
                        Masuk dengan Keycloak
                    </a>
                @endauth
            </div>
        </div>
    </div>

</body>
</html>
