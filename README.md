# Sistem Informasi Patung Bali

Website informasi patung di persimpangan dan ruang publik Bali, dua bahasa (Indonesia dan Inggris).
Proyek akhir mata kuliah Warisan Budaya Digital.

Laravel 13 · Blade · Tailwind CSS 4 · Leaflet + OpenStreetMap · SQLite (bisa diganti MySQL lewat `.env`)

## Menjalankan di komputer sendiri

Butuh PHP 8.3+, Composer, dan Node.js.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

Buka http://127.0.0.1:8000. Selama mengubah tampilan, jalankan `npm run dev` di terminal lain.

## Alur data

Data patung tidak diinput lewat website. Sumbernya file CSV `database/data/statues.csv`, yang dibuat dari Google Sheet tim.

1. Perbarui `database/data/statues.csv`.
2. Jalankan `php artisan patung:import` (atau `php artisan migrate:fresh --seed` untuk mengisi ulang semuanya).

Importer memeriksa seluruh baris dulu dan tidak menulis apa pun kalau ada satu baris yang salah. Yang diperiksa:
koordinat harus berada di Bali, kabupaten harus salah satu dari 9 kabupaten/kota, jenis persimpangan harus memakai
kosakata baku, nomor tidak boleh ganda, dan tautan referensi harus `http`/`https`.

CSV adalah satu-satunya sumber data: patung yang nomornya tidak ada lagi di CSV akan dihapus dari database saat impor.
File tanpa baris data ditolak supaya situs tidak kosong karena salah file.

### Kolom CSV

| Kolom | Isi |
|---|---|
| `no` | Nomor baris di Google Sheet. Dipakai sebagai kunci, jadi impor ulang memperbarui data, bukan menggandakan. |
| `name` | Nama patung |
| `regency` | Kabupaten/kota, misalnya `Badung` atau `Denpasar` |
| `district`, `village` | Kecamatan dan desa/kelurahan |
| `latitude`, `longitude` | Koordinat desimal |
| `themes` | Tema final dari informan, ditampilkan ke publik. Slug atau nama tema dipisah `;` atau `+`, misalnya `tokoh-babad; monumen-tokoh-sejarah`. Kosongkan sampai klasifikasi informan selesai. |
| `proposed_themes` | Usulan tema tim yang ditunjukkan ke informan, format sama dengan `themes` |
| `proposal_note` | Dasar usulan (maks. 300 karakter), misalnya menit rekaman wawancara |
| `junction_type` | `Perempatan`, `Pertigaan`, `Bundaran`, `Simpang`, masing-masing boleh diberi `(N arah)`; `Bukan persimpangan (pinggir jalan)`, `Bukan persimpangan (di dalam kawasan)`, atau `Perlu verifikasi` |
| `description_id`, `description_en` | Deskripsi (boleh kosong). Jika versi Inggris kosong, halaman Inggris menampilkan versi Indonesia beserta pemberitahuan. |
| `sources` | `Judul [https://...]; Judul lain [https://...]` |
| `photos` | Nama file di `public/images/patung/`, dipisah titik koma, misalnya `021-1.jpg;021-2.jpg` |

## Foto dan Street View

- Foto patung disimpan di `public/images/patung/` (lebar maksimal 1280 px). Setelah menambah foto, jalankan
  `php artisan patung:thumbnails` untuk membuat versi kecil (640 px) di `public/images/patung/thumbs/`, yang dipakai
  kartu, beranda, dan popup peta.
- Bagian Street View membuka Google Maps di tab baru. Untuk menampilkan panorama 360° langsung di halaman, isi
  `GOOGLE_MAPS_EMBED_KEY` dengan API key Google yang mengaktifkan Maps Embed API (gratis tanpa batas) dan dibatasi
  ke domain website.

## Klasifikasi tema oleh informan

Semua patung dipandang sebagai patung landmark. Informan budaya memilih tema tiap patung langsung di website, boleh
lebih dari satu: Itihasa, Monumen · Tokoh Sejarah, Monumen · Tugu, Tokoh Babad (Folklor), Ikon Daerah,
Figur/Kanda, Religi (daftarnya di `ReferenceDataSeeder`). Ada juga catatan bebas. Semua tersimpan otomatis, dan
penyimpanan yang gagal karena koneksi dicoba ulang sendiri.

- Pilihan informan disimpan di database terpisah (koneksi `reviews`): Supabase di produksi lewat `REVIEWS_DB_URL`,
  `database/reviews.sqlite` di komputer lokal. Kuncinya kolom `no`, jadi impor ulang CSV tidak menyentuh hasil klasifikasi.
- Mode informan aktif lewat tautan rahasia `/id/validasi/masuk/{EXPERT_ACCESS_TOKEN}`. Tanpa itu, halaman validasi menjawab 404
  dan pengunjung biasa tidak melihat panel maupun usulan tim.
- Tim mengunduh hasil sebagai Excel dari `/ekspor-validasi/{EXPORT_ACCESS_TOKEN}`.
- Usulan tim diisi dari kolom `proposed_themes` dan `proposal_note` di CSV. Setelah informan selesai, salin
  tema dari ekspor ke kolom `themes` agar tampil di halaman publik.
- Jangan memakai ulang nomor (`no`) untuk patung lain; patung pengganti diberi nomor baru. Ekspor memberi
  peringatan bila nama patung pada suatu nomor berubah sejak diklasifikasi.
- Mengganti `EXPERT_ACCESS_TOKEN` langsung mengakhiri semua sesi informan yang sedang aktif.
- Skema tabel produksi dibuat lewat migrasi Supabase yang sama isinya dengan
  `database/migrations/reviews/`. Untuk lokal: `php artisan migrate --database=reviews --path=database/migrations/reviews`.

## Struktur URL

- `/` mengarahkan ke `/id` atau `/en` sesuai bahasa browser.
- `/{bahasa}` beranda, `/{bahasa}/patung` daftar dan filter, `/{bahasa}/patung/{slug}` detail,
  `/{bahasa}/peta` peta, `/{bahasa}/tentang` tentang proyek.

Teks antarmuka ditulis dalam Bahasa Indonesia di Blade (`__('...')`), terjemahan Inggrisnya di `lang/en.json`.
Tes `TranslationCoverageTest` gagal kalau ada teks baru yang belum diterjemahkan.

## Tes

```bash
php artisan test
```

## Atribusi

Peta © kontributor [OpenStreetMap](https://www.openstreetmap.org/copyright) (ODbL).
