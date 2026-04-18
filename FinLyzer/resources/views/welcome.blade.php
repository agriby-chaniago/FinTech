<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Financial Analyzer Service') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
        }

        .btn {
            border: 0;
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
            cursor: not-allowed;
        }

        .btn-primary {
            background: linear-gradient(120deg, #22d3ee, #06b6d4);
            color: #042028;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-1px);
        }

        .btn-soft {
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
        }

        .breakdown {
            display: grid;
            gap: 0.32rem;
        }

        .bar-item {
            border: 1px solid #334d70;
            border-radius: 10px;
            background: #162942;
            padding: 0.4rem;
        }

        .bar-head {
            display: flex;
            justify-content: space-between;
            gap: 0.3rem;
            color: #a6c0df;
            font-size: 0.72rem;
            margin-bottom: 0.24rem;
        }

        .bar-track {
            height: 7px;
            border-radius: 999px;
            background: #233a5c;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            border-radius: 999px;
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
        }

        pre {
            margin: 0;
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
        }
    </style>
</head>
<body>
<div class="container">
    <header class="hero">
        <span class="chip">AUTO · SINGLE USER</span>
        <h1>Dashboard Analisis Otomatis yang Lebih Ringkas</h1>
        <p>
            Jalankan analisis otomatis untuk satu user berbasis email. Tampilan disederhanakan agar fokus ke hasil penting:
            source sync, metrik, ringkasan, insight AI, dan payload untuk Service C.
        </p>
    </header>

    <main class="layout">
        <section class="card controls">
            <h2>Kontrol</h2>
            <p>Isi email user lalu jalankan analisis.</p>

            <div class="stack">
                <div>
                    <label for="apiKey">API Key</label>
                    <input id="apiKey" type="text" value="{{ (string) config('services.analyzer.api_key', '') }}" placeholder="Contoh: fintrack1">
                </div>

                <div>
                    <label for="userEmail">User Email</label>
                    <input id="userEmail" type="text" value="" placeholder="Contoh: user@mail.com">
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
                    <li>Isi API key dan email user.</li>
                    <li>Klik Run Analyze by Email.</li>
                    <li>Lihat hasil analisis user pada panel detail.</li>
                </ol>

                <div class="actions">
                    <button id="runAnalyzeBtn" type="button" class="btn btn-primary">Run Analyze by Email</button>
                    <button id="clearBtn" type="button" class="btn btn-soft">Clear</button>
                </div>

                <div id="status" class="status info">Siap dipakai.</div>
            </div>
        </section>

        <section class="card">
            <h2>Hasil</h2>
            <p>Ringkasan hasil user tunggal berbasis email.</p>

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
                                <th>User Email</th>
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
                            <small>User Email</small>
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
                        <button id="sendServiceCBtn" type="button" class="btn btn-primary">Kirim ke Service C</button>
                    </div>
                    <div class="json">
                        <pre id="payloadOutput">Belum ada payload.</pre>
                    </div>
                </section>
            </div>
        </section>
    </main>
</div>

<script>
    const autoEndpoint = "{{ url('/api/analyze/auto') }}";
    const sendServiceCEndpoint = "{{ url('/api/analyze/send-service-c') }}";

    const apiKeyInput = document.getElementById('apiKey');
    const userEmailInput = document.getElementById('userEmail');
    const sinceOverrideInput = document.getElementById('sinceOverride');
    const includeSummaryInput = document.getElementById('includeSummary');
    const useSavedSinceInput = document.getElementById('useSavedSince');

    const runAnalyzeBtn = document.getElementById('runAnalyzeBtn');
    const clearBtn = document.getElementById('clearBtn');
    const copyPayloadBtn = document.getElementById('copyPayloadBtn');
    const sendServiceCBtn = document.getElementById('sendServiceCBtn');

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

    const mIncome = document.getElementById('mIncome');
    const mExpense = document.getElementById('mExpense');
    const mCount = document.getElementById('mCount');
    const mTop = document.getElementById('mTop');
    const mNet = document.getElementById('mNet');
    const mSavings = document.getElementById('mSavings');
    const mHealth = document.getElementById('mHealth');

    const analysisSummaryEl = document.getElementById('analysisSummary');
    const insightText = document.getElementById('insightText');
    const breakdownEl = document.getElementById('breakdown');
    const payloadOutput = document.getElementById('payloadOutput');

    let runResults = [];
    let selectedUserEmail = null;
    let selectedPayload = null;

    function setStatus(message, type) {
        statusEl.className = 'status ' + type;
        statusEl.textContent = message;
    }

    function setLoading(state) {
        runAnalyzeBtn.disabled = state;
        runAnalyzeBtn.textContent = state ? 'Running...' : 'Run Analyze by Email';
    }

    function setSendLoading(state) {
        sendServiceCBtn.disabled = state;
        sendServiceCBtn.textContent = state ? 'Mengirim...' : 'Kirim ke Service C';
    }

    function parseUserEmail(raw) {
        const email = String(raw || '').trim().toLowerCase();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailRegex.test(email)) {
            return '';
        }

        return email;
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
                const activeClass = item.userEmail === selectedUserEmail ? 'active' : '';
                const badge = '<span class="badge ' + badgeClass(item.status) + '">' + normalizeStatus(item) + '</span>';
                const fetched = Number(item.source?.fetched_transactions || 0);
                const top = item.analysis?.top_category || '-';
                const userEmail = String(item.userEmail || '-');

                return '' +
                    '<tr class="' + activeClass + '" data-user-email="' + userEmail + '">' +
                        '<td data-label="User">' + userEmail + '</td>' +
                        '<td data-label="Status">' + badge + '</td>' +
                        '<td data-label="Fetched">' + fetched + '</td>' +
                        '<td data-label="Top Category">' + top + '</td>' +
                    '</tr>';
            })
            .join('');

        userRows.querySelectorAll('tr[data-user-email]').forEach((row) => {
            row.addEventListener('click', () => {
                selectedUserEmail = String(row.getAttribute('data-user-email') || '');
                renderUserRows();
                renderDetails();
            });
        });
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

    function buildServiceCPayload(item) {
        const source = item.source || {};
        const analysis = item.analysis || null;
        const sourceUserId = source.user_id != null ? Number(source.user_id) : null;
        const normalizedSourceUserId = Number.isFinite(sourceUserId) ? sourceUserId : null;

        if (!analysis) {
            return {
                schema_version: 'service-c.v1',
                status: item.status,
                executed_at: item.executedAt,
                source_sync: {
                    user_id: normalizedSourceUserId,
                    user_email: item.userEmail || source.user_email || null,
                    fetched_transactions: Number(source.fetched_transactions || 0),
                    since_source: source.since_source || null,
                    next_since: source.next_since || null,
                },
                message: item.message || 'Tidak ada transaksi baru.',
                metrics: null,
                ai_insight: null,
                category_breakdown: [],
            };
        }

        return {
            schema_version: 'service-c.v1',
            status: item.status,
            executed_at: item.executedAt,
            source_sync: {
                user_id: normalizedSourceUserId,
                user_email: item.userEmail || source.user_email || null,
                fetched_transactions: Number(source.fetched_transactions || 0),
                since_source: source.since_source || null,
                next_since: source.next_since || null,
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

    function renderDetails() {
        const selected = runResults.find((item) => item.userEmail === selectedUserEmail) || null;

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

        dUser.textContent = String(selected.userEmail || '-');
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
        selectedUserEmail = null;
        selectedPayload = null;

        renderSummary();
        renderUserRows();
        renderDetails();

        if (showStatus) {
            setStatus('Dashboard dibersihkan.', 'info');
        }
    }

    async function requestAutoForUser(apiKey, userEmail) {
        const payload = {
            email: userEmail,
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
            userId: Number(data.source?.user_id || 0) || null,
            userEmail: String(data.source?.user_email || userEmail),
            status: data.analysis ? 'ok' : 'empty',
            executedAt: new Date().toISOString(),
            message: data.message || null,
            source: data.source || null,
            analysis: data.analysis || null,
        };
    }

    async function runAnalyze() {
        const apiKey = String(apiKeyInput.value || '').trim();

        if (!apiKey) {
            setStatus('API key wajib diisi.', 'error');
            return;
        }

        const userEmail = parseUserEmail(userEmailInput.value);

        if (!userEmail) {
            setStatus('Isi 1 email user yang valid. Contoh: user@mail.com', 'error');
            return;
        }

        setLoading(true);
        setStatus('Menjalankan analisis untuk ' + userEmail + '...', 'info');

        try {
            const result = await requestAutoForUser(apiKey, userEmail);

            runResults = [result];
            selectedUserEmail = result.userEmail;

            renderSummary();
            renderUserRows();
            renderDetails();

            if (result.status === 'empty') {
                setStatus('Tidak ada transaksi baru untuk email tersebut.', 'info');
            } else {
                setStatus('Analisis user berhasil diproses.', 'ok');
            }
        } catch (error) {
            runResults = [{
                userId: null,
                userEmail,
                status: 'error',
                executedAt: new Date().toISOString(),
                message: String(error.message || 'Gagal memproses user.'),
                source: null,
                analysis: null,
            }];

            selectedUserEmail = userEmail;

            renderSummary();
            renderUserRows();
            renderDetails();

            setStatus(String(error.message || 'Gagal memproses user.'), 'error');
        } finally {
            setLoading(false);
        }
    }

    async function sendToServiceC() {
        const apiKey = String(apiKeyInput.value || '').trim();

        if (!apiKey) {
            setStatus('API key wajib diisi.', 'error');
            return;
        }

        if (!selectedPayload) {
            setStatus('Belum ada payload yang bisa dikirim ke Service C.', 'error');
            return;
        }

        setSendLoading(true);
        setStatus('Mengirim payload ke Service C...', 'info');

        try {
            const response = await fetch(sendServiceCEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'x-api-key': apiKey,
                },
                body: JSON.stringify({
                    payload: selectedPayload,
                }),
            });

            const data = await response.json().catch(() => ({
                message: 'Response bukan JSON valid.',
            }));

            if (!response.ok) {
                throw new Error(String(data.message || ('Request gagal dengan status ' + response.status)));
            }

            const recommendation = String(data.service_c_response?.investment_recommendation || '').trim();

            if (recommendation !== '') {
                setStatus('Payload terkirim ke Service C. Rekomendasi: ' + recommendation, 'ok');
            } else {
                setStatus('Payload berhasil dikirim ke Service C.', 'ok');
            }
        } catch (error) {
            setStatus(String(error.message || 'Gagal mengirim ke Service C.'), 'error');
        } finally {
            setSendLoading(false);
        }
    }

    async function copyPayload() {
        if (!selectedPayload) {
            setStatus('Belum ada payload yang bisa di-copy.', 'error');
            return;
        }

        try {
            await navigator.clipboard.writeText(JSON.stringify(selectedPayload, null, 2));
            setStatus('Payload Service C berhasil di-copy.', 'ok');
        } catch (error) {
            setStatus('Gagal copy ke clipboard. Salin manual dari panel payload.', 'error');
        }
    }

    runAnalyzeBtn.addEventListener('click', runAnalyze);
    sendServiceCBtn.addEventListener('click', sendToServiceC);
    clearBtn.addEventListener('click', () => clearDashboard(true));
    copyPayloadBtn.addEventListener('click', copyPayload);

    clearDashboard(false);
</script>
</body>
</html>
