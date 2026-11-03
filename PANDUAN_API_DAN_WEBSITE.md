# Panduan Menjalankan Website-BisaGym & Biometric API

Folder API biometrik sekarang **sudah berada di dalam folder `Website-BisaGym`** dengan nama subfolder `bisagym-api`.

---

## 📁 Struktur Folder Baru

```text
Website-BisaGym/
├── app/
│   ├── Console/Commands/
│   │   └── ServeFaceApiCommand.php     <- Perintah Artisan php artisan face-api:serve
│   ├── Http/Controllers/
│   │   ├── FaceEnrollmentController.php
│   │   ├── StreamingCameraController.php
│   │   └── DeviceController.php
│   └── Services/
│       └── FaceRecognitionApi.php      <- Terhubung ke bisagym-api (port 8001)
├── bisagym-api/                        <- FOLDER API BIOMETRIK LENGKAP
│   ├── app/                            <- FastAPI, OpenCV YuNet & SFace Engine
│   ├── runtime-models/                 <- Model ONNX YuNet & SFace
│   ├── gym.db                          <- Database SQLite lokal untuk biometrik
│   ├── .env                            <- Konfigurasi port & model biometrik
│   └── .venv/                          <- Python 3.13 Virtual Environment siap pakai
├── run_all.bat                         <- Sekali klik untuk jalankan Laravel + API
├── run_api.bat                         <- Sekali klik untuk jalankan API Biometrik saja
└── .env                                <- Konfigurasi Laravel
```

---

## 🚀 Cara Menjalankan Aplikasi

Anda memiliki 3 cara mudah untuk menjalankan website beserta API biometriknya:

### Cara 1: Menggunakan Script Otomatis (Paling Mudah)
Cukup klik dua kali file **`run_all.bat`** di dalam folder `Website-BisaGym`.
Script ini akan otomatis membuka 2 jendela konsol:
1. **BisaGym Biometric API** berjalan di `http://127.0.0.1:8001`
2. **BisaGym Laravel Web** berjalan di `http://127.0.0.1:8000`

---

### Cara 2: Menggunakan Perintah Artisan Laravel
Buka dua jendela terminal/PowerShell di folder `Website-BisaGym`:

* **Terminal 1 (Jalankan API Biometrik):**
  ```powershell
  php artisan face-api:serve
  ```

* **Terminal 2 (Jalankan Web Laravel):**
  ```powershell
  php artisan serve --port=8000
  ```

---

### Cara 3: Menggunakan File Batch `run_api.bat`
Jika Anda menjalankan Laravel dari Laragon / XAMPP / CMD, Anda cukup klik dua kali **`run_api.bat`** untuk menyalakan engine biometriknya di port 8001.

---

## 🔍 Cek Kesehatan API Biometrik di Browser

Saat API berjalan, Anda dapat memverifikasi statusnya langsung di browser:
* Status Sistem: [http://127.0.0.1:8001/health/ready](http://127.0.0.1:8001/health/ready)
* Status Model AI YuNet/SFace: [http://127.0.0.1:8001/health/model](http://127.0.0.1:8001/health/model)
* Dokumentasi Swagger: [http://127.0.0.1:8001/docs](http://127.0.0.1:8001/docs)

Kedua endpoint harus menampilkan `"status": "ready"`.

