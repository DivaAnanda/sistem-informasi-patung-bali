<h1 class="font-display text-3xl font-semibold sm:text-4xl">Tentang proyek</h1>
<p class="mt-5 text-lg leading-relaxed text-stone-700">
    Patung Bali adalah dokumentasi digital patung-patung yang berdiri di persimpangan dan ruang publik Pulau Bali.
    Website ini merupakan proyek akhir mata kuliah Warisan Budaya Digital.
</p>

<div class="mt-12 space-y-12 leading-relaxed text-stone-800">
    <section>
        <h2 class="font-display text-2xl font-semibold text-ink">Mengapa patung?</h2>
        <p class="mt-3">
            Patung persimpangan di Bali umumnya mengangkat tokoh Ramayana, Mahabharata, sejarah, dan budaya lokal. Bagi
            warga, patung-patung ini adalah penanda arah. Bagi pengunjung, patung menjadi pintu masuk untuk mengenal cerita
            yang hidup di Bali. Namun informasi tentang tokoh, tahun pembangunan, dan pematungnya tersebar di berita, jurnal,
            dan dokumen pemerintah. Kami mengumpulkannya di satu tempat.
        </p>
    </section>

    <section>
        <h2 class="font-display text-2xl font-semibold text-ink">Patung landmark dan temanya</h2>
        <p class="mt-3">
            Semua patung di website ini kami pandang sebagai <strong>patung landmark (ikonik)</strong>: penanda kawasan di
            ruang publik. Patung-patung ini lalu dibedakan menurut <strong>tema perwujudannya</strong>. Satu patung bisa
            memiliki lebih dari satu tema.
        </p>
        <dl class="mt-4 space-y-3">
            @foreach ($themes as $theme)
                <div>
                    <dt class="font-semibold">{{ $theme->getTranslation('name', 'id') }}</dt>
                    <dd class="text-stone-700">{{ $theme->getTranslation('description', 'id') }}</dd>
                </div>
            @endforeach
        </dl>
        <p class="mt-4 text-sm text-stone-600">
            Struktur tema ini disusun bersama informan budaya <strong>Dewa Gede Purwita, S.Pd., M.Sn.</strong>, dosen
            Institut Desain dan Bisnis Bali (IDB Bali), dalam wawancara pada 20 November 2026.
        </p>
    </section>

    <section>
        <h2 class="font-display text-2xl font-semibold text-ink">Patung dan penjaga persimpangan</h2>
        <p class="mt-3">
            Menurut lontar Tutur Gong Besi, setiap persimpangan memiliki penjaga ruangnya sendiri:
            <em>Sang Hyang Catur Bhuwana</em> di perempatan dan <em>Sang Hyang Sapuh Jagat</em> di pertigaan. Sesajen
            yang dihaturkan warga di persimpangan ditujukan kepada penjaga ruang itu, bukan kepada patungnya. Karena itu
            patung di persimpangan, termasuk yang telah melalui upacara pasupati, tidak menjadi objek pemujaan utama,
            melainkan penanda kawasan.
        </p>
    </section>

    <section>
        <h2 class="font-display text-2xl font-semibold text-ink">Cara data dikumpulkan</h2>
        <ol class="mt-3 list-decimal space-y-2 pl-5 marker:text-stone-400">
            <li>Penelusuran sumber sekunder: berita, jurnal ilmiah, dan dokumen resmi seperti Rencana Strategis DLHK Kabupaten Badung 2021–2026.</li>
            <li>Penentuan titik koordinat setiap patung dan pengecekan silang dengan citra peta.</li>
            <li>Penentuan jenis persimpangan dari data jalan OpenStreetMap, dicocokkan dengan sumber tertulis.</li>
            <li>Survei lapangan untuk memotret patung beserta jalan di sekitarnya.</li>
            <li>Klasifikasi tema setiap patung bersama informan budaya.</li>
        </ol>
    </section>

    <section>
        <h2 class="font-display text-2xl font-semibold text-ink">Jenis persimpangan</h2>
        <dl class="mt-3 space-y-3">
            <div><dt class="font-semibold">Perempatan</dt><dd class="text-stone-700">Titik pertemuan empat ruas jalan.</dd></div>
            <div><dt class="font-semibold">Pertigaan</dt><dd class="text-stone-700">Titik pertemuan tiga ruas jalan.</dd></div>
            <div><dt class="font-semibold">Bundaran</dt><dd class="text-stone-700">Persimpangan melingkar dengan patung di tengahnya. Jumlah arahnya dicantumkan di halaman patung.</dd></div>
            <div><dt class="font-semibold">Simpang banyak</dt><dd class="text-stone-700">Pertemuan lima ruas jalan atau lebih.</dd></div>
            <div><dt class="font-semibold">Pinggir jalan</dt><dd class="text-stone-700">Patung di tepi jalan, bukan di titik pertemuan jalan.</dd></div>
            <div><dt class="font-semibold">Di dalam kawasan</dt><dd class="text-stone-700">Patung di dalam kawasan wisata, taman, atau kompleks tertentu.</dd></div>
        </dl>
    </section>

    <section>
        <h2 class="font-display text-2xl font-semibold text-ink">Sumber dan atribusi</h2>
        <p class="mt-3">
            Peta memakai data © kontributor OpenStreetMap dengan lisensi ODbL. Setiap halaman patung mencantumkan sumber
            rujukannya. Klasifikasi tema berdasarkan wawancara dengan informan Dewa Gede Purwita, S.Pd., M.Sn. Data masih
            terus bertambah seiring pengumpulan oleh tim.
        </p>
    </section>
</div>
