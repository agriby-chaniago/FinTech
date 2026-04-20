<x-app-layout>
    <div class="mx-auto py-10 md:py-12 px-4 md:px-6 max-w-7xl font-sans animate-fadeIn">

        <h2 class="fin-title text-xl md:text-2xl mb-4 pl-2">Riwayat Transaksi</h2>

        {{-- Simpan data transaksi secara aman di sini --}}
        <script id="transactions-data" type="application/json">
            {!! json_encode($transactions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
        </script>

        <div
            x-data="{
                search: '',
                transactions: [],

                normalizeDescription(transaction) {
                    return (transaction.description ?? transaction.deskripsi ?? '').toString();
                },
                normalizeType(transaction) {
                    if (transaction.type) return transaction.type;
                    if (transaction.kategori === 'pemasukan') return 'income';
                    if (transaction.kategori === 'pengeluaran') return 'expense';

                    return '';
                },
                normalizeTransactionCategory(transaction) {
                    const type = this.normalizeType(transaction);

                    if (type === 'income') {
                        return 'pemasukan';
                    }

                    if (type === 'expense') {
                        return 'pengeluaran';
                    }

                    return (transaction.kategori ?? '').toString().toLowerCase();
                },
                normalizeAiCategory(transaction) {
                    const value = (transaction.category ?? '').toString().trim();

                    return value !== '' ? value : 'lainnya';
                },
                normalizeAmount(transaction) {
                    return Number(transaction.amount ?? transaction.nominal ?? 0);
                },
                normalizeDate(transaction) {
                    return transaction.transaction_date ?? transaction.tanggal ?? '-';
                },
                formatDate(value) {
                    if (!value || value === '-') {
                        return '-';
                    }

                    const parsed = new Date(value);

                    if (!Number.isNaN(parsed.getTime())) {
                        return new Intl.DateTimeFormat('id-ID', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric'
                        }).format(parsed);
                    }

                    const fallback = value.toString();

                    return fallback.includes('T') ? fallback.split('T')[0] : fallback;
                },

                get filteredTransactions() {
                    const keyword = this.search.toLowerCase();

                    return this.transactions.filter(transaction => {
                        const description = this.normalizeDescription(transaction).toLowerCase();
                        const transactionCategory = this.normalizeTransactionCategory(transaction).toLowerCase();
                        const aiCategory = this.normalizeAiCategory(transaction).toLowerCase();
                        const type = this.normalizeType(transaction).toLowerCase();

                        return description.includes(keyword)
                            || transactionCategory.includes(keyword)
                            || aiCategory.includes(keyword)
                            || type.includes(keyword);
                    });
                },
                formatCurrency(value) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                },
                formatTransactionCategory(transaction) {
                    const category = this.normalizeTransactionCategory(transaction);

                    if (category === '') {
                        return '-';
                    }

                    if (category === 'pemasukan') {
                        return 'Pemasukan';
                    }

                    if (category === 'pengeluaran') {
                        return 'Pengeluaran';
                    }

                    return category.charAt(0).toUpperCase() + category.slice(1);
                },
                formatAiCategory(transaction) {
                    const category = this.normalizeAiCategory(transaction);

                    return category
                        .split(' ')
                        .filter(Boolean)
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ');
                },
                transactionCategoryColorClass(transaction) {
                    const type = this.normalizeType(transaction);

                    if (type === 'income') {
                        return 'text-ctp-green';
                    }

                    if (type === 'expense') {
                        return 'text-ctp-red';
                    }

                    return 'text-platinum';
                },
                aiCategoryColorClass(transaction) {
                    const type = this.normalizeType(transaction);

                    if (type === 'income') {
                        return 'text-ctp-sky';
                    }

                    if (type === 'expense') {
                        return 'text-ctp-yellow';
                    }

                    return 'text-platinum/70';
                },
                init() {
                    const parsed = JSON.parse(document.getElementById('transactions-data').textContent);
                    this.transactions = Array.isArray(parsed) ? parsed : [];
                }
            }"
            x-init="init()"
            class="fin-surface-card p-5 md:p-6 mt-3 md:mt-4 text-platinum"
        >
            <input
                type="text"
                x-model="search"
                placeholder="Cari deskripsi atau kategori..."
                class="mb-4 w-full md:max-w-md rounded-lg border border-raisin3 bg-transparent px-4 py-2.5 text-sm md:text-base text-platinum placeholder-ctp-overlay1 focus:outline-none focus:ring-2 focus:ring-byzantine focus:border-byzantine transition"
            />

            <div class="fin-scroll-x -mx-2 px-2 md:mx-0 md:px-0">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-raisin3/70">
                        <th class="fin-table-head">Tanggal</th>
                        <th class="fin-table-head">Kategori Transaksi</th>
                        <th class="fin-table-head">Kategori AI (Groq)</th>
                        <th class="fin-table-head">Nominal</th>
                        <th class="fin-table-head">Deskripsi</th>
                        <th class="fin-table-head text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="transaction in filteredTransactions" :key="transaction.id">
                        <tr class="border-b border-raisin3/50 hover:bg-raisin transition-colors duration-200">
                            <td class="fin-table-cell" x-text="formatDate(normalizeDate(transaction))"></td>
                            <td class="fin-table-cell">
                                <span
                                    :class="transactionCategoryColorClass(transaction)"
                                    x-text="formatTransactionCategory(transaction)"
                                ></span>
                            </td>
                            <td class="fin-table-cell">
                                <span
                                    :class="aiCategoryColorClass(transaction)"
                                    x-text="formatAiCategory(transaction)"
                                ></span>
                            </td>
                            <td class="fin-table-cell font-mono whitespace-nowrap" x-text="formatCurrency(normalizeAmount(transaction))"></td>
                            <td class="fin-table-cell max-w-xs md:max-w-md break-words" x-text="normalizeDescription(transaction)"></td>
                            <td class="fin-table-cell text-center whitespace-nowrap space-x-2">
                                <a
                                    :href="`/transactions/${transaction.id}/edit`"
                                    class="inline-flex items-center rounded-md border border-ctp-yellow/35 px-2 py-1 text-xs font-medium text-ctp-yellow hover:bg-ctp-yellow/10 transition"
                                >Edit</a>
                                <form :action="`/transactions/${transaction.id}`" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Yakin hapus transaksi ini?')"
                                        class="inline-flex items-center rounded-md border border-ctp-red/35 px-2 py-1 text-xs font-medium text-ctp-red hover:bg-ctp-red/10 transition"
                                    >Hapus</button>
                                </form>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredTransactions.length === 0">
                        <tr>
                            <td colspan="6" class="py-6 text-center text-platinum/70 italic">
                                Tidak ada transaksi ditemukan.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</x-app-layout>
