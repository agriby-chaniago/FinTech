<x-app-layout>
    <div class="mx-auto py-8 md:py-10 px-5 md:px-8 text-platinum font-sans animate-fadeIn max-w-3xl fin-surface-card">
        <p class="fin-kicker mb-2">Transaction</p>
        <h2 class="fin-title text-2xl md:text-3xl mb-2 text-byzantine">Edit Transaksi</h2>
        <p class="fin-subtitle max-w-2xl mb-8">Perbarui detail transaksi agar kategori dan insight AI tetap akurat.</p>

        {{-- Notifikasi sukses --}}
        @if (session('success'))
        <div class="border border-ctp-green/40 bg-ctp-green/15 text-ctp-green p-4 mb-8 rounded-lg text-sm leading-relaxed">
            {{ session('success') }}
        </div>
        @endif

        {{-- Error Validation --}}
        @if ($errors->any())
        <div class="border border-ctp-red/40 bg-ctp-red/15 text-ctp-red p-4 mb-8 rounded-lg text-sm leading-relaxed">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('transactions.update', $transaction->id) }}" method="POST" class="space-y-6 md:space-y-7">
            @csrf
            @method('PUT')

            <div>
                <label for="nominal" class="block mb-2 font-semibold text-platinum/90 text-xs uppercase tracking-[0.12em]">Nominal (Rp)</label>
                <input
                    type="number"
                    id="nominal"
                    name="nominal"
                    step="100"
                    placeholder="Masukkan nominal"
                    value="{{ old('nominal', $transaction->nominal) }}"
                    required
                          class="w-full rounded-lg border border-raisin3 bg-transparent px-4 py-3 text-sm md:text-base text-platinum placeholder-ctp-overlay1
                 focus:outline-none focus:ring-2 focus:ring-byzantine focus:border-byzantine transition">
            </div>

            <div>
                <label for="kategori" class="block mb-2 font-semibold text-platinum/90 text-xs uppercase tracking-[0.12em]">Kategori</label>
                <select
                    id="kategori"
                    name="kategori"
                    required
                    class="w-full rounded-lg border border-raisin3 bg-transparent px-4 py-3 text-sm md:text-base text-platinum
                 focus:outline-none focus:ring-2 focus:ring-byzantine focus:border-byzantine transition">
                    <option value="" disabled {{ old('kategori', $transaction->kategori) ? '' : 'selected' }} class="text-ctp-overlay1 bg-raisin">Pilih Kategori</option>
                    <option value="pemasukan" {{ old('kategori', $transaction->kategori) == 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="pengeluaran" {{ old('kategori', $transaction->kategori) == 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
            </div>

            <div>
                <label for="tanggal" class="block mb-2 font-semibold text-platinum/90 text-xs uppercase tracking-[0.12em]">Tanggal</label>
                <input
                    type="date"
                    id="tanggal"
                    name="tanggal"
                    value="{{ old('tanggal', \Carbon\Carbon::parse($transaction->tanggal)->format('Y-m-d')) }}"
                    required
                          class="w-full rounded-lg border border-raisin3 bg-transparent px-4 py-3 text-sm md:text-base text-platinum
                 focus:outline-none focus:ring-2 focus:ring-byzantine focus:border-byzantine transition">
            </div>

            <div>
                <label for="deskripsi" class="block mb-2 font-semibold text-platinum/90 text-xs uppercase tracking-[0.12em]">Deskripsi</label>
                <input
                    type="text"
                    id="deskripsi"
                    name="deskripsi"
                    placeholder="Masukkan deskripsi"
                    value="{{ old('deskripsi', $transaction->deskripsi) }}"
                          class="w-full rounded-lg border border-raisin3 bg-transparent px-4 py-3 text-sm md:text-base text-platinum placeholder-ctp-overlay1
                 focus:outline-none focus:ring-2 focus:ring-byzantine focus:border-byzantine transition">
            </div>

            <div>
                <label for="category" class="block mb-2 font-semibold text-platinum/90 text-xs uppercase tracking-[0.12em]">Kategori AI (Groq)</label>
                <input
                    type="text"
                    id="category"
                    name="category"
                    placeholder="Contoh: makan, tagihan, gaji, uang saku"
                    value="{{ old('category', $transaction->category) }}"
                    class="w-full rounded-lg border border-raisin3 bg-transparent px-4 py-3 text-sm md:text-base text-platinum placeholder-ctp-overlay1
                 focus:outline-none focus:ring-2 focus:ring-byzantine focus:border-byzantine transition">
                <p class="mt-2 text-sm text-platinum/70">
                    Bisa diedit manual untuk pemasukan maupun pengeluaran.
                </p>
            </div>

            <div class="flex justify-end pt-2">
                <button
                    type="submit"
                    class="w-full sm:w-auto rounded-lg bg-byzantine px-8 py-3 font-semibold text-night hover:bg-byzantine-hover transition duration-300">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
