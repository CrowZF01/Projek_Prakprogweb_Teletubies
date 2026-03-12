# Projek Prakprogweb Teletubies

Ini adalah prototipe crowdfunding sederhana menggunakan hanya HTML dan CSS. Empat halaman inti sudah tersedia:

- `halLogin.html`: form login statis dengan pilihan jenis pengguna.
- `halUtama.html`: halaman beranda yang menampilkan daftar kampanye dan area pencarian/filternya.
- `halDetail.html`: tampilan detail kampanye dengan tombol donasi.
- `halDonate.html`: ringkasan kampanye plus formulir donasi.

## Cara Menjalankan

Tidak ada proses build khusus karena semua file statis. Berikut cara melihat proyek di browser:

1. **Buka langsung**
   - Buka File Explorer dan navigasikan ke folder proyek.
   - Klik dua kali salah satu file HTML (`halUtama.html` misalnya) untuk membukanya di browser.

2. **Menggunakan ekstensi Live Server (rekomen)**
   - Jika memakai VS Code, install ekstensi *Live Server*.
   - Klik kanan pada `halUtama.html` dan pilih *Open with Live Server*.
   - Browser akan reload otomatis ketika file diubah.

3. **Menambahkan konten**
   - Ganti teks placeholder dengan data nyata.
   - Tambahkan gambar kampanye di folder `img/` dan sesuaikan `src` pada elemen `<img>`.

## Struktur Ringkas

```
halLogin.html
halUtama.html
halDetail.html
halDonate.html
styles/
  style_1.css  <-- definisi gaya halaman
img/           <-- tempat menyimpan poster kampanye
```

## Tips Lanjutan

- Karena tidak ada JavaScript, beberapa elemen (form pencarian, login, donasi) belum fungsional.
- Ketika siap menambahkan interaktivitas, bisa mulai dengan script sederhana atau backend.
- Pastikan setiap halaman terhubung menggunakan hyperlink yang ada di `<nav>` dan tombol.

Semoga membantu! 🎯