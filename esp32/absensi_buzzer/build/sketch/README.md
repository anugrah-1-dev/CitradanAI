#line 1 "D:\\project\\CitradanAI\\esp32\\absensi_buzzer\\README.md"
# Buzzer absensi ESP32

Sketch ini menerima perintah serial dari halaman absensi BisaGym. API wajah YuNet/SFace tetap berjalan di `bisagym-api`; ESP32 mengendalikan buzzer dan LED berdasarkan hasil deteksi wajah.

## Komponen dan kabel ESP32

Gunakan ESP32 DevKit yang terpisah dari ESP32-CAM, LCD I2C 16x2, dua LED dengan resistor 220 ohm, dan modul **active buzzer**:

| Komponen | ESP32 |
|---|---|
| LCD SDA | GPIO 22 |
| LCD SCL | GPIO 23 |
| LED merah anoda melalui resistor 220 ohm | GPIO 18 |
| LED hijau anoda melalui resistor 220 ohm | GPIO 19 |
| Buzzer IN / SIG | GPIO 13 |
| Katoda kedua LED, LCD GND, buzzer GND | GND |
| LCD VCC | Tegangan sesuai spesifikasi modul |
| Buzzer VCC | Tegangan sesuai spesifikasi modul |

Hubungkan setiap LED secara seri dengan resistor pembatas arus; jangan menghubungkan LED langsung ke GPIO. Jika memakai buzzer yang membutuhkan arus di atas kemampuan GPIO, kendalikan buzzer dengan transistor/driver dan jangan hubungkan beban tersebut langsung ke GPIO. Pastikan GND ESP32 dan supply buzzer tersambung bersama. Sketch mengasumsikan buzzer aktif saat GPIO 13 HIGH dan LCD beralamat I2C `0x27`. Ubah `LCD_ADDRESS` pada sketch jika modul Anda beralamat `0x3F` atau alamat lain. Pasang library Arduino **LiquidCrystal_I2C**.

## Langkah pemasangan

1. Pasang Arduino IDE dan dukungan board **esp32 by Espressif Systems** melalui Boards Manager.
2. Buka `absensi_buzzer.ino` di Arduino IDE, pilih board ESP32 yang sesuai dan port USB ESP32.
3. Hubungkan komponen sesuai tabel. Gunakan ESP32 DevKit sebagai pengendali LCD/LED/buzzer; jangan pasang komponen tersebut ke pin ESP32-CAM karena pin kamera dapat berbenturan. Tutup Serial Monitor setelah upload selesai karena port hanya dapat digunakan satu aplikasi pada satu waktu.
4. Upload sketch. Untuk uji mandiri, buka Serial Monitor pada **115200 baud** dan pilih line ending newline. Kirim `KNOWN:Anugrah` untuk menguji LED hijau dan nama pada LCD, `UNKNOWN` untuk menguji LED merah dan tiga bunyi buzzer, serta `TEST` untuk menguji buzzer. Perintah `OFF` mematikan LED/buzzer dan mengembalikan LCD ke layar siap.
5. Jalankan API biometrik dan Laravel seperti biasa. Buka halaman absensi di Chrome atau Edge terbaru melalui `localhost` atau HTTPS.
6. Tekan **Sambungkan Buzzer ESP32**, pilih port USB ESP32, lalu izinkan akses. Tombol ini harus ditekan langsung oleh pengguna karena browser meminta izin serial.
7. Jalankan scan wajah. Wajah yang dikenali mengirim `KNOWN:<nama>` (LED hijau dan nama member di LCD), termasuk saat check-in ditolak karena alasan non-pengenalan seperti membership kedaluwarsa. Wajah yang tidak dikenali mengirim `UNKNOWN` (LED merah dan tiga beep). Tidak ada wajah atau error lain mengirim `OFF`. Pastikan status menunjukkan ESP32 terhubung.

Koneksi USB serial digunakan oleh browser yang menjalankan halaman absensi. Jika halaman dibuka dari perangkat lain, ESP32 DevKit juga harus tersambung ke perangkat browser tersebut. Jangan biarkan Serial Monitor atau aplikasi lain memegang COM port saat browser menggunakannya. Status “ESP32 terhubung” hanya memastikan browser dapat membuka port serial; gunakan tombol **Tes Buzzer ESP32** dan pastikan buzzer berbunyi serta status menunjukkan `ESP32 menerima perintah TEST.` Halaman scan saat ini tetap menggunakan kamera browser; ESP32-CAM belum menjadi sumber gambar scan. Jika LCD kosong, periksa pin SDA/SCL, alamat I2C, dan library. Jika ACK diterima tetapi buzzer diam, periksa kabel GPIO 13/GND/VCC, kesamaan ground, polaritas modul, dan pastikan buzzer aktif (bukan passive buzzer). Jika tidak ada ACK, unggah sketch terbaru dan sambungkan ulang ESP32.
