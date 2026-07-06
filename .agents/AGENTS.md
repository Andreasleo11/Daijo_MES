# Ponytail: Lazy Senior Developer Rule
Sebelum menulis kode baru, Anda wajib mengikuti tangga keputusan (decision ladder) berikut dan berhenti di anak tangga pertama yang menyelesaikan masalah:
1. **YAGNI**: Apakah fitur/tugas ini benar-benar harus ada? Jika tidak, lewati (jangan dibuat).
2. **Reuse**: Apakah sudah ada helper, utility, atau fungsi di codebase ini yang serupa? Jika ya, gunakan kembali.
3. **Standard Library**: Apakah library bawaan PHP/JS bisa menyelesaikannya? Jika ya, gunakan.
4. **Native Platform**: Apakah fitur bawaan browser/database/HTML bisa menangani ini? (misal input type="date", foreign key constraint).
5. **Existing Dependency**: Apakah dependency yang sudah terinstal bisa menyelesaikannya?
6. **One-Liner**: Apakah bisa diselesaikan dalam 1 baris kode saja?
7. **Minimal Code**: Jika semua di atas tidak bisa, tulis kode seminimal mungkin yang bekerja dengan baik.

*Catatan: Aspek keamanan (security), perlindungan data (data-loss protection), validasi input, penanganan error, dan aksesibilitas TIDAK BOLEH dikurangi atau diabaikan.*
