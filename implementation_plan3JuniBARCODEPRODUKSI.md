# Production Output Log Feature (Revised)

Fitur untuk mencatat log output produksi per-produk keluar. Operator cukup klik **"Add Output Log"** — sistem otomatis generate log dengan timestamp WIB, nama operator (dari sesi scan), DIC ID aktif, dan quantity yang dihitung dari cavity.

---

## Perubahan dari Plan Sebelumnya

| | Plan Lama | Plan Baru |
|---|---|---|
| **Identitas operator** | `user_id` (FK ke users) | `operator_name` (string, dari sesi NIK login) |
| **Quantity** | Input manual | Auto-hitung dari cavity DIC/master |

---

## Logika Quantity

Quantity yang disimpan dihitung otomatis dengan urutan prioritas:

```
1. DIC.temporal_cavity (jika ada & > 0)
2. MasterListItem.cavity (jika ada & > 0)  
3. Default: 1
```

Ini berarti setiap kali operator klik "Add Output Log", quantity yang dicatat = jumlah produk per-shot berdasarkan cavity yang sudah dikonfigurasi.

---

## Open Questions

> [!NOTE]
> **Apakah `operator_name` diambil dari session verify NIK (`session('verifiedNIK')` / `session('operatorName')`)?**
> Di flow scan barcode existing, nama operator diambil dari sesi NIK yang diverifikasi sebelum scan. Saya akan menggunakan pendekatan yang sama — pass dari session saat tombol diklik.

> [!NOTE]
> **Apakah log ini hanya bisa dibuat jika ada DIC aktif?** Plan: ya, jika tidak ada `activeDIC`, tombol dinonaktifkan / ditampilkan pesan error.

---

## Proposed Changes

### 1. Database Migration

#### [NEW] `create_production_output_logs_table`

Tabel baru `production_output_logs`:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint (PK) | Auto increment |
| `dic_id` | bigint (FK) | Foreign key ke `daily_item_codes.id` |
| `operator_name` | varchar(255) | Nama operator (dari sesi NIK login) |
| `quantity` | integer | Auto dari cavity DIC → master → default 1 |
| `logged_at` | timestamp | Waktu log dicatat (timezone WIB) |
| `timestamps` | - | `created_at`, `updated_at` |

---

### 2. Model

#### [NEW] `app/Models/ProductionOutputLog.php`

```php
protected $fillable = ['dic_id', 'operator_name', 'quantity', 'logged_at'];
protected $casts = ['logged_at' => 'datetime'];

// Relasi
public function dailyItemCode() { return $this->belongsTo(DailyItemCode::class, 'dic_id'); }
```

#### [MODIFY] `app/Models/DailyItemCode.php`

Tambah relasi:
```php
public function outputLogs() {
    return $this->hasMany(ProductionOutputLog::class, 'dic_id');
}
```

---

### 3. Routes

#### [MODIFY] `routes/web.php`

Tambah di dalam middleware `auth`:

```php
// Production Output Log
Route::post('/production-output-log', [DashboardController::class, 'storeOutputLog'])
    ->name('production.output-log.store');
```

---

### 4. Controller

#### [MODIFY] `app/Http/Controllers/DashboardController.php`

**Tambah use statement:**
```php
use App\Models\ProductionOutputLog;
```

**Method `storeOutputLog(Request $request)`:**
```
1. Ambil activeDIC dari user yang login (sama seperti di index())
2. Hitung quantity:
   - Cek $activeDIC->temporal_cavity → gunakan jika ada & > 0
   - Fallback: $activeDIC->masterItem->cavity → gunakan jika ada & > 0
   - Fallback akhir: 1
3. Ambil operator_name dari request input ('operator_name') yang dikirim dari form
   (di form, operator_name diisi dari session verifiedNIK / nama operator yang login scan)
4. Create ProductionOutputLog:
   - dic_id = $activeDIC->id
   - operator_name = operator_name dari request
   - quantity = hasil kalkulasi cavity
   - logged_at = Carbon::now('Asia/Jakarta')
5. Return redirect()->back()->with('success', 'Log berhasil ditambahkan.')
```

**Modifikasi `index()` (bagian OPERATOR):**
Tambah load output logs untuk DIC aktif:
```php
$outputLogs = $activeDIC
    ? ProductionOutputLog::where('dic_id', $activeID)
        ->orderByDesc('logged_at')
        ->get()
    : collect();
```
Tambah `outputLogs` ke compact().

---

### 5. View — Dashboard Operator

#### [MODIFY] `resources/views/dashboards/dashboard-operator.blade.php`

Tambah section **"Output Log"** di bawah tabel Detail Pekerjaan (sekitar baris 670), di dalam blok `x-show="scanMode && verified"`:

**UI yang ditambahkan:**

1. **Card Output Log** dengan:
   - Header "Output Log Produksi"
   - Badge jumlah log hari ini
   - Info quantity per-shot (cavity yang dipakai)

2. **Tombol "Tambah Log Output"** — submit form langsung (no modal, lebih cepat untuk operator)
   - Form POST ke route `production.output-log.store`
   - Hidden field `operator_name` diisi dari JS (session/data yang sudah verified)

3. **Tabel log untuk DIC aktif:**

| Waktu (WIB) | Operator | Quantity |
|---|---|---|
| 08:30:12 | BUDI | 2 |
| 09:15:45 | SITI | 2 |

Tabel ini di-render server-side dari `$outputLogs` yang di-pass dari controller.

---

## Verification Plan

### Migration
```bash
php artisan migrate
```

### Manual Testing
1. Login sebagai operator
2. Verifikasi NIK untuk scan mode
3. Pastikan ada DIC aktif
4. Klik **"Tambah Log Output"**
5. Verifikasi:
   - Log muncul di tabel dengan timestamp WIB yang benar
   - Nama operator terisi dari sesi
   - Quantity sesuai cavity (DIC temporal > master item > 1)
6. Cek DB: `SELECT * FROM production_output_logs ORDER BY logged_at DESC`
