# Deploy ke Google Cloud Run

Panduan ini memindahkan website ke Cloud Run tanpa mengubah kode. Cloud Run menjalankan image Docker yang sama
dengan Vercel (`Dockerfile.vercel`). Database dan foto tetap di Supabase.

## Kenapa Cloud Run, dan berapa biayanya

- Instance hanya ditagih **selama ada request yang diproses**. Begitu request selesai, CPU dan memori berhenti dihitung.
- Memori bisa diatur sendiri. Kita pakai 512 MB; uji lokal menunjukkan aplikasi memakai sekitar 63 MB.
- Kuota gratis per bulan (mode tagihan per request): 180.000 vCPU-detik, 360.000 GiB-detik, dan 2 juta request.
  Dengan 512 MB, itu setara sekitar 200 jam request yang sedang berjalan. Website ini jauh di bawahnya.
- Biaya kecil yang mungkin muncul: penyimpanan image di Artifact Registry (image sekitar 1,2 GB, gratis 0,5 GB,
  sisanya sekitar US$0,10 per GB per bulan). Hapus image lama supaya tetap kecil.
- Akun Google Cloud **wajib punya metode pembayaran** (kartu) walaupun pemakaiannya masuk kuota gratis. Akun baru
  biasanya mendapat kredit uji coba.

Langkah 1.4 memasang alarm anggaran supaya kalian langsung tahu kalau ada biaya.

## 1. Siapkan akun dan project

Kita memakai **project yang sama dengan kunci Google Maps (Street View)**. Project itu sudah punya billing, jadi
langkah kartu tidak perlu diulang.

1. Buka <https://console.cloud.google.com> dengan akun Google yang dulu membuat kunci Maps.
2. Pilih project tempat kunci Maps berada (menu project di kiri atas). Catat **Project ID**-nya, misalnya
   `patung-bali-123456`. Di semua langkah berikutnya, "project `patung-bali`" berarti project ini.
3. Pastikan billing aktif: Billing → harus terlihat billing account yang terhubung. Kalau belum ada, hubungkan
   (Link a billing account).
4. Pasang alarm anggaran: Billing → Budgets & alerts → Create budget. Pilih project tadi, isi jumlah US$1, biarkan
   alarm di 50%, 90%, dan 100%. Email peringatan akan masuk ke pemilik akun.

## 2. Pasang gcloud CLI di laptop

Jalankan di PowerShell:

```powershell
winget install --id Google.CloudSDK -e
```

Tutup lalu buka lagi PowerShell, kemudian:

```powershell
gcloud init
```

Pilih akun Google tadi dan project `patung-bali`. Setelah itu, setel region default ke Singapura (sama dengan
Supabase, supaya query database cepat):

```powershell
gcloud config set run/region asia-southeast1
```

## 3. Aktifkan layanan yang dipakai

```powershell
gcloud services enable run.googleapis.com cloudbuild.googleapis.com artifactregistry.googleapis.com
```

Buat tempat penyimpanan image:

```powershell
gcloud artifacts repositories create patung --repository-format=docker --location=asia-southeast1
```

## 4. Siapkan variabel rahasia

Di folder repo, salin template lalu isi nilainya:

```powershell
Copy-Item cloudrun.env.example.yaml cloudrun.env.yaml
```

`cloudrun.env.yaml` sudah masuk `.gitignore`. **Jangan di-commit dan jangan dikirim ke grup chat.**

| Variabel | Dari mana nilainya |
|---|---|
| `APP_KEY` | Buat baru: `php artisan key:generate --show`. Kunci baru hanya membuat semua orang keluar dari sesi login. |
| `APP_URL` | Isi sementara apa saja; diganti di langkah 6 setelah alamat Cloud Run keluar. |
| `REVIEWS_DB_URL` | Sama dengan yang dipakai di Vercel. Wajib memakai alamat **Session pooler** Supabase (host `...pooler.supabase.com`, port `5432`), karena alamat langsung `db.<ref>.supabase.co` hanya IPv6 dan Cloud Run tidak bisa menjangkaunya. Ambil dari Supabase → Connect → Session pooler, lalu ganti user dan password dengan akun aplikasi (`patung_app`), sehingga user-nya berbentuk `patung_app.<project-ref>`. |
| `EXPERT_ACCESS_TOKEN`, `EXPORT_ACCESS_TOKEN` | File lokal `RAHASIA - tautan validasi pakar.txt`. |
| `ADMIN_EMAIL` | Email akun admin bersama. |
| `ADMIN_PASSWORD_HASH` | Hash yang sama dengan Vercel, atau buat baru dengan `php artisan patung:admin-password`. |
| `SUPABASE_S3_KEY_ID`, `SUPABASE_S3_SECRET` | Sama dengan Vercel, atau buat pasangan baru di Supabase → Storage → S3 Configuration → Access keys. |
| `GOOGLE_MAPS_EMBED_KEY` | Sama dengan Vercel (Google Cloud Console → APIs & Services → Credentials). |

Nilai yang ditandai *Sensitive* di Vercel tidak bisa dibaca ulang dari dashboard. Kalau kalian tidak menyimpannya,
buat yang baru seperti di tabel. Kalau password database `patung_app` yang harus direset, ingat untuk memperbarui juga
`REVIEWS_DB_URL` di Vercel bila suatu saat Vercel dipakai lagi.

## 5. Build image

Dari folder repo (yang berisi `cloudbuild.yaml`):

```powershell
gcloud builds submit --config cloudbuild.yaml --gcs-source-staging-dir=gs://patung-bali-build-src/source
```

Proses ini mengunggah kode (tanpa `vendor`, `node_modules`, dan `.env`) lalu membangun image di server Google.
Biasanya 5–10 menit.

## 6. Deploy

Ganti `PROJECT_ID` dengan Project ID kalian:

```powershell
gcloud run deploy patung-bali `
  --image asia-southeast1-docker.pkg.dev/PROJECT_ID/patung/web:latest `
  --allow-unauthenticated `
  --env-vars-file cloudrun.env.yaml `
  --memory 512Mi --cpu 1 `
  --cpu-throttling `
  --min-instances 0 --max-instances 2 `
  --concurrency 40 --timeout 60
```

Arti pengaman di perintah itu:

- `--cpu-throttling`: tagihan per request; CPU dan memori tidak dihitung saat tidak ada request.
- `--min-instances 0`: tidak ada instance yang terus menyala.
- `--max-instances 2`: batas atas, supaya serangan bot tidak bisa membengkakkan biaya.

Di akhir, gcloud menampilkan `Service URL`, misalnya `https://patung-bali-abc123xyz-as.a.run.app`. Masukkan alamat
itu ke `APP_URL`:

```powershell
gcloud run services update patung-bali --update-env-vars APP_URL=https://ALAMAT-DARI-GCLOUD
```

Ubah juga `APP_URL` di `cloudrun.env.yaml` supaya deploy berikutnya tidak mengembalikannya.

**Izinkan alamat baru memakai kunci Maps.** Kunci Street View biasanya dibatasi hanya untuk domain tertentu. Tanpa
langkah ini, embed Street View di halaman patung akan menampilkan error.

1. Console → APIs & Services → Credentials → klik kunci Maps.
2. Di *Application restrictions → Website restrictions*, tambahkan `https://ALAMAT-DARI-GCLOUD/*`. Jangan hapus
   alamat `vercel.app` selama alamat itu masih dipakai.
3. Save. Perubahan bisa butuh beberapa menit untuk berlaku.

## 7. Cek hasilnya

1. Buka `Service URL/up`, harus muncul halaman hijau "Application up".
2. Buka beranda, daftar patung, satu halaman patung, peta, dan dokumentasi.
3. Masuk ke `/admin/masuk`, lalu coba unggah satu foto uji dan hapus lagi.
4. Kalau ada error, lihat log:

```powershell
gcloud run services logs read patung-bali --limit 50
```

## 8. Setelah pindah

- Ganti tautan lama `sistem-informasi-patung-bali.vercel.app` di QR code, deck presentasi, laporan, dan README.
- Tautan rahasia informan ikut berganti domain: bagian depannya diganti, token di belakangnya tetap.

## Memperbarui website setelah ada perubahan kode

```powershell
gcloud builds submit --config cloudbuild.yaml --gcs-source-staging-dir=gs://patung-bali-build-src/source
```

```powershell
gcloud run deploy patung-bali --image asia-southeast1-docker.pkg.dev/PROJECT_ID/patung/web:latest --env-vars-file cloudrun.env.yaml
```

Pengaturan memori dan batas instance dari langkah 6 tetap tersimpan.

Hapus image lama sesekali di Console → Artifact Registry → `patung` → `web`. Sisakan yang terbaru saja.

## Kalau ada masalah

| Gejala | Penyebab dan solusi |
|---|---|
| `gcloud builds submit` gagal dengan `PERMISSION_DENIED` ke Artifact Registry | Beri peran *Artifact Registry Writer* ke service account Cloud Build di IAM, lalu ulangi. |
| Halaman error 500 dan log berisi `Network is unreachable` atau `could not translate host name` | `REVIEWS_DB_URL` memakai host `db.<ref>.supabase.co`. Ganti ke alamat Session pooler (langkah 4). |
| Log berisi `password authentication failed` | User pooler harus berbentuk `patung_app.<project-ref>`, bukan `patung_app` saja. |
| Halaman pertama lambat (2–5 detik) setelah lama tidak dibuka | Wajar: instance sedang dinyalakan (*cold start*). Request berikutnya cepat. |
| Email peringatan anggaran masuk | Cek Console → Billing → Reports untuk melihat layanan mana yang memakan biaya. |

## Alamat rapi lewat Firebase Hosting

Website publik diakses lewat `https://patung-bali.web.app`. Firebase Hosting meneruskan semua request ke Cloud Run
(`firebase.json`) dan menyimpan halaman di CDN sesuai header `s-maxage`, jadi Cloud Run lebih jarang menyala.

- Firebase hanya meneruskan cookie bernama `__session`, karena itu `SESSION_COOKIE` wajib `__session`.
- Karena hanya satu cookie yang lolos, isi session disimpan di tabel `sessions` di Supabase (`SESSION_DRIVER=database`),
  bukan di cookie. Tabel itu dibuat dengan RLS dan hak penuh untuk `patung_app`.
- `APP_URL` diisi `https://patung-bali.web.app`.
- Konfigurasi ini cukup dipasang sekali. Deploy kode baru ke Cloud Run tidak perlu mengulangnya. Kalau suatu saat
  perlu memasang ulang: `npx firebase-tools deploy --only hosting --project patung-bali`.

## Menekan biaya penyimpanan

- Kode sumber untuk build diunggah ke bucket `gs://patung-bali-build-src` di Singapura, yang menghapus isinya
  otomatis setelah 1 hari. Bucket bawaan Cloud Build ada di multi-region Amerika dan biaya transfernya justru yang
  paling besar di tagihan pertama, jadi selalu pakai `--gcs-source-staging-dir` seperti di atas.
- Artifact Registry `patung` punya aturan pembersihan: hanya 2 image terbaru yang disimpan.
- Kunci Google Maps dibatasi ke `patung-bali.web.app`, alamat Cloud Run, dan `localhost:8000`. Kalau domain berubah,
  tambahkan di Console → APIs & Services → Credentials.
