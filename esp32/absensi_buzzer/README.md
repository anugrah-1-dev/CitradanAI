# Buzzer absensi ESP32

Sketch ini menerima perintah serial dari halaman absensi BisaGym. API wajah YuNet/SFace tetap berjalan di `bisagym-api`; ESP32 hanya mengendalikan buzzer setelah browser menerima hasil deteksi.

## Komponen dan kabel

Gunakan ESP32 DevKit dan modul **active buzzer**:

| Modul buzzer | ESP32 |
|---|---|
| IN / SIG | GPIO 23 |
| GND | GND |
| VCC | Tegangan sesuai spesifikasi modul |

Sketch `absensi_buzzer.ino` saat ini menggunakan GPIO 23. Jika Anda memilih pin lain, ubah nilai `BUZZER_PIN` di sketch agar sama dengan pin kabel. Jika memakai buzzer yang membutuhkan arus di atas kemampuan GPIO, kendalikan buzzer dengan transistor/driver dan jangan hubungkan beban tersebut langsung ke GPIO. Pastikan GND ESP32 dan supply buzzer tersambung bersama. Sketch ini mengasumsikan buzzer aktif saat GPIO 23 HIGH; sesuaikan level HIGH/LOW bila modul Anda active-low.

## Langkah pemasangan

1. Pasang Arduino IDE dan dukungan board **esp32 by Espressif Systems** melalui Boards Manager.
2. Buka `absensi_buzzer.ino` di Arduino IDE, pilih board ESP32 yang sesuai dan port USB ESP32.
3. Hubungkan buzzer sesuai tabel. Tutup Serial Monitor setelah upload selesai karena port hanya dapat digunakan satu aplikasi pada satu waktu.
4. Upload sketch. Untuk uji mandiri, buka Serial Monitor pada **115200 baud**, kirim `UNKNOWN` dengan line ending newline, lalu pastikan buzzer berbunyi tiga kali. Perintah `OFF` menghentikan buzzer.
5. Jalankan API biometrik dan Laravel seperti biasa. Buka halaman absensi di Chrome atau Edge terbaru melalui `localhost` atau HTTPS.
6. Tekan **Sambungkan Buzzer ESP32**, pilih port USB ESP32, lalu izinkan akses. Tombol ini harus ditekan langsung oleh pengguna karena browser meminta izin serial.
7. Jalankan scan wajah. Respons `IDENTITY_NOT_FOUND` dari YuNet/SFace mengirim `UNKNOWN` ke ESP32; respons lain mengirim `OFF`. Pastikan status menunjukkan ESP32 terhubung.

Koneksi USB serial digunakan oleh browser yang menjalankan halaman absensi. Jika halaman dibuka dari perangkat lain, ESP32 juga harus tersambung ke perangkat browser tersebut. Jangan biarkan Serial Monitor atau aplikasi lain memegang COM port saat browser menggunakannya.
