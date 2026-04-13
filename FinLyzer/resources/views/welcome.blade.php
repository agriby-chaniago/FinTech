<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Financial Analyzer Service') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<<<<<<< HEAD
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b1220;
            --bg-soft: #121c2f;
            --card: #162338;
            --line: #2a3b59;
            --text: #e6eefc;
            --muted: #9bb0d1;
            --accent: #22d3ee;
            --accent-soft: rgba(34, 211, 238, 0.14);
            --ok: #22c55e;
            --warn: #f59e0b;
            --error: #ef4444;
            --shadow: 0 14px 34px rgba(1, 6, 16, 0.42);
=======
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-a: #f4fbff;
            --bg-b: #fff7ec;
            --surface: #ffffff;
            --surface-soft: #f8fcff;
            --line: #d6e8f3;
            --text: #163247;
            --muted: #5d7587;
            --primary: #0f766e;
            --primary-strong: #0b5f59;
            --accent: #f97316;
            --ok: #15803d;
            --info: #0c4a6e;
            --error: #b91c1c;
            --shadow: 0 18px 45px rgba(12, 74, 110, 0.11);
>>>>>>> 95acdce (Fix nested repo issue)
        }

        * {
            box-sizing: border-box;
        }

<<<<<<< HEAD
        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        body {
            font-family: "Manrope", "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(600px 260px at 10% -5%, rgba(34, 211, 238, 0.15), transparent 68%),
                linear-gradient(170deg, var(--bg), #0e1625 60%, #101a2c);
        }

        .container {
            width: min(1160px, calc(100% - 2rem));
            margin: 1.2rem auto 1.8rem;
        }

        .hero {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: linear-gradient(145deg, rgba(22, 35, 56, 0.95), rgba(13, 22, 37, 0.95));
            box-shadow: var(--shadow);
            padding: 1rem 1.1rem;
        }

        .hero .chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid #2b5270;
            border-radius: 999px;
            background: var(--accent-soft);
            color: #92e6f5;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.26rem 0.56rem;
            letter-spacing: 0.03em;
        }

        .hero h1 {
            margin: 0.65rem 0 0;
            font-size: clamp(1.24rem, 2.4vw, 1.9rem);
            line-height: 1.28;
            max-width: 25ch;
        }

        .hero p {
            margin: 0.5rem 0 0;
            color: var(--muted);
            max-width: 76ch;
            font-size: 0.9rem;
            line-height: 1.56;
        }

        .layout {
            margin-top: 0.9rem;
            display: grid;
            grid-template-columns: 330px 1fr;
            gap: 0.8rem;
            align-items: start;
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: rgba(22, 35, 56, 0.94);
            box-shadow: var(--shadow);
            padding: 0.9rem;
        }

        .card h2 {
            margin: 0;
            font-size: 0.98rem;
        }

        .card p {
            margin: 0.38rem 0 0;
            color: var(--muted);
            font-size: 0.84rem;
            line-height: 1.5;
        }

        .controls {
            position: sticky;
            top: 0.7rem;
        }

        .stack {
            margin-top: 0.74rem;
            display: grid;
            gap: 0.56rem;
        }

        label {
            display: block;
            margin-bottom: 0.24rem;
            color: #aac0df;
            font-size: 0.76rem;
            font-weight: 600;
        }

        input {
            width: 100%;
            border: 1px solid #344a6d;
            border-radius: 10px;
            background: #121f33;
            color: var(--text);
            font-family: "IBM Plex Mono", ui-monospace, monospace;
            font-size: 0.81rem;
            padding: 0.58rem 0.62rem;
        }

        input:focus {
            outline: 2px solid rgba(34, 211, 238, 0.22);
            border-color: #2eaac2;
        }

        .switch {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid #334967;
            border-radius: 10px;
            background: #132235;
            color: #abc0df;
            font-size: 0.79rem;
            padding: 0.48rem 0.56rem;
        }

        .switch input {
            width: auto;
            margin: 0;
            accent-color: var(--accent);
        }

        .guide {
            margin: 0;
            padding-left: 1rem;
            display: grid;
            gap: 0.3rem;
            color: #a2b8d8;
            font-size: 0.79rem;
            line-height: 1.48;
        }

        .actions {
            display: grid;
            gap: 0.42rem;
=======
        body {
            margin: 0;
            color: var(--text);
            font-family: "Sora", "Segoe UI", sans-serif;
            background:
                radial-gradient(900px 360px at 12% -8%, rgba(15, 118, 110, 0.13), transparent 60%),
                radial-gradient(700px 300px at 92% 5%, rgba(249, 115, 22, 0.16), transparent 68%),
                linear-gradient(145deg, var(--bg-a), var(--bg-b));
            min-height: 100vh;
        }

        .wrap {
            width: min(1140px, calc(100% - 2rem));
            margin: 1.5rem auto 2.6rem;
        }

        .hero {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: linear-gradient(130deg, rgba(255, 255, 255, 0.96), rgba(255, 255, 255, 0.9));
            box-shadow: var(--shadow);
            padding: 1.4rem 1.4rem 1.3rem;
            animation: rise 420ms ease-out both;
        }

        .hero::after {
            content: "";
            position: absolute;
            right: -60px;
            top: -64px;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.25), rgba(249, 115, 22, 0));
            pointer-events: none;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border: 1px solid #b8e0d6;
            background: #ecfdf5;
            color: var(--primary-strong);
            border-radius: 999px;
            font-size: 0.77rem;
            font-weight: 700;
            padding: 0.33rem 0.72rem;
            letter-spacing: 0.02em;
        }

        .hero h1 {
            margin: 0.72rem 0 0;
            font-size: clamp(1.45rem, 2.8vw, 2.22rem);
            line-height: 1.25;
            max-width: 24ch;
        }

        .hero p {
            margin: 0.7rem 0 0;
            color: var(--muted);
            max-width: 72ch;
            font-size: 0.95rem;
            line-height: 1.55;
        }

        .hero-meta {
            margin-top: 0.9rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .hero-meta span {
            border: 1px solid #d6e8f3;
            background: #f8fcff;
            border-radius: 12px;
            color: #25506d;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.36rem 0.55rem;
        }

        .grid {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: 1.08fr 0.92fr;
            gap: 0.95rem;
        }

        .panel {
            border: 1px solid var(--line);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: var(--shadow);
            padding: 1rem;
            animation: rise 520ms ease-out both;
        }

        .panel:nth-child(2) {
            animation-delay: 80ms;
        }

        .panel h2 {
            margin: 0;
            font-size: 1.02rem;
        }

        .panel p {
            margin: 0.42rem 0 0;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.52;
        }

        .stack {
            margin-top: 0.9rem;
            display: grid;
            gap: 0.64rem;
        }

        .field label {
            display: block;
            margin-bottom: 0.32rem;
            color: #4d6477;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .field input {
            width: 100%;
            border: 1px solid #cadfee;
            border-radius: 12px;
            padding: 0.62rem 0.72rem;
            background: #ffffff;
            color: var(--text);
            font: 600 0.88rem "JetBrains Mono", ui-monospace, monospace;
        }

        .field input:focus {
            outline: 2px solid rgba(15, 118, 110, 0.2);
            border-color: #86cdbf;
        }

        .hint {
            margin: 0;
            border: 1px dashed #cbe3f0;
            border-radius: 12px;
            background: #f7fcff;
            color: #3f5f76;
            font-size: 0.82rem;
            line-height: 1.5;
            padding: 0.62rem 0.67rem;
        }

        .actions {
            margin-top: 0.75rem;
            display: flex;
            gap: 0.55rem;
            flex-wrap: wrap;
        }

        .actions.compact {
            margin-top: 0.45rem;
>>>>>>> 95acdce (Fix nested repo issue)
        }

        .btn {
            border: 0;
<<<<<<< HEAD
            border-radius: 10px;
            padding: 0.56rem 0.66rem;
            font-family: inherit;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 120ms ease, opacity 120ms ease;
        }

        .btn:disabled {
            opacity: 0.62;
=======
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.86rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            padding: 0.62rem 0.85rem;
            cursor: pointer;
            transition: transform 130ms ease, box-shadow 130ms ease, opacity 130ms ease;
        }

        .btn:disabled {
            opacity: 0.64;
>>>>>>> 95acdce (Fix nested repo issue)
            cursor: not-allowed;
        }

        .btn-primary {
<<<<<<< HEAD
            background: linear-gradient(120deg, #22d3ee, #06b6d4);
            color: #042028;
=======
            background: linear-gradient(130deg, var(--primary), var(--primary-strong));
            color: #ffffff;
            box-shadow: 0 12px 24px rgba(11, 95, 89, 0.26);
>>>>>>> 95acdce (Fix nested repo issue)
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-1px);
        }

        .btn-soft {
<<<<<<< HEAD
            background: #1a2b45;
            border: 1px solid #355274;
            color: #c2d7f3;
        }

        .status {
            border-radius: 10px;
            border: 1px solid transparent;
            padding: 0.52rem 0.58rem;
            font-size: 0.79rem;
            font-weight: 600;
            line-height: 1.45;
        }

        .status.info {
            color: #98dff5;
            background: rgba(56, 189, 248, 0.11);
            border-color: rgba(56, 189, 248, 0.3);
        }

        .status.ok {
            color: #b7f7cb;
            background: rgba(34, 197, 94, 0.12);
            border-color: rgba(34, 197, 94, 0.35);
        }

        .status.error {
            color: #fecaca;
            background: rgba(239, 68, 68, 0.12);
            border-color: rgba(239, 68, 68, 0.35);
        }

        .summary {
            margin-top: 0.72rem;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.45rem;
        }

        .metric {
            border: 1px solid #354f72;
            border-radius: 11px;
            background: #142338;
            padding: 0.5rem;
            min-height: 66px;
        }

        .metric small {
            display: block;
            color: #96aed0;
            font-size: 0.71rem;
            margin-bottom: 0.2rem;
        }

        .metric strong {
            font-size: 1rem;
            color: #e6eefc;
        }

        .result-layout {
            margin-top: 0.72rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.6rem;
        }

        .table-wrap {
            border: 1px solid #334d70;
            border-radius: 12px;
            overflow: hidden;
            background: #132235;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.79rem;
        }

        thead th {
            text-align: left;
            color: #a8c1e2;
            font-weight: 700;
            padding: 0.52rem 0.58rem;
            border-bottom: 1px solid #314a6d;
            background: #162942;
        }

        tbody td {
            padding: 0.5rem 0.58rem;
            border-top: 1px solid #2a425f;
            color: #d7e6fb;
        }

        tbody tr {
            cursor: pointer;
        }

        tbody tr:hover {
            background: #1a2d49;
        }

        tbody tr.active {
            background: #18324d;
            box-shadow: inset 3px 0 0 #22d3ee;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            font-size: 0.67rem;
            font-weight: 700;
            padding: 0.17rem 0.42rem;
            letter-spacing: 0.03em;
        }

        .badge.ok {
            color: #b7f7cb;
            background: rgba(34, 197, 94, 0.2);
        }

        .badge.empty {
            color: #ffe0a5;
            background: rgba(245, 158, 11, 0.22);
        }

        .badge.error {
            color: #fecaca;
            background: rgba(239, 68, 68, 0.22);
        }

        .detail {
            border: 1px solid #334d70;
            border-radius: 12px;
            background: #132235;
            padding: 0.66rem;
        }

        .detail-head {
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
            align-items: center;
        }

        .detail-head h3 {
            margin: 0;
            font-size: 0.88rem;
            color: #d1e3fb;
        }

        .detail-grid {
            margin-top: 0.56rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.42rem;
        }

        .cell {
            border: 1px solid #334d70;
            border-radius: 10px;
            background: #162942;
            padding: 0.45rem;
            min-height: 58px;
        }

        .cell small {
            display: block;
            color: #91abcf;
            font-size: 0.69rem;
            margin-bottom: 0.2rem;
        }

        .cell strong {
            color: #e4efff;
            font-size: 0.85rem;
            line-height: 1.45;
            word-break: break-word;
        }

        .title {
            margin: 0.62rem 0 0.36rem;
            font-size: 0.77rem;
            color: #b1cbeb;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .summary-box,
        .insight-box {
            border: 1px solid #334d70;
            border-radius: 10px;
            background: #162942;
            color: #d6e6fb;
            font-size: 0.82rem;
            line-height: 1.55;
            min-height: 62px;
            padding: 0.5rem 0.56rem;
=======
            background: #edf6ff;
            border: 1px solid #cde0f2;
            color: #2f5677;
        }

        .status {
            margin-top: 0.74rem;
            border-radius: 12px;
            border: 1px solid transparent;
            padding: 0.62rem 0.7rem;
            font-size: 0.84rem;
            font-weight: 600;
        }

        .status.info {
            color: var(--info);
            background: #e8f4fb;
            border-color: #b8dbef;
        }

        .status.ok {
            color: var(--ok);
            background: #ecfdf3;
            border-color: #bce8cb;
        }

        .status.error {
            color: var(--error);
            background: #fff0f0;
            border-color: #f5caca;
        }

        .steps {
            margin: 0.78rem 0 0;
            display: grid;
            gap: 0.46rem;
        }

        .step {
            border: 1px solid #d8e8f4;
            border-radius: 12px;
            background: var(--surface-soft);
            padding: 0.55rem 0.64rem;
            font-size: 0.82rem;
            color: #44627a;
            line-height: 1.5;
        }

        .step strong {
            color: #19445f;
        }

        .kv {
            margin-top: 0.86rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.52rem;
        }

        .kcard {
            border: 1px solid #d7e7f3;
            border-radius: 12px;
            background: #fbfeff;
            padding: 0.55rem;
            min-height: 68px;
        }

        .kcard small {
            display: block;
            color: #6d8091;
            font-size: 0.72rem;
            margin-bottom: 0.24rem;
        }

        .kcard strong {
            font-size: 0.98rem;
            color: #1c4661;
            word-break: break-word;
            line-height: 1.4;
        }

        .micro-note {
            margin: 0;
            color: #5d768a;
            font-size: 0.79rem;
            line-height: 1.5;
        }

        .section-title {
            margin: 0.88rem 0 0.48rem;
            font-size: 0.86rem;
            font-weight: 800;
            color: #274c66;
            letter-spacing: 0.01em;
>>>>>>> 95acdce (Fix nested repo issue)
        }

        .breakdown {
            display: grid;
<<<<<<< HEAD
            gap: 0.32rem;
        }

        .bar-item {
            border: 1px solid #334d70;
            border-radius: 10px;
            background: #162942;
            padding: 0.4rem;
=======
            gap: 0.44rem;
        }

        .bar-item {
            border: 1px solid #d5e6f2;
            border-radius: 11px;
            background: #fbfdff;
            padding: 0.45rem;
>>>>>>> 95acdce (Fix nested repo issue)
        }

        .bar-head {
            display: flex;
            justify-content: space-between;
<<<<<<< HEAD
            gap: 0.3rem;
            color: #a6c0df;
            font-size: 0.72rem;
            margin-bottom: 0.24rem;
        }

        .bar-track {
            height: 7px;
            border-radius: 999px;
            background: #233a5c;
=======
            font-size: 0.78rem;
            color: #4f6980;
            margin-bottom: 0.3rem;
            gap: 0.34rem;
        }

        .bar-track {
            height: 8px;
            border-radius: 999px;
            background: #dceaf5;
>>>>>>> 95acdce (Fix nested repo issue)
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            border-radius: 999px;
<<<<<<< HEAD
            background: linear-gradient(90deg, #22d3ee, #60a5fa);
        }

        .payload-actions {
            margin-top: 0.42rem;
            display: flex;
            justify-content: flex-end;
        }

        .json {
            margin-top: 0.42rem;
            border: 1px solid #294160;
            border-radius: 10px;
            background: #0d182b;
            max-height: 220px;
            overflow: auto;
=======
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .insight {
            border: 1px solid #f6d8b4;
            background: #fff8ef;
            color: #7f5222;
            border-radius: 12px;
            padding: 0.66rem 0.72rem;
            min-height: 70px;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        .json-box {
            margin-top: 0.55rem;
            border: 1px solid #152636;
            border-radius: 12px;
            background: linear-gradient(180deg, #132739, #0f1f2d);
            overflow: auto;
            max-height: 270px;
>>>>>>> 95acdce (Fix nested repo issue)
        }

        pre {
            margin: 0;
<<<<<<< HEAD
            color: #c8dcf7;
            line-height: 1.45;
            font-size: 0.74rem;
            padding: 0.56rem;
            white-space: pre-wrap;
            word-break: break-word;
            font-family: "IBM Plex Mono", ui-monospace, monospace;
        }

        @media (max-width: 1040px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .controls {
                position: static;
            }

            .summary {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 720px) {
            .container {
                width: calc(100% - 1rem);
                margin: 0.8rem auto 1.2rem;
            }

            .summary,
            .detail-grid {
                grid-template-columns: 1fr;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead {
                display: none;
            }

            tbody tr {
                border-top: 1px solid #2a425f;
                padding: 0.4rem 0;
            }

            tbody td {
                border-top: 0;
                padding: 0.2rem 0.58rem;
            }

            tbody td::before {
                content: attr(data-label) ': ';
                color: #93add1;
                font-weight: 700;
            }
=======
            color: #daf0ff;
            font-size: 0.79rem;
            line-height: 1.52;
            padding: 0.72rem;
            white-space: pre-wrap;
            word-break: break-word;
            font-family: "JetBrains Mono", ui-monospace, monospace;
        }

        @keyframes rise {
            from {
                transform: translateY(8px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 980px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .wrap {
                width: calc(100% - 1rem);
                margin: 1rem auto 1.4rem;
            }

            .hero {
                border-radius: 17px;
                padding: 1rem;
            }

            .panel {
                border-radius: 17px;
                padding: 0.86rem;
            }

            .actions .btn {
                width: 100%;
            }

            .kv {
                grid-template-columns: 1fr;
            }
>>>>>>> 95acdce (Fix nested repo issue)
        }
    </style>
</head>
<body>
<<<<<<< HEAD
<div class="container">
    <header class="hero">
        <span class="chip">AUTO · MULTI USER</span>
        <h1>Dashboard Analisis Otomatis yang Lebih Ringkas</h1>
        <p>
            Jalankan analisis untuk banyak user sekaligus. Tampilan disederhanakan agar fokus ke hasil penting:
            source sync, metrik, ringkasan, insight AI, dan payload untuk Service C.
        </p>
    </header>

    <main class="layout">
        <section class="card controls">
            <h2>Kontrol</h2>
            <p>Isi data minimal lalu jalankan batch.</p>

            <div class="stack">
                <div>
=======
<div class="wrap">
    <header class="hero">
        <span class="badge">AUTO MODE ONLY</span>
        <h1>Financial Analyzer AI - Sinkronisasi Otomatis Tanpa Input Manual</h1>
        <p>
            Halaman ini hanya untuk jalur otomatis. Data transaksi akan diambil dari feed Service 1,
            diproses analisis AI, lalu hasilnya langsung tampil di dashboard ini.
        </p>
        <div class="hero-meta">
            <span>Endpoint: POST /api/analyze/auto/run</span>
            <span>Service C Pull: GET /api/analyze/auto/latest</span>
            <span>Mode: API key only</span>
            <span>No manual transaction form</span>
        </div>
    </header>

    <main class="grid">
        <section class="panel">
            <h2>Jalankan Otomatis</h2>
            <p>Tekan tombol sekali. Sistem akan pakai default user + since tersimpan secara otomatis.</p>

            <div class="stack">
                <div class="field">
>>>>>>> 95acdce (Fix nested repo issue)
                    <label for="apiKey">API Key</label>
                    <input id="apiKey" type="text" value="{{ (string) config('services.analyzer.api_key', '') }}" placeholder="Contoh: fintrack1">
                </div>

<<<<<<< HEAD
                <div>
                    <label for="userIds">User IDs (pisahkan koma)</label>
                    <input id="userIds" type="text" value="2" placeholder="Contoh: 2,3,5">
                </div>

                <div>
                    <label for="sinceOverride">Since Override (opsional)</label>
                    <input id="sinceOverride" type="text" placeholder="Contoh: 2026-04-13T07:48:43+00:00">
                </div>

                <label class="switch">
                    <input id="includeSummary" type="checkbox">
                    Include summary dari feed
                </label>

                <label class="switch">
                    <input id="useSavedSince" type="checkbox" checked>
                    Gunakan saved since
                </label>

                <ol class="guide">
                    <li>Isi API key dan user IDs.</li>
                    <li>Klik Run Batch Multi User.</li>
                    <li>Pilih user pada tabel hasil untuk lihat detail.</li>
                </ol>

                <div class="actions">
                    <button id="runBatchBtn" type="button" class="btn btn-primary">Run Batch Multi User</button>
                    <button id="runDefaultBtn" type="button" class="btn btn-soft">Run Default User</button>
                    <button id="clearBtn" type="button" class="btn btn-soft">Clear</button>
                </div>

                <div id="status" class="status info">Siap dipakai.</div>
            </div>
        </section>

        <section class="card">
            <h2>Hasil</h2>
            <p>Ringkasan batch di atas, detail user terpilih di bawah.</p>

            <div class="summary">
                <div class="metric">
                    <small>Total User</small>
                    <strong id="sumUsers">0</strong>
                </div>
                <div class="metric">
                    <small>Berhasil</small>
                    <strong id="sumSuccess">0</strong>
                </div>
                <div class="metric">
                    <small>Gagal</small>
                    <strong id="sumFailed">0</strong>
                </div>
                <div class="metric">
                    <small>Total Fetched</small>
                    <strong id="sumFetched">0</strong>
                </div>
            </div>

            <div class="result-layout">
                <section class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Status</th>
                                <th>Fetched</th>
                                <th>Top Category</th>
                            </tr>
                        </thead>
                        <tbody id="userRows">
                            <tr>
                                <td colspan="4" style="color:#9ab0d1;">Belum ada hasil.</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section class="detail">
                    <div class="detail-head">
                        <h3>Detail User Terpilih</h3>
                        <span id="detailBadge" class="badge empty">Belum dipilih</span>
                    </div>

                    <div class="detail-grid">
                        <div class="cell">
                            <small>User ID</small>
                            <strong id="dUser">-</strong>
                        </div>
                        <div class="cell">
                            <small>Status</small>
                            <strong id="dStatus">-</strong>
                        </div>
                        <div class="cell">
                            <small>Fetched Transactions</small>
                            <strong id="dFetched">-</strong>
                        </div>
                        <div class="cell">
                            <small>Since Source</small>
                            <strong id="dSinceSource">-</strong>
                        </div>
                        <div class="cell">
                            <small>Next Since</small>
                            <strong id="dNextSince">-</strong>
                        </div>
                        <div class="cell">
                            <small>Executed At</small>
                            <strong id="dExecutedAt">-</strong>
                        </div>
                    </div>

                    <div class="title">Metrik</div>
                    <div class="detail-grid">
                        <div class="cell">
                            <small>Total Income</small>
                            <strong id="mIncome">-</strong>
                        </div>
                        <div class="cell">
                            <small>Total Expense</small>
                            <strong id="mExpense">-</strong>
                        </div>
                        <div class="cell">
                            <small>Transaction Count</small>
                            <strong id="mCount">-</strong>
                        </div>
                        <div class="cell">
                            <small>Top Category</small>
                            <strong id="mTop">-</strong>
                        </div>
                        <div class="cell">
                            <small>Arus Kas Bersih</small>
                            <strong id="mNet">-</strong>
                        </div>
                        <div class="cell">
                            <small>Rasio Tabungan</small>
                            <strong id="mSavings">-</strong>
                        </div>
                    </div>

                    <div class="title">Status Keuangan</div>
                    <div class="summary-box" id="mHealth">-</div>

                    <div class="title">Ringkasan Analisis</div>
                    <div class="summary-box" id="analysisSummary">Belum ada ringkasan.</div>

                    <div class="title">Insight AI</div>
                    <div class="insight-box" id="insightText">Belum ada insight.</div>

                    <div class="title">Breakdown Kategori</div>
                    <div id="breakdown" class="breakdown">
                        <div class="bar-item">
                            <div class="bar-head"><span>Belum ada data</span><span>0%</span></div>
                            <div class="bar-track"><div class="bar-fill" style="width:0%"></div></div>
                        </div>
                    </div>

                    <div class="title">Payload Service C</div>
                    <div class="payload-actions">
                        <button id="copyPayloadBtn" type="button" class="btn btn-soft">Copy Payload</button>
                    </div>
                    <div class="json">
                        <pre id="payloadOutput">Belum ada payload.</pre>
                    </div>
                </section>
=======
                <p class="hint">
                    Tidak ada opsi manual di UI ini. Jika Service 1 belum aktif atau endpoint feed belum tersedia,
                    hasil akan langsung menampilkan error upstream secara jelas.
                </p>
            </div>

            <div class="actions">
                <button type="button" class="btn btn-primary" id="runBtn">Run Automatic Analysis</button>
                <button type="button" class="btn btn-soft" id="clearBtn">Clear Output</button>
            </div>

            <div id="status" class="status info">Siap menjalankan sinkronisasi otomatis.</div>

            <div class="steps">
                <div class="step"><strong>Step 1:</strong> Service 1 harus aktif (feed endpoint ready).</div>
                <div class="step"><strong>Step 2:</strong> Klik Run Automatic Analysis.</div>
                <div class="step"><strong>Step 3:</strong> Dashboard menampilkan source sync, metrik, dan insight AI.</div>
            </div>
        </section>

        <section class="panel">
            <h2>Live Analysis Output</h2>
            <p>Menampilkan hasil run terbaru dari mode otomatis.</p>

            <div class="section-title">Source Sync</div>
            <div class="kv">
                <div class="kcard">
                    <small>User ID</small>
                    <strong id="sUser">-</strong>
                </div>
                <div class="kcard">
                    <small>Fetched Transactions</small>
                    <strong id="sFetched">-</strong>
                </div>
                <div class="kcard">
                    <small>Since Source</small>
                    <strong id="sSinceSource">-</strong>
                </div>
                <div class="kcard">
                    <small>Next Since</small>
                    <strong id="sNextSince">-</strong>
                </div>
                <div class="kcard">
                    <small>Executed At</small>
                    <strong id="sExecutedAt">-</strong>
                </div>
            </div>

            <div class="section-title">Metrics</div>
            <div class="kv">
                <div class="kcard">
                    <small>Total Income</small>
                    <strong id="mIncome">-</strong>
                </div>
                <div class="kcard">
                    <small>Total Expense</small>
                    <strong id="mExpense">-</strong>
                </div>
                <div class="kcard">
                    <small>Transaction Count</small>
                    <strong id="mCount">-</strong>
                </div>
                <div class="kcard">
                    <small>Top Category</small>
                    <strong id="mTop">-</strong>
                </div>
            </div>

            <div class="section-title">Category Breakdown</div>
            <div id="breakdown" class="breakdown">
                <div class="bar-item">
                    <div class="bar-head"><span>Belum ada data</span><span>0%</span></div>
                    <div class="bar-track"><div class="bar-fill" style="width: 0%"></div></div>
                </div>
            </div>

            <div class="section-title">AI Insight</div>
            <div id="insight" class="insight">Belum ada insight.</div>

            <div class="section-title">Raw JSON</div>
            <div class="json-box">
                <pre id="jsonOutput">Belum ada response.</pre>
            </div>

            <div class="section-title">Payload Siap Ambil untuk Service C</div>
            <p class="micro-note">Payload ini menormalisasi hasil run terbaru. Service C dapat pull via endpoint GET /api/analyze/auto/latest?user_id=...</p>
            <div class="actions compact">
                <button type="button" class="btn btn-soft" id="copyServiceCPayloadBtn">Copy Payload Service C</button>
            </div>
            <div class="json-box">
                <pre id="serviceCPayloadOutput">Belum ada payload Service C.</pre>
>>>>>>> 95acdce (Fix nested repo issue)
            </div>
        </section>
    </main>
</div>

<script>
<<<<<<< HEAD
    const autoEndpoint = @json(url('/api/analyze/auto'));
    const autoRunEndpoint = @json(url('/api/analyze/auto/run'));

    const apiKeyInput = document.getElementById('apiKey');
    const userIdsInput = document.getElementById('userIds');
    const sinceOverrideInput = document.getElementById('sinceOverride');
    const includeSummaryInput = document.getElementById('includeSummary');
    const useSavedSinceInput = document.getElementById('useSavedSince');

    const runBatchBtn = document.getElementById('runBatchBtn');
    const runDefaultBtn = document.getElementById('runDefaultBtn');
    const clearBtn = document.getElementById('clearBtn');
    const copyPayloadBtn = document.getElementById('copyPayloadBtn');

    const statusEl = document.getElementById('status');

    const sumUsers = document.getElementById('sumUsers');
    const sumSuccess = document.getElementById('sumSuccess');
    const sumFailed = document.getElementById('sumFailed');
    const sumFetched = document.getElementById('sumFetched');

    const userRows = document.getElementById('userRows');

    const detailBadge = document.getElementById('detailBadge');
    const dUser = document.getElementById('dUser');
    const dStatus = document.getElementById('dStatus');
    const dFetched = document.getElementById('dFetched');
    const dSinceSource = document.getElementById('dSinceSource');
    const dNextSince = document.getElementById('dNextSince');
    const dExecutedAt = document.getElementById('dExecutedAt');
=======
    const autoRunEndpoint = @json(url('/api/analyze/auto/run'));

    const apiKeyInput = document.getElementById('apiKey');
    const runBtn = document.getElementById('runBtn');
    const clearBtn = document.getElementById('clearBtn');
    const copyServiceCPayloadBtn = document.getElementById('copyServiceCPayloadBtn');
    const statusEl = document.getElementById('status');
    const jsonOutput = document.getElementById('jsonOutput');
    const serviceCPayloadOutput = document.getElementById('serviceCPayloadOutput');

    const sUser = document.getElementById('sUser');
    const sFetched = document.getElementById('sFetched');
    const sSinceSource = document.getElementById('sSinceSource');
    const sNextSince = document.getElementById('sNextSince');
    const sExecutedAt = document.getElementById('sExecutedAt');
>>>>>>> 95acdce (Fix nested repo issue)

    const mIncome = document.getElementById('mIncome');
    const mExpense = document.getElementById('mExpense');
    const mCount = document.getElementById('mCount');
    const mTop = document.getElementById('mTop');
<<<<<<< HEAD
    const mNet = document.getElementById('mNet');
    const mSavings = document.getElementById('mSavings');
    const mHealth = document.getElementById('mHealth');

    const analysisSummaryEl = document.getElementById('analysisSummary');
    const insightText = document.getElementById('insightText');
    const breakdownEl = document.getElementById('breakdown');
    const payloadOutput = document.getElementById('payloadOutput');

    let runResults = [];
    let selectedUserId = null;
    let selectedPayload = null;
=======
    const insightEl = document.getElementById('insight');
    const breakdownEl = document.getElementById('breakdown');
    let latestServiceCPayload = null;
>>>>>>> 95acdce (Fix nested repo issue)

    function setStatus(message, type) {
        statusEl.className = 'status ' + type;
        statusEl.textContent = message;
    }

    function setLoading(state) {
<<<<<<< HEAD
        runBatchBtn.disabled = state;
        runDefaultBtn.disabled = state;
        runBatchBtn.textContent = state ? 'Running Batch...' : 'Run Batch Multi User';
        runDefaultBtn.textContent = state ? 'Running...' : 'Run Default User';
    }

    function parseUserIds(raw) {
        return Array.from(
            new Set(
                String(raw || '')
                    .split(/[\s,;]+/)
                    .map((value) => Number(value.trim()))
                    .filter((value) => Number.isInteger(value) && value > 0)
            )
        );
    }

    function formatMoney(value) {
        const number = Number(value);

        if (Number.isNaN(number)) {
=======
        runBtn.disabled = state;
        runBtn.textContent = state ? 'Running...' : 'Run Automatic Analysis';
    }

    function formatMoney(value) {
        const amount = Number(value);

        if (Number.isNaN(amount)) {
>>>>>>> 95acdce (Fix nested repo issue)
            return '-';
        }

        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
<<<<<<< HEAD
        }).format(number);
    }

    function formatDateTime(value) {
        if (!value) {
            return '-';
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return String(value);
        }

        return new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'medium',
            timeStyle: 'medium',
        }).format(date);
    }

    function normalizeStatus(item) {
        if (item.status === 'ok') {
            return 'Berhasil';
        }

        if (item.status === 'empty') {
            return 'Tidak ada data';
        }

        return 'Gagal';
    }

    function badgeClass(status) {
        if (status === 'ok') {
            return 'ok';
        }

        if (status === 'empty') {
            return 'empty';
        }

        return 'error';
    }

    function renderSummary() {
        const totalUsers = runResults.length;
        const successUsers = runResults.filter((item) => item.status === 'ok' || item.status === 'empty').length;
        const failedUsers = runResults.filter((item) => item.status === 'error').length;
        const fetchedTotal = runResults.reduce((total, item) => total + Number(item.source?.fetched_transactions || 0), 0);

        sumUsers.textContent = String(totalUsers);
        sumSuccess.textContent = String(successUsers);
        sumFailed.textContent = String(failedUsers);
        sumFetched.textContent = String(fetchedTotal);
    }

    function renderUserRows() {
        if (!runResults.length) {
            userRows.innerHTML = '<tr><td colspan="4" style="color:#9ab0d1;" data-label="Info">Belum ada hasil.</td></tr>';
            return;
        }

        userRows.innerHTML = runResults
            .map((item) => {
                const activeClass = item.userId === selectedUserId ? 'active' : '';
                const badge = '<span class="badge ' + badgeClass(item.status) + '">' + normalizeStatus(item) + '</span>';
                const fetched = Number(item.source?.fetched_transactions || 0);
                const top = item.analysis?.top_category || '-';

                return '' +
                    '<tr class="' + activeClass + '" data-user-id="' + item.userId + '">' +
                        '<td data-label="User">' + item.userId + '</td>' +
                        '<td data-label="Status">' + badge + '</td>' +
                        '<td data-label="Fetched">' + fetched + '</td>' +
                        '<td data-label="Top Category">' + top + '</td>' +
                    '</tr>';
            })
            .join('');

        userRows.querySelectorAll('tr[data-user-id]').forEach((row) => {
            row.addEventListener('click', () => {
                selectedUserId = Number(row.getAttribute('data-user-id'));
                renderUserRows();
                renderDetails();
            });
        });
=======
        }).format(amount);
>>>>>>> 95acdce (Fix nested repo issue)
    }

    function renderBreakdown(breakdown) {
        const entries = Object.entries(breakdown || {});

        if (!entries.length) {
            breakdownEl.innerHTML = '<div class="bar-item"><div class="bar-head"><span>Belum ada data</span><span>0%</span></div><div class="bar-track"><div class="bar-fill" style="width:0%"></div></div></div>';
            return;
        }

        breakdownEl.innerHTML = entries
            .sort((a, b) => Number(b[1]) - Number(a[1]))
            .map(([category, percent]) => {
                const value = Number(percent) || 0;
                const width = Math.max(0, Math.min(100, value));

                return '' +
                    '<div class="bar-item">' +
                        '<div class="bar-head"><span>' + String(category) + '</span><span>' + value.toFixed(2) + '%</span></div>' +
                        '<div class="bar-track"><div class="bar-fill" style="width:' + width.toFixed(2) + '%"></div></div>' +
                    '</div>';
            })
            .join('');
    }

<<<<<<< HEAD
    function buildServiceCPayload(item) {
        const source = item.source || {};
        const analysis = item.analysis || null;
=======
    function renderSource(source, executedAt) {
        sUser.textContent = source && source.user_id != null ? String(source.user_id) : '-';
        sFetched.textContent = source && source.fetched_transactions != null ? String(source.fetched_transactions) : '-';
        sSinceSource.textContent = source && source.since_source ? String(source.since_source) : '-';
        sNextSince.textContent = source && source.next_since ? String(source.next_since) : '-';
        sExecutedAt.textContent = executedAt || '-';
    }

    function buildServiceCPayload(data, executedAt) {
        const source = data && data.source ? data.source : {};
        const analysis = data && data.analysis ? data.analysis : null;
>>>>>>> 95acdce (Fix nested repo issue)

        if (!analysis) {
            return {
                schema_version: 'service-c.v1',
<<<<<<< HEAD
                status: item.status,
                executed_at: item.executedAt,
                source_sync: {
                    user_id: item.userId,
                    fetched_transactions: Number(source.fetched_transactions || 0),
                    since_source: source.since_source || null,
                    next_since: source.next_since || null,
                },
                message: item.message || 'Tidak ada transaksi baru.',
=======
                status: 'no_new_transactions',
                executed_at: executedAt,
                message: data && data.message ? String(data.message) : 'Tidak ada transaksi baru.',
                source_sync: {
                    user_id: source && source.user_id != null ? Number(source.user_id) : null,
                    fetched_transactions: source && source.fetched_transactions != null ? Number(source.fetched_transactions) : 0,
                    since_source: source && source.since_source ? String(source.since_source) : null,
                    next_since: source && source.next_since ? String(source.next_since) : null,
                },
>>>>>>> 95acdce (Fix nested repo issue)
                metrics: null,
                ai_insight: null,
                category_breakdown: [],
            };
        }

        return {
            schema_version: 'service-c.v1',
<<<<<<< HEAD
            status: item.status,
            executed_at: item.executedAt,
            source_sync: {
                user_id: item.userId,
                fetched_transactions: Number(source.fetched_transactions || 0),
                since_source: source.since_source || null,
                next_since: source.next_since || null,
=======
            status: 'ready',
            executed_at: executedAt,
            source_sync: {
                user_id: source && source.user_id != null ? Number(source.user_id) : null,
                fetched_transactions: source && source.fetched_transactions != null ? Number(source.fetched_transactions) : Number(analysis.transaction_count || 0),
                since_source: source && source.since_source ? String(source.since_source) : null,
                next_since: source && source.next_since ? String(source.next_since) : null,
>>>>>>> 95acdce (Fix nested repo issue)
            },
            metrics: {
                total_income: Number(analysis.total_income || 0),
                total_expense: Number(analysis.total_expense || 0),
                transaction_count: Number(analysis.transaction_count || 0),
<<<<<<< HEAD
                top_category: analysis.top_category || null,
                net_balance: Number(analysis.net_balance || 0),
                savings_rate: Number(analysis.savings_rate || 0),
                financial_health: analysis.financial_health || null,
                summary: analysis.summary || null,
            },
            ai_insight: analysis.insight || null,
            category_breakdown: Object.entries(analysis.category_breakdown || {}).map(([category, percentage]) => ({
                category: String(category),
                percentage: Number(percentage || 0),
            })),
        };
    }

    function renderDetails() {
        const selected = runResults.find((item) => item.userId === selectedUserId) || null;

        if (!selected) {
            detailBadge.className = 'badge empty';
            detailBadge.textContent = 'Belum dipilih';

            dUser.textContent = '-';
            dStatus.textContent = '-';
            dFetched.textContent = '-';
            dSinceSource.textContent = '-';
            dNextSince.textContent = '-';
            dExecutedAt.textContent = '-';

            mIncome.textContent = '-';
            mExpense.textContent = '-';
            mCount.textContent = '-';
            mTop.textContent = '-';
            mNet.textContent = '-';
            mSavings.textContent = '-';
            mHealth.textContent = '-';

            analysisSummaryEl.textContent = 'Belum ada ringkasan.';
            insightText.textContent = 'Belum ada insight.';
            renderBreakdown({});
            selectedPayload = null;
            payloadOutput.textContent = 'Belum ada payload.';
            return;
        }

        const analysis = selected.analysis;
        const source = selected.source || {};

        detailBadge.className = 'badge ' + badgeClass(selected.status);
        detailBadge.textContent = normalizeStatus(selected);

        dUser.textContent = String(selected.userId);
        dStatus.textContent = normalizeStatus(selected);
        dFetched.textContent = String(Number(source.fetched_transactions || 0));
        dSinceSource.textContent = source.since_source ? String(source.since_source) : '-';
        dNextSince.textContent = source.next_since ? String(source.next_since) : '-';
        dExecutedAt.textContent = formatDateTime(selected.executedAt);

        if (analysis) {
            const netBalance = analysis.net_balance != null
                ? Number(analysis.net_balance)
                : Number(analysis.total_income || 0) - Number(analysis.total_expense || 0);
            const savingsRate = analysis.savings_rate != null
                ? Number(analysis.savings_rate)
                : (Number(analysis.total_income || 0) > 0
                    ? (netBalance / Number(analysis.total_income || 0)) * 100
                    : 0);

            mIncome.textContent = formatMoney(analysis.total_income);
            mExpense.textContent = formatMoney(analysis.total_expense);
            mCount.textContent = String(analysis.transaction_count ?? '-');
            mTop.textContent = analysis.top_category ? String(analysis.top_category) : '-';
            mNet.textContent = formatMoney(netBalance);
            mSavings.textContent = Number.isFinite(savingsRate) ? savingsRate.toFixed(2) + '%' : '-';
            mHealth.textContent = analysis.financial_health ? String(analysis.financial_health) : '-';

            analysisSummaryEl.textContent = analysis.summary
                ? String(analysis.summary)
                : 'Analisis selesai. Perhatikan arus kas bersih, rasio tabungan, dan kategori pengeluaran terbesar.';

            insightText.textContent = analysis.insight
                ? String(analysis.insight)
                : 'Insight tidak tersedia.';

            renderBreakdown(analysis.category_breakdown || {});
        } else {
            mIncome.textContent = '-';
            mExpense.textContent = '-';
            mCount.textContent = '-';
            mTop.textContent = '-';
            mNet.textContent = '-';
            mSavings.textContent = '-';
            mHealth.textContent = '-';

            analysisSummaryEl.textContent = selected.message
                ? String(selected.message)
                : 'Tidak ada ringkasan karena tidak ada transaksi baru.';

            insightText.textContent = selected.message
                ? String(selected.message)
                : 'Tidak ada insight baru.';

            renderBreakdown({});
        }

        selectedPayload = buildServiceCPayload(selected);
        payloadOutput.textContent = JSON.stringify(selectedPayload, null, 2);
    }

    function clearDashboard(showStatus = true) {
        runResults = [];
        selectedUserId = null;
        selectedPayload = null;

        renderSummary();
        renderUserRows();
        renderDetails();

        if (showStatus) {
            setStatus('Dashboard dibersihkan.', 'info');
        }
    }

    async function requestAutoForUser(apiKey, userId) {
        const payload = {
            user_id: userId,
            include_summary: includeSummaryInput.checked,
            use_saved_since: useSavedSinceInput.checked,
        };

        const sinceValue = String(sinceOverrideInput.value || '').trim();

        if (sinceValue !== '') {
            payload.since = sinceValue;
        }

        const response = await fetch(autoEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'x-api-key': apiKey,
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({
            message: 'Response bukan JSON valid.',
        }));

        if (!response.ok) {
            throw new Error(String(data.message || ('Request gagal dengan status ' + response.status)));
        }

        return {
            userId,
            status: data.analysis ? 'ok' : 'empty',
            executedAt: new Date().toISOString(),
            message: data.message || null,
            source: data.source || null,
            analysis: data.analysis || null,
        };
    }

    async function runBatch() {
        const apiKey = String(apiKeyInput.value || '').trim();

        if (!apiKey) {
            setStatus('API key wajib diisi.', 'error');
            return;
        }

        const userIds = parseUserIds(userIdsInput.value);

        if (!userIds.length) {
            setStatus('Isi minimal satu user ID valid. Contoh: 2,3,5', 'error');
            return;
        }

        setLoading(true);
        setStatus('Menjalankan batch untuk ' + userIds.length + ' user...', 'info');

        const results = [];

        for (const userId of userIds) {
            try {
                const result = await requestAutoForUser(apiKey, userId);
                results.push(result);
            } catch (error) {
                results.push({
                    userId,
                    status: 'error',
                    executedAt: new Date().toISOString(),
                    message: String(error.message || 'Gagal memproses user.'),
                    source: null,
                    analysis: null,
                });
            }
        }

        runResults = results;
        selectedUserId = results[0]?.userId || null;

        renderSummary();
        renderUserRows();
        renderDetails();

        const failedCount = results.filter((item) => item.status === 'error').length;

        if (failedCount > 0) {
            setStatus('Batch selesai dengan beberapa gagal. Periksa user yang berstatus gagal.', 'error');
        } else {
            setStatus('Batch selesai. Semua user berhasil diproses.', 'ok');
        }

        setLoading(false);
    }

    async function runDefaultUser() {
=======
                top_category: analysis.top_category ? String(analysis.top_category) : null,
            },
            ai_insight: analysis.insight ? String(analysis.insight) : null,
            category_breakdown: Object.entries(analysis.category_breakdown || {})
                .map(([category, percentage]) => ({
                    category: String(category),
                    percentage: Number(percentage || 0),
                })),
        };
    }

    function renderServiceCPayload(data, executedAt) {
        latestServiceCPayload = buildServiceCPayload(data, executedAt);
        serviceCPayloadOutput.textContent = JSON.stringify(latestServiceCPayload, null, 2);
    }

    function renderAnalysis(analysis) {
        mIncome.textContent = formatMoney(analysis.total_income);
        mExpense.textContent = formatMoney(analysis.total_expense);
        mCount.textContent = analysis.transaction_count != null ? String(analysis.transaction_count) : '-';
        mTop.textContent = analysis.top_category ? String(analysis.top_category) : '-';
        insightEl.textContent = analysis.insight ? String(analysis.insight) : 'Insight tidak tersedia.';
        renderBreakdown(analysis.category_breakdown || {});
    }

    function clearOutput() {
        renderSource(null, null);
        mIncome.textContent = '-';
        mExpense.textContent = '-';
        mCount.textContent = '-';
        mTop.textContent = '-';
        insightEl.textContent = 'Belum ada insight.';
        renderBreakdown({});
        jsonOutput.textContent = 'Belum ada response.';
        latestServiceCPayload = null;
        serviceCPayloadOutput.textContent = 'Belum ada payload Service C.';
        setStatus('Output dibersihkan.', 'info');
    }

    async function runAutoAnalysis() {
>>>>>>> 95acdce (Fix nested repo issue)
        const apiKey = String(apiKeyInput.value || '').trim();

        if (!apiKey) {
            setStatus('API key wajib diisi.', 'error');
            return;
        }

        setLoading(true);
<<<<<<< HEAD
        setStatus('Menjalankan default user...', 'info');
=======
        setStatus('Menjalankan analisis otomatis...', 'info');
>>>>>>> 95acdce (Fix nested repo issue)

        try {
            const response = await fetch(autoRunEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'x-api-key': apiKey,
                },
                body: JSON.stringify({}),
            });

            const data = await response.json().catch(() => ({
<<<<<<< HEAD
                message: 'Response bukan JSON valid.',
            }));

=======
                message: 'Response bukan JSON valid.'
            }));

            jsonOutput.textContent = JSON.stringify(data, null, 2);

>>>>>>> 95acdce (Fix nested repo issue)
            if (!response.ok) {
                throw new Error(String(data.message || ('Request gagal dengan status ' + response.status)));
            }

<<<<<<< HEAD
            const userId = Number(data.source?.user_id || 0) || 2;

            runResults = [{
                userId,
                status: data.analysis ? 'ok' : 'empty',
                executedAt: new Date().toISOString(),
                message: data.message || null,
                source: data.source || null,
                analysis: data.analysis || null,
            }];

            selectedUserId = userId;

            renderSummary();
            renderUserRows();
            renderDetails();

            setStatus('Default user selesai diproses.', 'ok');
        } catch (error) {
            setStatus(String(error.message || 'Gagal menjalankan default user.'), 'error');
=======
            const executedAt = new Date().toISOString();

            renderSource(data.source || null, executedAt);
            renderServiceCPayload(data, executedAt);

            if (data.analysis) {
                renderAnalysis(data.analysis);
                setStatus('Auto analysis berhasil. ' + String((data.source && data.source.fetched_transactions) || 0) + ' transaksi diproses.', 'ok');
            } else {
                mIncome.textContent = '-';
                mExpense.textContent = '-';
                mCount.textContent = '-';
                mTop.textContent = '-';
                insightEl.textContent = 'Tidak ada insight baru.';
                renderBreakdown({});
                setStatus(String(data.message || 'Tidak ada transaksi baru.'), 'info');
            }
        } catch (error) {
            setStatus(String(error.message || 'Terjadi kesalahan saat auto analysis.'), 'error');
>>>>>>> 95acdce (Fix nested repo issue)
        } finally {
            setLoading(false);
        }
    }

<<<<<<< HEAD
    async function copyPayload() {
        if (!selectedPayload) {
            setStatus('Belum ada payload yang bisa di-copy.', 'error');
=======
    runBtn.addEventListener('click', runAutoAnalysis);
    clearBtn.addEventListener('click', clearOutput);
    copyServiceCPayloadBtn.addEventListener('click', async () => {
        if (!latestServiceCPayload) {
            setStatus('Jalankan analisis otomatis dulu agar payload Service C tersedia.', 'error');
>>>>>>> 95acdce (Fix nested repo issue)
            return;
        }

        try {
<<<<<<< HEAD
            await navigator.clipboard.writeText(JSON.stringify(selectedPayload, null, 2));
            setStatus('Payload Service C berhasil di-copy.', 'ok');
        } catch (error) {
            setStatus('Gagal copy ke clipboard. Salin manual dari panel payload.', 'error');
        }
    }

    runBatchBtn.addEventListener('click', runBatch);
    runDefaultBtn.addEventListener('click', runDefaultUser);
    clearBtn.addEventListener('click', () => clearDashboard(true));
    copyPayloadBtn.addEventListener('click', copyPayload);

    clearDashboard(false);
=======
            await navigator.clipboard.writeText(JSON.stringify(latestServiceCPayload, null, 2));
            setStatus('Payload Service C berhasil di-copy.', 'ok');
        } catch (error) {
            setStatus('Gagal copy ke clipboard. Silakan copy manual dari panel payload.', 'error');
        }
    });
>>>>>>> 95acdce (Fix nested repo issue)
</script>
</body>
</html>
