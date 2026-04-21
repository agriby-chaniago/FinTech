<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login {{ config('app.name', 'FinLyzer') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a1120;
            --card: #14233a;
            --line: #2e4668;
            --text: #e6eefc;
            --muted: #9db2d2;
            --accent: #22d3ee;
            --danger: #ef4444;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: "Manrope", "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(500px 240px at 20% -10%, rgba(34, 211, 238, 0.14), transparent 72%),
                linear-gradient(160deg, var(--bg), #0f1a2d 65%, #101f35);
            padding: 1rem;
        }

        .card {
            width: min(460px, 100%);
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(20, 35, 58, 0.96);
            box-shadow: 0 16px 38px rgba(1, 6, 16, 0.46);
            padding: 1rem;
        }

        h1 {
            margin: 0;
            font-size: 1.28rem;
        }

        p {
            margin: 0.4rem 0 0;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        form {
            margin-top: 0.8rem;
            display: grid;
            gap: 0.55rem;
        }

        label {
            display: block;
            margin-bottom: 0.24rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #adc3e3;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            border: 1px solid #365175;
            border-radius: 10px;
            background: #101f34;
            color: var(--text);
            font-size: 0.9rem;
            padding: 0.6rem 0.64rem;
        }

        input:focus {
            outline: 2px solid rgba(34, 211, 238, 0.24);
            border-color: #37b5ce;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 0.44rem;
            color: var(--muted);
            font-size: 0.82rem;
        }

        .actions {
            display: grid;
            gap: 0.42rem;
            margin-top: 0.2rem;
        }

        .btn {
            border: 0;
            border-radius: 10px;
            padding: 0.62rem 0.68rem;
            font-size: 0.84rem;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }

        .btn-primary {
            color: #032228;
            background: linear-gradient(120deg, #22d3ee, #06b6d4);
        }

        .btn-soft {
            color: #d4e7ff;
            border: 1px solid #3b5a80;
            background: #1b2f49;
        }

        .error {
            margin-top: 0.7rem;
            border: 1px solid rgba(239, 68, 68, 0.34);
            border-radius: 10px;
            background: rgba(239, 68, 68, 0.12);
            color: #fecaca;
            padding: 0.56rem 0.62rem;
            font-size: 0.82rem;
        }

        .status-ok {
            margin-top: 0.7rem;
            border: 1px solid rgba(34, 197, 94, 0.35);
            border-radius: 10px;
            background: rgba(34, 197, 94, 0.12);
            color: #bbf7d0;
            padding: 0.56rem 0.62rem;
            font-size: 0.82rem;
        }

        .error ul {
            margin: 0;
            padding-left: 1.1rem;
            display: grid;
            gap: 0.2rem;
        }

        .foot {
            margin-top: 0.72rem;
            color: #8ea8ce;
            font-size: 0.78rem;
            line-height: 1.45;
        }
    </style>
</head>
<body>
<main class="card">
    <h1>Login FinLyzer</h1>
    <p>Masuk dengan email dan password dari database untuk memakai dashboard analisis.</p>

    @if (session('status'))
        <div class="status-ok">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf

        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
        </div>

        <div>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password">
        </div>

        <label class="remember">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            Ingat saya
        </label>

        <div class="actions">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>

    <div class="foot">
        Gunakan akun user yang sudah tersimpan pada tabel users di database FinLyzer.
    </div>
</main>
</body>
</html>
