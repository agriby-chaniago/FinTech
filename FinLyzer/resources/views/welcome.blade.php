<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FinLyzer</title>
    <style>
        :root {
            --byzantine: #3B59DD;
            --byzantine-hover: #324ECC;
            --byzantine-light: #637BFF;
            --platinum: #DDDDE5;
            --night: #15151B;
            --raisin: #181824;
            --raisin2: #23232E;
            --ok: #A8E6CF;
            --error: #FF8B94;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        body {
            font-family: Inter, "Segoe UI", sans-serif;
            color: var(--platinum);
            background: linear-gradient(135deg, var(--night), var(--raisin), var(--raisin2));
        }

        .shell {
            width: min(1180px, calc(100% - 2rem));
            margin: 1rem auto 2rem;
        }

        .app-shell {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 86px;
            background: var(--raisin);
            border-right: 1px solid rgba(221, 221, 229, 0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem 0.45rem;
            gap: 0.9rem;
            position: sticky;
            top: 0;
            height: 100vh;
            box-shadow: 4px 0 12px rgba(0, 0, 0, 0.28);
        }

        .brand {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--night);
            background: var(--byzantine);
        }

        .side-nav {
            display: grid;
            gap: 0.62rem;
            width: 100%;
        }

        .side-item {
            border: 1px solid rgba(221, 221, 229, 0.1);
            background: rgba(35, 35, 46, 0.85);
            border-radius: 10px;
            color: rgba(221, 221, 229, 0.78);
            font-size: 0.65rem;
            text-align: center;
            line-height: 1.35;
            padding: 0.45rem 0.25rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .side-item.active {
            border-color: rgba(99, 123, 255, 0.5);
            color: var(--byzantine-light);
            background: rgba(59, 89, 221, 0.16);
        }

        .main-shell {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            height: 72px;
            background: var(--raisin);
            border-bottom: 1px solid rgba(221, 221, 229, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.1rem;
            position: sticky;
            top: 0;
            z-index: 20;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .topbar h2 {
            margin: 0;
            color: var(--platinum);
            font-size: 1.08rem;
            font-weight: 700;
        }

        .topbar p {
            margin: 0;
            color: rgba(221, 221, 229, 0.68);
            font-size: 0.79rem;
        }

        .hero {
            border: 1px solid rgba(99, 123, 255, 0.25);
            border-radius: 20px;
            background: linear-gradient(145deg, rgba(35, 35, 46, 0.95), rgba(24, 24, 36, 0.95));
            padding: 1rem 1.1rem;
            box-shadow: 0 16px 34px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.6s ease-out;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border: 1px solid rgba(99, 123, 255, 0.5);
            border-radius: 999px;
            padding: 0.3rem 0.62rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--byzantine-light);
            background: rgba(59, 89, 221, 0.15);
            letter-spacing: 0.03em;
        }

        .hero h1 {
            margin: 0.7rem 0 0;
            font-size: clamp(1.3rem, 3.2vw, 2rem);
            line-height: 1.25;
            color: var(--platinum);
        }

        .hero p {
            margin: 0.55rem 0 0;
            max-width: 72ch;
            color: rgba(221, 221, 229, 0.8);
            line-height: 1.58;
            font-size: 0.9rem;
        }

        .layout {
            margin-top: 0.85rem;
            display: grid;
            grid-template-columns: 330px 1fr;
            gap: 0.8rem;
            align-items: start;
        }

        .panel {
            border: 1px solid rgba(221, 221, 229, 0.08);
            border-radius: 18px;
            background: rgba(35, 35, 46, 0.95);
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.25);
            padding: 0.9rem;
        }

        .panel h2 {
            margin: 0;
            font-size: 1rem;
            color: var(--platinum);
        }

        .panel p {
            margin: 0.36rem 0 0;
            font-size: 0.84rem;
            color: rgba(221, 221, 229, 0.72);
            line-height: 1.5;
        }

        .control {
            position: sticky;
            top: 0.8rem;
        }

        label {
            display: block;
            margin: 0.74rem 0 0.28rem;
            color: rgba(221, 221, 229, 0.78);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        input {
            width: 100%;
            border: 1px solid rgba(99, 123, 255, 0.45);
            border-radius: 11px;
            background: rgba(24, 24, 36, 0.92);
            color: var(--platinum);
            font-size: 0.83rem;
            padding: 0.6rem 0.66rem;
        }

        .date-input-wrap {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 0.35rem;
            align-items: center;
        }

        .date-input-lock {
            cursor: pointer;
        }

        .date-picker-btn {
            border: 1px solid rgba(99, 123, 255, 0.52);
            border-radius: 10px;
            background: rgba(59, 89, 221, 0.18);
            color: var(--platinum);
            padding: 0.56rem 0.62rem;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            white-space: nowrap;
            cursor: pointer;
            transition: transform 120ms ease, filter 120ms ease;
        }

        .date-picker-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.08);
        }

        .hint {
            margin-top: 0.4rem;
            color: rgba(221, 221, 229, 0.66);
            font-size: 0.76rem;
            line-height: 1.4;
        }

        .range-presets {
            margin-top: 0.62rem;
        }

        .range-presets small {
            display: block;
            color: rgba(221, 221, 229, 0.72);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin-bottom: 0.3rem;
        }

        .preset-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.34rem;
        }

        .preset-btn {
            border: 1px solid rgba(99, 123, 255, 0.42);
            background: rgba(24, 24, 36, 0.92);
            color: rgba(221, 221, 229, 0.86);
            border-radius: 9px;
            padding: 0.42rem 0.4rem;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            cursor: pointer;
            transition: transform 120ms ease, border-color 120ms ease, color 120ms ease;
        }

        .preset-btn:hover {
            transform: translateY(-1px);
            border-color: rgba(99, 123, 255, 0.68);
            color: var(--platinum);
        }

        .preset-btn.active {
            color: var(--byzantine-light);
            border-color: rgba(99, 123, 255, 0.8);
            background: rgba(59, 89, 221, 0.16);
        }

        .hint-strong {
            color: rgba(221, 221, 229, 0.82);
        }

        .actions {
            margin-top: 0.78rem;
            display: grid;
            gap: 0.46rem;
        }

        .btn {
            border: 0;
            border-radius: 11px;
            padding: 0.6rem 0.72rem;
            font-family: inherit;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 120ms ease, filter 120ms ease;
        }

        .btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .btn-primary {
            background: var(--byzantine);
            color: var(--night);
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--byzantine-hover);
            transform: translateY(-1px);
        }

        .btn-soft {
            border: 1px solid rgba(99, 123, 255, 0.45);
            background: rgba(24, 24, 36, 0.9);
            color: var(--platinum);
        }

        .btn-soft:hover:not(:disabled) {
            transform: translateY(-1px);
            filter: brightness(1.05);
        }

        .status {
            margin-top: 0.72rem;
            border-radius: 11px;
            border: 1px solid transparent;
            padding: 0.55rem 0.62rem;
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.45;
        }

        .status.info {
            color: #B9C7FF;
            background: rgba(59, 89, 221, 0.15);
            border-color: rgba(99, 123, 255, 0.4);
        }

        .status.ok {
            color: var(--ok);
            background: rgba(168, 230, 207, 0.14);
            border-color: rgba(168, 230, 207, 0.35);
        }

        .status.error {
            color: var(--error);
            background: rgba(255, 139, 148, 0.12);
            border-color: rgba(255, 139, 148, 0.35);
        }

        .metrics {
            margin-top: 0.76rem;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.48rem;
        }

        .metric {
            border: 1px solid rgba(221, 221, 229, 0.08);
            border-radius: 12px;
            background: rgba(24, 24, 36, 0.82);
            min-height: 74px;
            padding: 0.52rem;
        }

        .metric small {
            display: block;
            color: rgba(221, 221, 229, 0.7);
            font-size: 0.72rem;
            margin-bottom: 0.2rem;
        }

        .metric strong {
            font-size: 1rem;
            color: var(--platinum);
            word-break: break-word;
        }

        .metric.range strong {
            font-size: 0.84rem;
            line-height: 1.4;
        }

        .section-title {
            margin: 0.76rem 0 0.35rem;
            color: var(--byzantine-light);
            font-size: 0.78rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .box {
            border: 1px solid rgba(221, 221, 229, 0.08);
            border-radius: 12px;
            background: rgba(24, 24, 36, 0.82);
            min-height: 78px;
            padding: 0.56rem 0.62rem;
            font-size: 0.84rem;
            line-height: 1.55;
            color: rgba(221, 221, 229, 0.9);
        }

        .breakdown {
            display: grid;
            gap: 0.35rem;
        }

        .bar-item {
            border: 1px solid rgba(221, 221, 229, 0.08);
            border-radius: 11px;
            background: rgba(24, 24, 36, 0.82);
            padding: 0.42rem;
        }

        .bar-head {
            display: flex;
            justify-content: space-between;
            gap: 0.3rem;
            margin-bottom: 0.24rem;
            color: rgba(221, 221, 229, 0.78);
            font-size: 0.74rem;
        }

        .bar-track {
            height: 8px;
            border-radius: 999px;
            background: rgba(59, 89, 221, 0.2);
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--byzantine), var(--byzantine-light));
        }

        .meta {
            margin-top: 0.62rem;
            font-size: 0.76rem;
            color: rgba(221, 221, 229, 0.68);
        }

        @media (max-width: 1024px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .control {
                position: static;
            }

            .sidebar {
                display: none;
            }
        }

        @media (max-width: 720px) {
            .shell {
                width: calc(100% - 1rem);
                margin: 0.8rem auto 1.1rem;
            }

            .metrics {
                grid-template-columns: 1fr 1fr;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" aria-label="Main navigation">
            <div class="brand">FZ</div>
            <nav class="side-nav">
                <span class="side-item active">Dashboard</span>
                <span class="side-item">Analyze</span>
                <span class="side-item">Planner</span>
            </nav>
        </aside>

        <div class="main-shell">
            <header class="topbar">
                <h2>FinLyzer</h2>
                <p>Financial Analyzer Service</p>
            </header>

            <div class="shell">
                <header class="hero">
                    <span class="chip">FINLYZER | FINTRACK STYLE</span>
                    <h1>Analisis Keuangan Otomatis</h1>
                    <p>
                        Tampilan FinLyzer ini disamakan dengan nuansa visual FinTrack dan berjalan tanpa login tambahan.
                        Cukup klik analisis untuk memproses data terbaru, lalu kirim hasil ke FinGoals jika dibutuhkan.
                    </p>
                </header>

                <main class="layout">
                    <section class="panel control">
                        <h2>Quick Action</h2>
                        <p>Jalankan analisis langsung dari dashboard.</p>

                        <label for="username">Username</label>
                        <input id="username" type="text" value="User" readonly>
                        <div class="hint">Terisi otomatis dari hasil analisis user default yang diproses sistem.</div>

                        <label for="dateFrom">Tanggal Mulai</label>
                        <div class="date-input-wrap">
                            <input id="dateFrom" type="date" class="date-input-lock" autocomplete="off">
                            <button id="openDateFromPickerBtn" type="button" class="date-picker-btn" aria-label="Buka kalender tanggal mulai">Kalender</button>
                        </div>

                        <label for="dateTo">Tanggal Selesai</label>
                        <div class="date-input-wrap">
                            <input id="dateTo" type="date" class="date-input-lock" autocomplete="off">
                            <button id="openDateToPickerBtn" type="button" class="date-picker-btn" aria-label="Buka kalender tanggal selesai">Kalender</button>
                        </div>
                        <div class="hint">Pilih tanggal lewat kalender. Ketik manual untuk tanggal dinonaktifkan.</div>

                        <div class="range-presets">
                            <small>Pilih cepat rentang waktu</small>
                            <div class="preset-grid">
                                <button type="button" class="preset-btn" data-range-preset="7d">7 Hari</button>
                                <button type="button" class="preset-btn" data-range-preset="30d">30 Hari</button>
                                <button type="button" class="preset-btn" data-range-preset="90d">90 Hari</button>
                                <button type="button" class="preset-btn" data-range-preset="thisMonth">Bulan Ini</button>
                            </div>
                        </div>

                        <div id="rangeHint" class="hint hint-strong">Mode otomatis aktif (semua data).</div>

                        <div class="actions">
                            <button id="runAnalyzeBtn" type="button" class="btn btn-primary">Run Analyze</button>
                            <button id="sendServiceCBtn" type="button" class="btn btn-soft">Send to FinGoals</button>
                            <button id="clearBtn" type="button" class="btn btn-soft">Reset</button>
                        </div>

                        <div id="status" class="status info">Siap digunakan.</div>
                    </section>

                    <section class="panel">
                        <h2>Financial Overview</h2>
                        <p>Ringkasan hasil analisis terbaru untuk membantu keputusan keuangan kamu.</p>

                        <div class="metrics">
                            <div class="metric">
                                <small>Total Income</small>
                                <strong id="mIncome">-</strong>
                            </div>
                            <div class="metric">
                                <small>Total Expense</small>
                                <strong id="mExpense">-</strong>
                            </div>
                            <div class="metric">
                                <small>Net Balance</small>
                                <strong id="mNet">-</strong>
                            </div>
                            <div class="metric">
                                <small>Fetched</small>
                                <strong id="mFetched">-</strong>
                            </div>
                            <div class="metric">
                                <small>Transactions</small>
                                <strong id="mCount">-</strong>
                            </div>
                            <div class="metric">
                                <small>Top Category</small>
                                <strong id="mTop">-</strong>
                            </div>
                            <div class="metric">
                                <small>Savings Rate</small>
                                <strong id="mSavings">-</strong>
                            </div>
                            <div class="metric">
                                <small>Health Status</small>
                                <strong id="mHealth">-</strong>
                            </div>
                            <div class="metric range">
                                <small>Rentang Waktu</small>
                                <strong id="mRange">Semua data</strong>
                            </div>
                        </div>

                        <div class="section-title">Summary</div>
                        <div id="summaryText" class="box">Belum ada ringkasan.</div>

                        <div class="section-title">AI Insight</div>
                        <div id="insightText" class="box">Belum ada insight.</div>

                        <div class="section-title">Category Breakdown</div>
                        <div id="breakdown" class="breakdown">
                            <div class="bar-item">
                                <div class="bar-head"><span>Belum ada data</span><span>0%</span></div>
                                <div class="bar-track"><div class="bar-fill" style="width:0%"></div></div>
                            </div>
                        </div>

                        <div class="meta">Last analyzed: <span id="executedAt">-</span></div>
                    </section>
                </main>
            </div>
        </div>
    </div>

<script>
    const runEndpoint = "{{ route('dashboard.analyze.auto.run') }}";
    const sendServiceCEndpoint = "{{ route('dashboard.analyze.send-service-c') }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const usernameInput = document.getElementById('username');
    const dateFromInput = document.getElementById('dateFrom');
    const dateToInput = document.getElementById('dateTo');
    const openDateFromPickerBtn = document.getElementById('openDateFromPickerBtn');
    const openDateToPickerBtn = document.getElementById('openDateToPickerBtn');
    const presetButtons = Array.from(document.querySelectorAll('[data-range-preset]'));
    const runAnalyzeBtn = document.getElementById('runAnalyzeBtn');
    const sendServiceCBtn = document.getElementById('sendServiceCBtn');
    const clearBtn = document.getElementById('clearBtn');
    const statusEl = document.getElementById('status');
    const rangeHintEl = document.getElementById('rangeHint');

    const mIncome = document.getElementById('mIncome');
    const mExpense = document.getElementById('mExpense');
    const mNet = document.getElementById('mNet');
    const mFetched = document.getElementById('mFetched');
    const mCount = document.getElementById('mCount');
    const mTop = document.getElementById('mTop');
    const mSavings = document.getElementById('mSavings');
    const mHealth = document.getElementById('mHealth');
    const mRange = document.getElementById('mRange');
    const summaryText = document.getElementById('summaryText');
    const insightText = document.getElementById('insightText');
    const breakdownEl = document.getElementById('breakdown');
    const executedAtEl = document.getElementById('executedAt');

    const MAX_RANGE_DAYS = 366;
    let latestPayload = null;

    function pickerApiAvailable(input) {
        return input && typeof input.showPicker === 'function';
    }

    function openNativePicker(input) {
        if (pickerApiAvailable(input)) {
            try {
                input.showPicker();
                return;
            } catch (error) {
                // Ignore and fallback to focus.
            }
        }

        input.focus();
    }

    function enforceCalendarOnlyInput(input) {
        if (!pickerApiAvailable(input)) {
            return;
        }

        input.setAttribute('inputmode', 'none');

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Tab' || event.key === 'Escape') {
                return;
            }

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openNativePicker(input);
                return;
            }

            event.preventDefault();
        });

        input.addEventListener('paste', (event) => event.preventDefault());
        input.addEventListener('drop', (event) => event.preventDefault());
        input.addEventListener('wheel', (event) => event.preventDefault(), { passive: false });
        input.addEventListener('focus', () => openNativePicker(input));
    }

    function dateOnlyString(value) {
        return new Date(value).toISOString().slice(0, 10);
    }

    function getTodayDateString() {
        const now = new Date();
        return dateOnlyString(now);
    }

    function calculateRangeDays(dateFrom, dateTo) {
        if (!dateFrom || !dateTo) {
            return null;
        }

        const start = new Date(dateFrom + 'T00:00:00Z');
        const end = new Date(dateTo + 'T00:00:00Z');

        if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end < start) {
            return null;
        }

        const diffMs = end.getTime() - start.getTime();
        return Math.floor(diffMs / 86400000) + 1;
    }

    function formatRangeLabel(dateFrom, dateTo) {
        if (dateFrom === '' && dateTo === '') {
            return 'Semua data';
        }

        const resolvedFrom = dateFrom !== '' ? dateFrom : 'awal data';
        const resolvedTo = dateTo !== '' ? dateTo : 'sekarang';
        const rangeDays = calculateRangeDays(dateFrom, dateTo);

        if (rangeDays !== null) {
            return resolvedFrom + ' - ' + resolvedTo + ' (' + rangeDays + ' hari)';
        }

        return resolvedFrom + ' - ' + resolvedTo;
    }

    function syncDateConstraints() {
        const today = getTodayDateString();
        dateFromInput.max = today;
        dateToInput.max = today;
        dateToInput.min = dateFromInput.value || '';
    }

    function updatePresetActiveState(activePreset) {
        presetButtons.forEach((button) => {
            const isActive = button.dataset.rangePreset === activePreset;
            button.classList.toggle('active', isActive);
        });
    }

    function updateRangeHint() {
        const dateFrom = String(dateFromInput.value || '').trim();
        const dateTo = String(dateToInput.value || '').trim();

        if (dateFrom === '' && dateTo === '') {
            rangeHintEl.textContent = 'Mode otomatis aktif (semua data).';
            updatePresetActiveState('');
            return;
        }

        const rangeDays = calculateRangeDays(dateFrom, dateTo);

        if (rangeDays !== null) {
            rangeHintEl.textContent = 'Rentang aktif: ' + rangeDays + ' hari. Maksimal ' + MAX_RANGE_DAYS + ' hari.';
        } else {
            rangeHintEl.textContent = 'Rentang aktif sebagian. Sistem akan menggunakan batas tanggal yang diisi.';
        }

        updatePresetActiveState('');
    }

    function applyPresetRange(preset) {
        const today = new Date();
        const dateTo = dateOnlyString(today.toISOString());
        let dateFrom = '';

        if (preset === '7d') {
            const from = new Date(today);
            from.setUTCDate(from.getUTCDate() - 6);
            dateFrom = dateOnlyString(from.toISOString());
        }

        if (preset === '30d') {
            const from = new Date(today);
            from.setUTCDate(from.getUTCDate() - 29);
            dateFrom = dateOnlyString(from.toISOString());
        }

        if (preset === '90d') {
            const from = new Date(today);
            from.setUTCDate(from.getUTCDate() - 89);
            dateFrom = dateOnlyString(from.toISOString());
        }

        if (preset === 'thisMonth') {
            const from = new Date(Date.UTC(today.getUTCFullYear(), today.getUTCMonth(), 1));
            dateFrom = dateOnlyString(from.toISOString());
        }

        if (dateFrom !== '') {
            dateFromInput.value = dateFrom;
            dateToInput.value = dateTo;
            syncDateConstraints();
            updateRangeHint();
            updatePresetActiveState(preset);
        }
    }

    function normalizeErrorMessage(message) {
        const raw = String(message || '').trim();
        const lowered = raw.toLowerCase();

        if (lowered.includes('failed to connect') || lowered.includes('cURL error 7'.toLowerCase())) {
            return 'Service FinTrack belum aktif. Jalankan FinTrack dulu, lalu ulangi analisis.';
        }

        return raw !== '' ? raw : 'Terjadi kesalahan saat memproses permintaan.';
    }

    function setStatus(message, type) {
        statusEl.className = 'status ' + type;
        statusEl.textContent = message;
    }

    function setAnalyzeLoading(state) {
        runAnalyzeBtn.disabled = state;
        runAnalyzeBtn.textContent = state ? 'Processing...' : 'Run Analyze';
    }

    function setSendLoading(state) {
        sendServiceCBtn.disabled = state;
        sendServiceCBtn.textContent = state ? 'Sending...' : 'Send to FinGoals';
    }

    function formatMoney(value) {
        const number = Number(value);

        if (Number.isNaN(number)) {
            return '-';
        }

        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
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
            timeStyle: 'short',
        }).format(date);
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

    function buildServiceCPayload(result) {
        const source = result.source || {};
        const analysis = result.analysis || null;
        const userId = Number(source.user_id || 0);
        const normalizedUserId = Number.isFinite(userId) ? userId : null;

        if (!analysis) {
            return {
                schema_version: 'service-c.v1',
                status: result.status,
                executed_at: result.executedAt,
                source_sync: {
                    user_id: normalizedUserId,
                    user_name: result.username,
                    fetched_transactions: Number(source.fetched_transactions || 0),
                    date_from: source.date_from || null,
                    date_to: source.date_to || null,
                },
                message: result.message || 'Tidak ada transaksi baru.',
                metrics: null,
                ai_insight: null,
                category_breakdown: [],
            };
        }

        return {
            schema_version: 'service-c.v1',
            status: result.status,
            executed_at: result.executedAt,
            source_sync: {
                user_id: normalizedUserId,
                user_name: result.username,
                fetched_transactions: Number(source.fetched_transactions || 0),
                date_from: source.date_from || null,
                date_to: source.date_to || null,
            },
            metrics: {
                total_income: Number(analysis.total_income || 0),
                total_expense: Number(analysis.total_expense || 0),
                transaction_count: Number(analysis.transaction_count || 0),
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

    function deriveUsername(source) {
        const sourceName = String(source.user_name || '').trim();

        if (sourceName !== '') {
            return sourceName;
        }

        const sourceEmail = String(source.user_email || '').trim();

        if (sourceEmail.includes('@')) {
            return sourceEmail.split('@')[0];
        }

        const sourceUserId = Number(source.user_id || 0);

        if (Number.isFinite(sourceUserId) && sourceUserId > 0) {
            return 'User ' + sourceUserId;
        }

        return 'User';
    }

    function renderResult(result) {
        if (!result) {
            usernameInput.value = 'User';
            mIncome.textContent = '-';
            mExpense.textContent = '-';
            mNet.textContent = '-';
            mFetched.textContent = '-';
            mCount.textContent = '-';
            mTop.textContent = '-';
            mSavings.textContent = '-';
            mHealth.textContent = '-';
            mRange.textContent = 'Semua data';
            summaryText.textContent = 'Belum ada ringkasan.';
            insightText.textContent = 'Belum ada insight.';
            executedAtEl.textContent = '-';
            renderBreakdown({});
            latestPayload = null;
            return;
        }

        const source = result.source || {};
        const analysis = result.analysis || null;
        const dateFrom = String(source.date_from || '').trim();
        const dateTo = String(source.date_to || '').trim();
        const rangeLabel = formatRangeLabel(dateFrom, dateTo);

        usernameInput.value = result.username;
        mFetched.textContent = String(Number(source.fetched_transactions || 0));
        mRange.textContent = rangeLabel;
        executedAtEl.textContent = formatDateTime(result.executedAt);

        if (!analysis) {
            mIncome.textContent = '-';
            mExpense.textContent = '-';
            mNet.textContent = '-';
            mCount.textContent = '-';
            mTop.textContent = '-';
            mSavings.textContent = '-';
            mHealth.textContent = 'Belum ada data baru';
            summaryText.textContent = result.message || 'Belum ada transaksi baru.';
            insightText.textContent = 'Belum ada insight baru.';
            renderBreakdown({});
            latestPayload = buildServiceCPayload(result);
            return;
        }

        const totalIncome = Number(analysis.total_income || 0);
        const totalExpense = Number(analysis.total_expense || 0);
        const netBalance = analysis.net_balance != null
            ? Number(analysis.net_balance)
            : totalIncome - totalExpense;
        const savingsRate = analysis.savings_rate != null
            ? Number(analysis.savings_rate)
            : (totalIncome > 0 ? (netBalance / totalIncome) * 100 : 0);

        mIncome.textContent = formatMoney(totalIncome);
        mExpense.textContent = formatMoney(totalExpense);
        mNet.textContent = formatMoney(netBalance);
        mCount.textContent = String(Number(analysis.transaction_count || 0));
        mTop.textContent = analysis.top_category ? String(analysis.top_category) : '-';
        mSavings.textContent = Number.isFinite(savingsRate) ? savingsRate.toFixed(2) + '%' : '-';
        mHealth.textContent = analysis.financial_health ? String(analysis.financial_health) : '-';
        summaryText.textContent = analysis.summary
            ? String(analysis.summary)
            : 'Analisis selesai. Gunakan ringkasan ini untuk keputusan finansial berikutnya.';
        insightText.textContent = analysis.insight
            ? String(analysis.insight)
            : 'Insight AI tidak tersedia untuk saat ini.';

        renderBreakdown(analysis.category_breakdown || {});
        latestPayload = buildServiceCPayload(result);
    }

    async function requestAnalyze() {
        const payload = {};
        const dateFrom = String(dateFromInput.value || '').trim();
        const dateTo = String(dateToInput.value || '').trim();

        if (dateFrom !== '' && dateTo !== '' && dateFrom > dateTo) {
            throw new Error('Tanggal selesai harus sama atau setelah tanggal mulai.');
        }

        const rangeDays = calculateRangeDays(dateFrom, dateTo);

        if (rangeDays !== null && rangeDays > MAX_RANGE_DAYS) {
            throw new Error('Rentang maksimal ' + MAX_RANGE_DAYS + ' hari agar analisis tetap cepat dan stabil.');
        }

        if (dateFrom !== '') {
            payload.date_from = dateFrom;
        }

        if (dateTo !== '') {
            payload.date_to = dateTo;
        }

        const response = await fetch(runEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({
            message: 'Response server tidak valid.',
        }));

        if (!response.ok) {
            throw new Error(normalizeErrorMessage(data.message || ('Request gagal dengan status ' + response.status)));
        }

        const source = data.source || {};

        return {
            username: deriveUsername(source),
            status: data.analysis ? 'ok' : 'empty',
            executedAt: new Date().toISOString(),
            message: data.message || null,
            source,
            analysis: data.analysis || null,
        };
    }

    async function runAnalyze() {
        setAnalyzeLoading(true);
        const dateFrom = String(dateFromInput.value || '').trim();
        const dateTo = String(dateToInput.value || '').trim();
        const runContext = dateFrom !== '' || dateTo !== ''
            ? ('pada rentang ' + (dateFrom !== '' ? dateFrom : 'awal data') + ' sampai ' + (dateTo !== '' ? dateTo : 'sekarang'))
            : 'tanpa batas rentang';
        setStatus('Menjalankan analisis ' + runContext + '...', 'info');

        try {
            const result = await requestAnalyze();
            renderResult(result);

            if (result.status === 'empty') {
                setStatus('Belum ada transaksi baru untuk dianalisis.', 'info');
            } else {
                setStatus('Analisis selesai dan berhasil ditampilkan.', 'ok');
            }
        } catch (error) {
            setStatus(normalizeErrorMessage(error.message || 'Gagal menjalankan analisis.'), 'error');
        } finally {
            setAnalyzeLoading(false);
        }
    }

    async function sendToServiceC() {
        if (!latestPayload) {
            setStatus('Jalankan analisis dulu sebelum mengirim ke FinGoals.', 'error');
            return;
        }

        setSendLoading(true);
        setStatus('Mengirim hasil ke FinGoals...', 'info');

        try {
            const response = await fetch(sendServiceCEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    payload: latestPayload,
                }),
            });

            const data = await response.json().catch(() => ({
                message: 'Response server tidak valid.',
            }));

            if (!response.ok) {
                throw new Error(normalizeErrorMessage(data.message || ('Request gagal dengan status ' + response.status)));
            }

            setStatus('Hasil berhasil dikirim ke FinGoals.', 'ok');
        } catch (error) {
            setStatus(normalizeErrorMessage(error.message || 'Gagal mengirim hasil.'), 'error');
        } finally {
            setSendLoading(false);
        }
    }

    function clearDashboard() {
        dateFromInput.value = '';
        dateToInput.value = '';
        syncDateConstraints();
        updateRangeHint();
        renderResult(null);
        setStatus('Dashboard direset.', 'info');
    }

    dateFromInput.addEventListener('change', () => {
        syncDateConstraints();

        if (dateToInput.value !== '' && dateFromInput.value !== '' && dateToInput.value < dateFromInput.value) {
            dateToInput.value = dateFromInput.value;
        }

        updateRangeHint();
    });

    dateToInput.addEventListener('change', updateRangeHint);

    presetButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const preset = String(button.dataset.rangePreset || '');

            if (preset !== '') {
                applyPresetRange(preset);
            }
        });
    });

    openDateFromPickerBtn.addEventListener('click', () => openNativePicker(dateFromInput));
    openDateToPickerBtn.addEventListener('click', () => openNativePicker(dateToInput));

    enforceCalendarOnlyInput(dateFromInput);
    enforceCalendarOnlyInput(dateToInput);

    runAnalyzeBtn.addEventListener('click', runAnalyze);
    sendServiceCBtn.addEventListener('click', sendToServiceC);
    clearBtn.addEventListener('click', clearDashboard);

    syncDateConstraints();
    updateRangeHint();
    renderResult(null);
</script>
</body>
</html>
