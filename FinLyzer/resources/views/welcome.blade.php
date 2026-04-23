<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>{{ config('app.name', 'FinLyzer') }} Dashboard</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<style>
		:root {
			--page: #070f1b;
			--card: #111f34;
			--line: #284466;
			--line-soft: rgba(193, 214, 245, 0.16);
			--text: #e8f1ff;
			--muted: #9ab0d0;
			--primary: #4ad8f6;
			--primary-deep: #19badf;
			--ok: #a7f3d0;
			--danger: #fecaca;
		}

		* {
			box-sizing: border-box;
		}

		body {
			margin: 0;
			min-height: 100vh;
			font-family: "Manrope", "Segoe UI", sans-serif;
			color: var(--text);
			background:
				radial-gradient(460px 220px at 14% -6%, rgba(74, 216, 246, 0.17), transparent 72%),
				radial-gradient(420px 240px at 102% 8%, rgba(68, 132, 255, 0.16), transparent 70%),
				linear-gradient(156deg, #050b15, #0a1526 54%, #0d1a2d);
		}

		.shell {
			width: min(1220px, calc(100% - 1.4rem));
			margin: 1rem auto 1.6rem;
			border: 1px solid var(--line);
			border-radius: 20px;
			background: rgba(7, 15, 27, 0.76);
			box-shadow: 0 24px 52px rgba(1, 5, 12, 0.55);
			backdrop-filter: blur(8px);
			overflow: hidden;
		}

		.topbar {
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 0.8rem;
			padding: 1rem 1.1rem;
			border-bottom: 1px solid var(--line-soft);
			background: linear-gradient(180deg, rgba(19, 34, 55, 0.9), rgba(17, 31, 52, 0.5));
		}

		.brand h1 {
			margin: 0;
			font-size: 1.16rem;
			letter-spacing: 0.02em;
		}

		.brand p {
			margin: 0.2rem 0 0;
			color: var(--muted);
			font-size: 0.84rem;
		}

		.session-box {
			display: flex;
			align-items: center;
			gap: 0.65rem;
			border: 1px solid rgba(74, 216, 246, 0.26);
			background: rgba(12, 25, 43, 0.72);
			border-radius: 14px;
			padding: 0.5rem 0.58rem;
		}

		.session-box strong {
			display: block;
			font-size: 0.8rem;
			color: #d6ebff;
		}

		.session-box small {
			display: block;
			color: var(--muted);
			font-size: 0.72rem;
			max-width: 200px;
			white-space: nowrap;
			text-overflow: ellipsis;
			overflow: hidden;
		}

		.layout {
			display: grid;
			grid-template-columns: 336px 1fr;
			gap: 0.72rem;
			padding: 0.75rem;
		}

		.panel {
			border: 1px solid var(--line-soft);
			border-radius: 16px;
			background: rgba(11, 22, 38, 0.84);
			padding: 0.78rem;
			animation: fadeIn 220ms ease-out;
		}

		.panel h2 {
			margin: 0;
			font-size: 0.98rem;
		}

		.panel p {
			margin: 0.28rem 0 0.68rem;
			color: var(--muted);
			font-size: 0.82rem;
			line-height: 1.55;
		}

		label {
			display: block;
			margin: 0.46rem 0 0.24rem;
			color: #bfd4f2;
			font-size: 0.77rem;
			font-weight: 700;
		}

		input[type="text"],
		input[type="date"] {
			width: 100%;
			border: 1px solid #35567e;
			border-radius: 10px;
			background: #101f35;
			color: #e6f0ff;
			font-size: 0.84rem;
			padding: 0.58rem 0.62rem;
		}

		input:focus {
			outline: 2px solid rgba(74, 216, 246, 0.24);
			border-color: var(--primary);
		}

		.date-wrap {
			display: flex;
			align-items: center;
			gap: 0.45rem;
		}

		.date-wrap input {
			flex: 1;
			min-width: 0;
		}

		.btn {
			border: 0;
			border-radius: 10px;
			padding: 0.58rem 0.66rem;
			font-family: inherit;
			font-size: 0.8rem;
			font-weight: 700;
			cursor: pointer;
			transition: transform 120ms ease, filter 120ms ease;
		}

		.btn:disabled {
			opacity: 0.65;
			cursor: not-allowed;
		}

		.btn:hover:not(:disabled) {
			transform: translateY(-1px);
			filter: brightness(1.04);
		}

		.btn-primary {
			color: #032631;
			background: linear-gradient(120deg, var(--primary), var(--primary-deep));
		}

		.btn-soft {
			color: #d8e8ff;
			border: 1px solid #3b5f8b;
			background: #1a304f;
		}

		.actions {
			display: grid;
			gap: 0.45rem;
			margin-top: 0.7rem;
		}

		.hint {
			margin-top: 0.35rem;
			color: var(--muted);
			font-size: 0.74rem;
			line-height: 1.5;
		}

		.status {
			margin-top: 0.72rem;
			border-radius: 10px;
			border: 1px solid transparent;
			padding: 0.56rem 0.62rem;
			font-size: 0.8rem;
			line-height: 1.45;
			font-weight: 600;
		}

		.status.info {
			color: #bfdaff;
			border-color: rgba(96, 146, 255, 0.4);
			background: rgba(59, 99, 181, 0.2);
		}

		.status.ok {
			color: var(--ok);
			border-color: rgba(167, 243, 208, 0.35);
			background: rgba(16, 70, 55, 0.28);
		}

		.status.error {
			color: var(--danger);
			border-color: rgba(254, 202, 202, 0.35);
			background: rgba(116, 26, 26, 0.3);
		}

		.metrics {
			margin-top: 0.75rem;
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 0.48rem;
		}

		.metric {
			border: 1px solid var(--line-soft);
			border-radius: 11px;
			background: rgba(15, 29, 49, 0.85);
			min-height: 72px;
			padding: 0.48rem;
		}

		.metric small {
			display: block;
			color: var(--muted);
			font-size: 0.72rem;
			margin-bottom: 0.15rem;
		}

		.metric strong {
			color: #e6f1ff;
			font-size: 0.96rem;
			line-height: 1.45;
			word-break: break-word;
		}

		.metric.range strong {
			font-size: 0.84rem;
		}

		.section-title {
			margin: 0.78rem 0 0.34rem;
			color: #9cc9ff;
			font-size: 0.76rem;
			font-weight: 700;
			letter-spacing: 0.04em;
			text-transform: uppercase;
		}

		.box {
			border: 1px solid var(--line-soft);
			border-radius: 11px;
			background: rgba(15, 29, 49, 0.84);
			min-height: 72px;
			padding: 0.56rem 0.62rem;
			color: #d9e8ff;
			font-size: 0.83rem;
			line-height: 1.55;
		}

		.breakdown {
			display: grid;
			gap: 0.35rem;
		}

		.bar-item {
			border: 1px solid var(--line-soft);
			border-radius: 10px;
			background: rgba(15, 29, 49, 0.84);
			padding: 0.44rem;
		}

		.bar-head {
			display: flex;
			justify-content: space-between;
			gap: 0.3rem;
			font-size: 0.74rem;
			color: #b8cdf0;
			margin-bottom: 0.2rem;
		}

		.bar-track {
			height: 8px;
			border-radius: 999px;
			overflow: hidden;
			background: rgba(66, 123, 204, 0.25);
		}

		.bar-fill {
			height: 100%;
			border-radius: inherit;
			background: linear-gradient(90deg, #57d8f8, #4a7dff);
		}

		.meta {
			margin-top: 0.62rem;
			color: var(--muted);
			font-size: 0.76rem;
		}

		@media (max-width: 1024px) {
			.layout {
				grid-template-columns: 1fr;
			}

			.session-box {
				max-width: 50%;
			}
		}

		@media (max-width: 740px) {
			.topbar {
				flex-direction: column;
				align-items: flex-start;
			}

			.session-box {
				width: 100%;
				max-width: 100%;
				justify-content: space-between;
			}

			.metrics {
				grid-template-columns: 1fr 1fr;
			}
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
				transform: translateY(8px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}
	</style>
</head>
<body>
<main class="shell">
	<header class="topbar">
		<div class="brand">
			<h1>FinLyzer Dashboard</h1>
			<p>Analisis transaksi otomatis dengan rentang waktu fleksibel.</p>
		</div>

		<div class="session-box">
			<div>
				<strong>{{ auth()->user()?->name ?? 'User' }}</strong>
				<small>{{ auth()->user()?->email ?? '-' }}</small>
			</div>
			<form method="POST" action="{{ route('logout') }}">
				@csrf
				<button type="submit" class="btn btn-soft">Logout</button>
			</form>
		</div>
	</header>

	<section class="layout">
		<section class="panel">
			<h2>Quick Action</h2>
			<p>Pilih rentang tanggal lalu jalankan analisis untuk akun yang sedang login.</p>

			<label for="dateFrom">Tanggal Mulai</label>
			<div class="date-wrap">
				<input id="dateFrom" type="date" autocomplete="off">
				<button id="openDateFromPickerBtn" type="button" class="btn btn-soft">Kalender</button>
			</div>

			<label for="dateTo">Tanggal Selesai</label>
			<div class="date-wrap">
				<input id="dateTo" type="date" autocomplete="off">
				<button id="openDateToPickerBtn" type="button" class="btn btn-soft">Kalender</button>
			</div>

			<div id="rangeHint" class="hint">Rentang aktif akan dipakai saat analisis.</div>

			<div class="actions">
				<button id="runAnalyzeBtn" type="button" class="btn btn-primary">Run Analyze</button>
				<button id="sendServiceCBtn" type="button" class="btn btn-soft">Send to FinGoals</button>
				<button id="clearBtn" type="button" class="btn btn-soft">Reset</button>
			</div>

			<div id="status" class="status info">Siap digunakan.</div>
		</section>

		<section class="panel">
			<h2>Financial Overview</h2>
			<p>Ringkasan hasil analisis terakhir untuk membantu pengambilan keputusan keuangan.</p>

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
				<div class="metric range">
					<small>Rentang Waktu</small>
					<strong id="mRange">-</strong>
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
	</section>
</main>

<script>
	const runEndpoint = "{{ route('dashboard.analyze.auto.run') }}";
	const sendServiceCEndpoint = "{{ route('dashboard.analyze.send-service-c') }}";
	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

	const dateFromInput = document.getElementById('dateFrom');
	const dateToInput = document.getElementById('dateTo');
	const openDateFromPickerBtn = document.getElementById('openDateFromPickerBtn');
	const openDateToPickerBtn = document.getElementById('openDateToPickerBtn');

	const runAnalyzeBtn = document.getElementById('runAnalyzeBtn');
	const sendServiceCBtn = document.getElementById('sendServiceCBtn');
	const clearBtn = document.getElementById('clearBtn');
	const rangeHintEl = document.getElementById('rangeHint');
	const statusEl = document.getElementById('status');

	const mIncome = document.getElementById('mIncome');
	const mExpense = document.getElementById('mExpense');
	const mNet = document.getElementById('mNet');
	const mCount = document.getElementById('mCount');
	const mTop = document.getElementById('mTop');
	const mSavings = document.getElementById('mSavings');
	const mRange = document.getElementById('mRange');
	const summaryText = document.getElementById('summaryText');
	const insightText = document.getElementById('insightText');
	const breakdownEl = document.getElementById('breakdown');
	const executedAtEl = document.getElementById('executedAt');

	const MAX_RANGE_DAYS = 366;
	let latestPayload = null;

	function asNumber(value, fallback = 0) {
		const numberValue = Number(value);
		return Number.isFinite(numberValue) ? numberValue : fallback;
	}

	function formatCurrency(value) {
		const numberValue = asNumber(value, NaN);

		if (!Number.isFinite(numberValue)) {
			return '-';
		}

		return new Intl.NumberFormat('id-ID', {
			style: 'currency',
			currency: 'IDR',
			maximumFractionDigits: 0,
		}).format(numberValue);
	}

	function formatPercent(value) {
		const numberValue = asNumber(value, NaN);

		if (!Number.isFinite(numberValue)) {
			return '-';
		}

		return numberValue.toFixed(2) + '%';
	}

	function formatDateTime(value) {
		const parsed = new Date(String(value || ''));

		if (Number.isNaN(parsed.getTime())) {
			return '-';
		}

		return new Intl.DateTimeFormat('id-ID', {
			dateStyle: 'medium',
			timeStyle: 'short',
		}).format(parsed);
	}

	function toYmdLocal(date) {
		const year = date.getFullYear();
		const month = String(date.getMonth() + 1).padStart(2, '0');
		const day = String(date.getDate()).padStart(2, '0');
		return year + '-' + month + '-' + day;
	}

	function getTodayDateString() {
		return toYmdLocal(new Date());
	}

	function calculateRangeDays(dateFrom, dateTo) {
		if (dateFrom === '' || dateTo === '') {
			return null;
		}

		const start = new Date(dateFrom + 'T00:00:00');
		const end = new Date(dateTo + 'T00:00:00');

		if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end < start) {
			return null;
		}

		return Math.floor((end.getTime() - start.getTime()) / 86400000) + 1;
	}

	function formatRangeLabel(dateFrom, dateTo) {
		if (dateFrom === '' && dateTo === '') {
			return 'Semua data';
		}

		const resolvedFrom = dateFrom !== '' ? dateFrom : 'awal data';
		const resolvedTo = dateTo !== '' ? dateTo : 'hari ini';
		return resolvedFrom + ' - ' + resolvedTo;
	}

	function resolveMetrics(result) {
		if (typeof result === 'object' && result !== null && typeof result.metrics === 'object' && result.metrics !== null) {
			return result.metrics;
		}

		if (typeof result === 'object' && result !== null && typeof result.analysis === 'object' && result.analysis !== null) {
			return result.analysis;
		}

		return {};
	}

	function resolveBreakdownItems(result, metrics) {
		if (typeof result === 'object' && result !== null && Array.isArray(result.category_breakdown)) {
			return result.category_breakdown;
		}

		const breakdownMap = (typeof metrics === 'object' && metrics !== null && typeof metrics.category_breakdown === 'object' && metrics.category_breakdown !== null)
			? metrics.category_breakdown
			: null;

		if (!breakdownMap) {
			return [];
		}

		const totalExpense = asNumber((typeof metrics === 'object' && metrics !== null) ? metrics.total_expense : 0, 0);

		return Object.entries(breakdownMap)
			.map(([category, percentageRaw]) => {
				const percentage = asNumber(percentageRaw, 0);
				const amount = totalExpense > 0 ? (totalExpense * percentage) / 100 : 0;

				return {
					category,
					amount,
					percentage,
				};
			});
	}

	function resolveInsight(result, metrics) {
		if (typeof result === 'object' && result !== null && typeof result.ai_insight === 'string') {
			return result.ai_insight;
		}

		if (typeof result === 'object' && result !== null && typeof result.ai_insight === 'object' && result.ai_insight !== null) {
			return String(result.ai_insight.text || '').trim();
		}

		if (typeof metrics === 'object' && metrics !== null && typeof metrics.insight === 'string') {
			return metrics.insight;
		}

		return '';
	}

	function setStatus(type, message) {
		statusEl.classList.remove('info', 'ok', 'error');
		statusEl.classList.add(type);
		statusEl.textContent = message;
	}

	function syncDateConstraints() {
		const today = getTodayDateString();
		dateFromInput.max = today;
		dateToInput.max = today;

		if (dateFromInput.value !== '') {
			dateToInput.min = dateFromInput.value;

			if (dateToInput.value !== '' && dateToInput.value < dateFromInput.value) {
				dateToInput.value = dateFromInput.value;
			}
		} else {
			dateToInput.min = '';
		}
	}

	function applyDefaultLast30Days() {
		const now = new Date();
		const start = new Date(now);
		start.setDate(start.getDate() - 29);

		dateFromInput.value = toYmdLocal(start);
		dateToInput.value = toYmdLocal(now);
		syncDateConstraints();
	}

	function updateRangeHint() {
		const dateFrom = String(dateFromInput.value || '').trim();
		const dateTo = String(dateToInput.value || '').trim();
		const days = calculateRangeDays(dateFrom, dateTo);

		if (dateFrom !== '' && dateTo !== '' && days === null) {
			rangeHintEl.textContent = 'Rentang tanggal tidak valid. Pastikan tanggal selesai >= tanggal mulai.';
			return;
		}

		if (days !== null && days > MAX_RANGE_DAYS) {
			rangeHintEl.textContent = 'Rentang melebihi 366 hari. Silakan pendekkan rentang.';
			return;
		}

		rangeHintEl.textContent = 'Rentang aktif: ' + formatRangeLabel(dateFrom, dateTo);
		mRange.textContent = formatRangeLabel(dateFrom, dateTo);
	}

	function renderBreakdown(items) {
		if (!Array.isArray(items) || items.length === 0) {
			breakdownEl.innerHTML = '<div class="bar-item"><div class="bar-head"><span>Belum ada data</span><span>0%</span></div><div class="bar-track"><div class="bar-fill" style="width:0%"></div></div></div>';
			return;
		}

		breakdownEl.innerHTML = items
			.map((item) => {
				const category = String(item?.category || 'uncategorized');
				const amountRaw = item?.amount;
				const amount = asNumber(amountRaw, NaN);
				const percentage = Math.max(0, Math.min(100, asNumber(item?.percentage)));
				const amountLabel = Number.isFinite(amount)
					? category + ' (' + formatCurrency(amount) + ')'
					: category;

				return '<div class="bar-item">'
					+ '<div class="bar-head"><span>' + amountLabel + '</span><span>' + percentage.toFixed(2) + '%</span></div>'
					+ '<div class="bar-track"><div class="bar-fill" style="width:' + percentage.toFixed(2) + '%"></div></div>'
					+ '</div>';
			})
			.join('');
	}

	function buildServiceCPayload(result) {
		const source = (typeof result.source === 'object' && result.source !== null) ? result.source : {};
		const metrics = resolveMetrics(result);
		const aiInsight = resolveInsight(result, metrics);
		const breakdownItems = resolveBreakdownItems(result, metrics);

		let insightTextValue = '';

		if (typeof aiInsight === 'string') {
			insightTextValue = aiInsight.trim();
		}

		if (insightTextValue === '') {
			insightTextValue = String(metrics.summary || '').trim();
		}

		return {
			message: String(result.message || 'Analisis dari FinLyzer.'),
			source_sync: {
				user_id: source.user_id,
				user_email: String(source.user_email || '').trim(),
				keycloak_sub: String(source.user_keycloak_sub || '').trim(),
				fetched_transactions: asNumber(source.fetched_transactions, asNumber(metrics.transaction_count)),
				next_since: source.next_since || null,
			},
			metrics: {
				total_income: asNumber(metrics.total_income),
				total_expense: asNumber(metrics.total_expense),
				transaction_count: asNumber(metrics.transaction_count),
				top_category: String(metrics.top_category || ''),
				savings_rate: asNumber(metrics.savings_rate),
				financial_health: String(metrics.financial_health || ''),
				summary: String(metrics.summary || ''),
			},
			category_breakdown: breakdownItems,
			ai_insight: insightTextValue,
		};
	}

	function renderResult(result) {
		const source = (typeof result.source === 'object' && result.source !== null) ? result.source : {};
		const metrics = resolveMetrics(result);
		const breakdownItems = resolveBreakdownItems(result, metrics);
		const resolvedInsight = resolveInsight(result, metrics);

		const income = asNumber(metrics.total_income);
		const expense = asNumber(metrics.total_expense);
		const net = Number.isFinite(Number(metrics.net_balance))
			? asNumber(metrics.net_balance)
			: income - expense;

		mIncome.textContent = formatCurrency(income);
		mExpense.textContent = formatCurrency(expense);
		mNet.textContent = formatCurrency(net);
		mCount.textContent = String(asNumber(metrics.transaction_count, 0));
		mTop.textContent = String(metrics.top_category || '-');
		mSavings.textContent = formatPercent(metrics.savings_rate);

		const selectedDateFrom = String(dateFromInput.value || '').trim();
		const selectedDateTo = String(dateToInput.value || '').trim();
		const resultDateFrom = String(source.date_from || selectedDateFrom).trim();
		const resultDateTo = String(source.date_to || selectedDateTo).trim();
		mRange.textContent = formatRangeLabel(resultDateFrom, resultDateTo);

		summaryText.textContent = String(metrics.summary || 'Belum ada ringkasan.');

		if (resolvedInsight !== '') {
			insightText.textContent = resolvedInsight;
		} else {
			insightText.textContent = 'Belum ada insight.';
		}

		renderBreakdown(breakdownItems);

		const executedAt = String(result.executed_at || source.executed_at || '').trim();
		executedAtEl.textContent = formatDateTime(executedAt !== '' ? executedAt : new Date().toISOString());

		latestPayload = buildServiceCPayload(result);
	}

	async function requestAnalyze() {
		const dateFrom = String(dateFromInput.value || '').trim();
		const dateTo = String(dateToInput.value || '').trim();
		const rangeDays = calculateRangeDays(dateFrom, dateTo);

		if (dateFrom !== '' && dateTo !== '' && rangeDays === null) {
			setStatus('error', 'Tanggal selesai harus sama atau setelah tanggal mulai.');
			return;
		}

		if (rangeDays !== null && rangeDays > MAX_RANGE_DAYS) {
			setStatus('error', 'Rentang tanggal maksimal 366 hari.');
			return;
		}

		runAnalyzeBtn.disabled = true;
		setStatus('info', 'Menjalankan analisis...');

		const payload = {};

		if (dateFrom !== '') {
			payload.date_from = dateFrom;
		}

		if (dateTo !== '') {
			payload.date_to = dateTo;
		}

		try {
			const response = await fetch(runEndpoint, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Accept': 'application/json',
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': csrfToken,
				},
				body: JSON.stringify(payload),
			});

			const contentType = response.headers.get('content-type') || '';
			const responseBody = contentType.includes('application/json')
				? await response.json()
				: { message: await response.text() };

			if (!response.ok) {
				const message = String(responseBody.message || 'Gagal menjalankan analisis.');
				throw new Error(message);
			}

			renderResult(responseBody);
			setStatus('ok', String(responseBody.message || 'Analisis berhasil dijalankan.'));
		} catch (error) {
			const message = error instanceof Error ? error.message : 'Terjadi kesalahan saat analisis.';
			setStatus('error', message);
		} finally {
			runAnalyzeBtn.disabled = false;
		}
	}

	async function requestSendServiceC() {
		if (!latestPayload) {
			setStatus('error', 'Jalankan analisis dulu sebelum mengirim ke FinGoals.');
			return;
		}

		sendServiceCBtn.disabled = true;
		setStatus('info', 'Mengirim payload ke FinGoals...');

		try {
			const response = await fetch(sendServiceCEndpoint, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Accept': 'application/json',
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': csrfToken,
				},
				body: JSON.stringify({ payload: latestPayload }),
			});

			const contentType = response.headers.get('content-type') || '';
			const responseBody = contentType.includes('application/json')
				? await response.json()
				: { message: await response.text() };

			if (!response.ok) {
				const message = String(responseBody.message || 'Gagal mengirim payload ke FinGoals.');
				throw new Error(message);
			}

			setStatus('ok', String(responseBody.message || 'Payload berhasil dikirim ke FinGoals.'));
		} catch (error) {
			const message = error instanceof Error ? error.message : 'Terjadi kesalahan saat mengirim payload.';
			setStatus('error', message);
		} finally {
			sendServiceCBtn.disabled = false;
		}
	}

	function resetDashboard() {
		latestPayload = null;
		applyDefaultLast30Days();
		updateRangeHint();

		mIncome.textContent = '-';
		mExpense.textContent = '-';
		mNet.textContent = '-';
		mCount.textContent = '-';
		mTop.textContent = '-';
		mSavings.textContent = '-';
		summaryText.textContent = 'Belum ada ringkasan.';
		insightText.textContent = 'Belum ada insight.';
		renderBreakdown([]);
		executedAtEl.textContent = '-';

		setStatus('info', 'Dashboard direset ke rentang 30 hari terakhir.');
	}

	function attachPickerBehavior(input, button) {
		const canShowPicker = typeof input.showPicker === 'function';

		if (canShowPicker) {
			input.setAttribute('inputmode', 'none');
			input.addEventListener('keydown', (event) => {
				if (event.key === 'Tab' || event.key === 'Escape') {
					return;
				}

				event.preventDefault();
			});
			input.addEventListener('paste', (event) => event.preventDefault());
			input.addEventListener('drop', (event) => event.preventDefault());
		}

		button.addEventListener('click', () => {
			if (canShowPicker) {
				try {
					input.showPicker();
					return;
				} catch (error) {
					// Fallback to focus when picker cannot open.
				}
			}

			input.focus();
		});
	}

	dateFromInput.addEventListener('change', () => {
		syncDateConstraints();
		updateRangeHint();
	});

	dateToInput.addEventListener('change', updateRangeHint);
	runAnalyzeBtn.addEventListener('click', requestAnalyze);
	sendServiceCBtn.addEventListener('click', requestSendServiceC);
	clearBtn.addEventListener('click', resetDashboard);

	attachPickerBehavior(dateFromInput, openDateFromPickerBtn);
	attachPickerBehavior(dateToInput, openDateToPickerBtn);

	applyDefaultLast30Days();
	updateRangeHint();
</script>
</body>
</html>
