# CitradanAI

Sistem Manajemen Gym **BisaGym** terintegrasi Pengolahan Citra Digital dan AI Biometrik Pengenalan Wajah (*YuNet Face Detection & SFace Face Recognition*).

## Fitur Utama
1. **Manajemen Member & Kasir**: Pendaftaran, perpanjangan paket gym, POS snack, laporan keuangan.
2. **Pendaftaran Wajah (Face Enrollment)**: Pengambilan 3 pose wajah (Depan, Kiri, Kanan) dengan validasi kualitas (Laplacian variance, exposure, alignment).
3. **Scan Absensi & Kiosk Mandiri**: Absensi member via Barcode/ID atau Kiosk Wajah AI (dilengkapi mode fullscreen).
4. **Streaming Kamera**: Deteksi beberapa wajah secara real-time pada video feed dengan pelacakan IoU dan bounding box.
5. **Manajemen Perangkat (Kiosk)**: Monitoring status perangkat, rotasi kredensial, dan status kamera.

## Cara Menjalankan
1. **Otomatis**: Jalankan `run_all.bat` untuk menyalakan Laravel (port 8000) dan API Biometrik (port 8001).
2. **Manual**:
   - Terminal 1: `php artisan face-api:serve`
   - Terminal 2: `php artisan serve --port=8000`

