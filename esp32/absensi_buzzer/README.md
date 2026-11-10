# Output absensi ESP32: buzzer, LED, dan LCD

Sketch ini menerima perintah serial dari halaman absensi BisaGym. Setelah server mengirim hasil check-in, ESP32 mengendalikan buzzer, LED, dan LCD. API wajah YuNet/SFace tetap berjalan di `bisagym-api`; ESP32 tidak melakukan pengenalan wajah.

## Komponen dan kabel

Gunakan ESP32 DevKit, modul **active buzzer**, dua LED dengan resistor 220-330 ohm, dan LCD 16x2 dengan backpack I2C:

| Komponen | ESP32 |
|---|---|
| Buzzer IN / SIG | GPIO 14 |
| LED merah anoda melalui resistor | GPIO 13 |
| LED hijau anoda melalui resistor | GPIO 12 |
| Katoda kedua LED | GND |
| LCD SDA | GPIO 22 |
| LCD SCL | GPIO 23 |
| LCD VCC / GND | Tegangan sesuai modul / GND |

Sketch ini disesuaikan dengan wiring DevKit di atas, mengatur I2C secara eksplisit pada SDA GPIO 22 dan SCL GPIO 23, memakai alamat LCD `0x27`, dan mengasumsikan buzzer aktif-HIGH. Jika LCD Anda memakai alamat `0x3F`, ubah `LCD_ADDRESS` di sketch. Jika buzzer membutuhkan arus di atas kemampuan GPIO, gunakan transistor/driver; jangan hubungkan beban tersebut langsung ke GPIO. Semua komponen harus memiliki GND bersama.

## Langkah pemasangan

1. Pasang Arduino IDE dan dukungan board **esp32 by Espressif Systems** melalui Boards Manager.
2. Pasang library Arduino **LiquidCrystal_I2C** melalui Library Manager.
3. Buka `absensi_buzzer.ino` di Arduino IDE, pilih board ESP32 yang sesuai dan port USB ESP32.
4. Hubungkan buzzer, LED, dan LCD sesuai tabel. Tutup Serial Monitor setelah upload karena port hanya dapat digunakan satu aplikasi pada satu waktu.
5. Upload sketch. Untuk uji mandiri, buka Serial Monitor pada **115200 baud** dan kirim perintah dengan line ending newline:
   - `SUCCESS`: LED hijau, LCD “ABSEN BERHASIL”, dan satu beep pendek.
   - `FAIL`: LED merah dan LCD “ABSEN GAGAL”.
   - `UNKNOWN`: LED merah, LCD “ABSEN GAGAL”, dan tiga beep.
   - `TEST`: LED hijau, LCD “TES OUTPUT OK”, dan satu beep.
   - `OFF`: matikan LED dan buzzer, lalu tampilkan layar siaga.
6. Jalankan API biometrik dan Laravel seperti biasa. Buka halaman absensi di Chrome atau Edge terbaru melalui `localhost` atau HTTPS.
7. Tekan **Sambungkan Output ESP32**, pilih port USB ESP32, lalu izinkan akses. Tombol ini harus ditekan langsung oleh pengguna karena browser meminta izin serial.
8. Jalankan scan wajah. Check-in berhasil menyalakan LED hijau dan menampilkan “ABSEN BERHASIL”. Check-in ditolak menyalakan LED merah dan menampilkan “ABSEN GAGAL”; buzzer berbunyi tiga kali untuk wajah yang tidak dikenali.

Koneksi USB serial digunakan oleh browser yang menjalankan halaman absensi. Jika halaman dibuka dari perangkat lain, ESP32 juga harus tersambung ke perangkat browser tersebut. Jangan biarkan Serial Monitor atau aplikasi lain memegang COM port saat browser menggunakannya. Gunakan tombol **Tes Output ESP32 (LED/LCD/Buzzer)**: LED hijau harus menyala, LCD menampilkan “TES OUTPUT OK”, dan buzzer berbunyi sekali. Jika LCD kosong, cek alamat I2C, kabel SDA/SCL, dan tegangan LCD; jika LED tidak menyala, cek polaritas dan resistor.
