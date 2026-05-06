# SISTEM ABSEN CAFE AREA - DESIGN DOCUMENT

## 1. DESKRIPSI SISTEM

Sistem absen berbasis lokasi GPS dan QR Code untuk cafe area dengan fitur shift-based attendance dan salary deduction system.

### Fitur Utama:

- **Admin Panel**: Manajemen user, setup QR location-based, setting gaji, tracking absen
- **User Mobile**: Login, scan QR otomatis via kamera, absen instant
- **Location-Based QR**: QR hanya valid jika dalam radius tertentu dari cafe
- **Shift Management**: 2 shift (Shift 1: 9:00-17:00, Shift 2: 17:00-00:00)
- **Automatic Salary Deduction**: Potong gaji otomatis jika telat

---

## 2. ALUR SISTEM

### A. ALUR ADMIN

#### 1. Setup System Admin

```
Login Admin
  ↓
Dashboard
  ├─ Manage Employees
  ├─ Setup QR Location
  ├─ Manage Shifts
  ├─ Salary Management
  └─ View Attendance Reports
```

#### 2. Manage Employees

```
Admin Buka Employee Management
  ↓
Pilih: Create New Employee
  ├─ Input: Nama, Email, No.HP, Role (staff/kasir/manager)
  ├─ Input: Basic Salary & Shift Assignment
  ├─ Set: Late Deduction Amount (misal: -50000 per 15 min)
  ├─ Generate: Username & Temp Password
  └─ Show QR Code untuk employee (bisa di-print)

Pilih: Edit/View Employee
  ├─ View: Attendance History
  ├─ View: Current Month Attendance
  ├─ View: Calculated Salary dengan deductions
  ├─ View: Late records dengan duration
  └─ Action: Edit gaji, disable account
```

#### 3. Setup QR Location-Based

```
Admin Dashboard → QR Management
  ↓
Pilih: Create New QR
  ├─ Input: QR Name (misal: "QR Main Counter")
  ├─ Input: QR Code (unique, generate or custom)
  ├─ Pick Location:
  │    Click Map / Input Coordinates
  │    Set: Latitude, Longitude
  │    Set: Radius (misal: 100 meter)
  ├─ Input: Active Hours (start-end)
  ├─ Link: Assign ke Shift (Shift 1 & 2 atau salah satu)
  └─ Generate: QR Image Display

Pilih: Edit QR
  ├─ Update: Location coordinates & radius
  ├─ Update: Active Hours
  ├─ View: List employee scan records
  └─ Action: Deactivate / Reactivate

Pilih: View QR Statistics
  ├─ Yesterday/Today/This Week scans
  ├─ Top employees
  └─ Failed scans (outside radius)
```

#### 4. Manage Shifts

```
Admin Dashboard → Shift Management
  ↓
View Shifts:
  ├─ Shift 1: 09:00 - 17:00
  │    ├─ Expected arrival: 09:00
  │    ├─ Late threshold: >5 min
  │    └─ Employees assigned
  │
  └─ Shift 2: 17:00 - 00:00 (midnight)
       ├─ Expected arrival: 17:00
       ├─ Late threshold: >5 min
       └─ Employees assigned

Edit Shift:
  ├─ Adjust: Start time, End time
  ├─ Adjust: Threshold telat
  └─ Bulk assign employees
```

#### 5. Salary Management

```
Admin Dashboard → Salary Management
  ↓
View Salary:
  ├─ Select Employee & Month
  ├─ Show: Base Salary
  ├─ Show: Attendance Record (list absen days)
  ├─ Show: Late Records:
  │    ├─ Date, Duration, Amount Deducted
  │    └─ Example: 2026-02-10: 15 min → -50000
  ├─ Show: Total Deductions
  ├─ Show: Final Salary (Base - Deductions)
  └─ Action: Approve/Reject untuk payroll

Payroll Processing:
  ├─ Bulk select employees
  ├─ Set: Payroll Period (start-end date)
  ├─ Generate: Salary slip untuk semua
  ├─ Show: Total payroll amount
  └─ Export: CSV / PDF report
```

#### 6. Attendance Reports

```
Admin Dashboard → Attendance Reports
  ↓
Filter By:
  ├─ Date Range
  ├─ Employee
  ├─ Shift
  └─ Status (Present/Late/Absent)

Export:
  ├─ PDF report
  ├─ Excel report
  └─ Email report ke management
```

---

### B. ALUR USER (MOBILE APP / WEB)

#### 1. User Login

```
User akses aplikasi
  ↓
Input: Username & Password
  ↓
Sistem validasi
  ├─ Valid → Proceed to Dashboard
  └─ Invalid → Show error message
```

#### 2. Attendance Dashboard

```
User Dashboard setelah login
  ├─ Show: Nama, Assigned Shift
  ├─ Show: Today's Status
  │    ├─ Not yet checked in
  │    ├─ OR: Checked in at HH:MM
  │    ├─ Show: Current time vs deadline
  │    └─ Show: Late status (if any)
  │
  ├─ Main Action: "SCAN QR SEKARANG"
  │    └─ Buka kamera
  │       ↓
  │       Kamera auto-detect QR code
  │       ↓
  │       Sistem proses:
  │       ├─ Validasi: QR code exists?
  │       ├─ Validasi: Waktu dalam operating hours?
  │       ├─ Validasi: User dalam radius lokasi?
  │       ├─ Validasi: User belum absen hari ini?
  │       ├─ Hitung: Late duration jika applicable
  │       └─ Save attendance record
  │
  │    Hasil: ✓ Absen sukses
  │        Show: Jam absen, Status (On time/Late), Location verified
  │
  └─ Section: View History
       ├─ This week attendance
       ├─ Total present, late, absent
       └─ Salary deduction preview (if any)
```

#### 3. Attendance Success Flow

```
User scan QR → System validate lokasi + waktu
  ↓
Jika dalam radius & pada jam kerja:
  ├─ Check: Sudah absen hari ini?
  │    ├─ Belum → Save attendance PRESENT
  │    └─ Sudah → Show "Sudah absen hari ini"
  │
  └─ Check: Telat?
       ├─ On time (before expected) → Status: ON TIME
       ├─ Telat (>5 min) → Status: LATE
       │    ├─ Hitung duration: (actual time - expected time)
       │    ├─ Hitung: Deduction amount = (duration / 15 min) × rate
       │    └─ Save: Late record dengan deduction amount
       └─ Show confirmation screen
            ├─ Attendance saved
            ├─ Your time: 09:15
            ├─ Expected time: 09:00
            ├─ Status: LATE 15 minutes
            ├─ Potential deduction: -50,000
            └─ Button: Kembali ke dashboard
```

#### 4. Attendance Failed Flow

```
User scan QR → System validate
  ↓
Jika OUTSIDE radius:
  ├─ Show: "❌ Lokasi tidak valid"
  ├─ Show: "Anda berada di luar area cafe (>100 meter)"
  ├─ Show: "Silahkan datang ke cafe untuk absen"
  └─ Tidak save attendance

Jika Di luar jam kerja:
  ├─ Show: "❌ Bukan jam kerja"
  ├─ Show: "Jam kerja: 09:00 - 17:00 (Shift 1) atau 17:00 - 00:00 (Shift 2)"
  └─ Tidak save attendance

Jika Sudah absen:
  ├─ Show: "✓ Anda sudah absen hari ini"
  ├─ Show: "Waktu: 09:05 AM"
  ├─ Show: "Status: ON TIME"
  └─ Option: Kembali ke dashboard

Invalid QR:
  ├─ Show: "❌ QR tidak terdaftar"
  ├─ Show: "Hubungi admin untuk konfirmasi"
  └─ Tidak save attendance
```

---

## 3. DATABASE SCHEMA (3NF - Third Normal Form)

### Normalisasi Approach:

**1st Normal Form (1NF)**: Semua field atomik (tidak ada repeating groups)
**2nd Normal Form (2NF)**: Semua attributes fully dependent pada primary key
**3rd Normal Form (3NF)**: Tidak ada transitive dependencies

### ENTITIES & RELATIONSHIPS

```
┌─────────────────────────────────────────────────────────────┐
│                                                              │
│  users                                                       │
│  ├─ id (PK)                                                 │
│  ├─ username (UNIQUE)                                       │
│  ├─ email (UNIQUE)                                          │
│  ├─ password                                                │
│  ├─ role (admin/employee)                                   │
│  ├─ status (active/inactive)                                │
│  ├─ created_at, updated_at                                  │
│                                                              │
│  ├─── HAS ONE ──→ employees                                 │
│  └─── HAS MANY ─→ attendance_records                        │
│                                                              │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  employees                                                   │
│  ├─ id (PK)                                                 │
│  ├─ user_id (FK to users - UNIQUE)                          │
│  ├─ full_name                                               │
│  ├─ phone                                                   │
│  ├─ basic_salary                                            │
│  ├─ shift_id (FK to shifts)                                 │
│  ├─ late_deduction_amount (per 15 min)                      │
│  ├─ hire_date                                               │
│  ├─ status (active/inactive)                                │
│  ├─ created_at, updated_at                                  │
│                                                              │
│  ├─── BELONGS TO ── users                                   │
│  ├─── BELONGS TO ── shifts                                  │
│  ├─── HAS MANY ─→ attendance_records                        │
│  ├─── HAS MANY ─→ late_records                              │
│  └─── HAS MANY ─→ salary_calculations                       │
│                                                              │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  shifts                                                      │
│  ├─ id (PK)                                                 │
│  ├─ name (misal: "Shift 1", "Shift 2")                      │
│  ├─ start_time (HH:MM)                                      │
│  ├─ end_time (HH:MM)                                        │
│  ├─ expected_arrival_time (HH:MM)                           │
│  ├─ late_threshold_minutes (misal: 5)                       │
│  ├─ created_at, updated_at                                  │
│                                                              │
│  ├─── HAS MANY ─→ employees                                 │
│  ├─── HAS MANY ─→ qr_codes                                  │
│  └─── HAS MANY ─→ attendance_records                        │
│                                                              │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  qr_codes                                                    │
│  ├─ id (PK)                                                 │
│  ├─ shift_id (FK to shifts)                                 │
│  ├─ name                                                    │
│  ├─ code (UNIQUE - QR string/hash)                          │
│  ├─ latitude                                                │
│  ├─ longitude                                               │
│  ├─ radius_meters (default: 100)                            │
│  ├─ active_from (HH:MM)                                     │
│  ├─ active_until (HH:MM)                                    │
│  ├─ is_active (boolean)                                     │
│  ├─ created_at, updated_at                                  │
│                                                              │
│  ├─── BELONGS TO ── shifts                                  │
│  └─── HAS MANY ─→ attendance_records                        │
│                                                              │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  attendance_records                                          │
│  ├─ id (PK)                                                 │
│  ├─ employee_id (FK to employees)                           │
│  ├─ qr_id (FK to qr_codes)                                  │
│  ├─ shift_id (FK to shifts)                                 │
│  ├─ date (DATE)                                             │
│  ├─ check_in_time (TIME)                                    │
│  ├─ user_latitude                                           │
│  ├─ user_longitude                                          │
│  ├─ distance_from_qr (meters - untuk validasi)              │
│  ├─ is_late (boolean)                                       │
│  ├─ created_at, updated_at                                  │
│                                                              │
│  ├─── BELONGS TO ── employees                               │
│  ├─── BELONGS TO ── qr_codes                                │
│  ├─── BELONGS TO ── shifts                                  │
│  └─── HAS ONE ──→ late_records (optional)                   │
│                                                              │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  late_records                                                │
│  ├─ id (PK)                                                 │
│  ├─ employee_id (FK to employees)                           │
│  ├─ attendance_id (FK to attendance_records - UNIQUE)       │
│  ├─ shift_id (FK to shifts)                                 │
│  ├─ date (DATE)                                             │
│  ├─ expected_time (TIME dari shift)                         │
│  ├─ actual_time (TIME dari check_in)                        │
│  ├─ duration_minutes ((actual - expected) dalam menit)      │
│  ├─ late_deduction_amount (durasi / 15 × amount/15)         │
│  ├─ created_at                                              │
│                                                              │
│  ├─── BELONGS TO ── employees                               │
│  ├─── BELONGS TO ── attendance_records                       │
│  └─── BELONGS TO ── shifts                                  │
│                                                              │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  salary_calculations                                         │
│  ├─ id (PK)                                                 │
│  ├─ employee_id (FK to employees)                           │
│  ├─ period_start_date (DATE)                                │
│  ├─ period_end_date (DATE)                                  │
│  ├─ base_salary                                             │
│  ├─ total_deduction (total dari late_records di period)     │
│  ├─ final_salary (base - total_deduction)                   │
│  ├─ status (draft/approved/paid)                            │
│  ├─ notes                                                   │
│  ├─ created_at, updated_at, paid_at                         │
│                                                              │
│  └─── BELONGS TO ── employees                               │
│                                                              │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  RELATIONSHIPS SUMMARY:                                      │
│                                                              │
│  users (1) ────→ (1) employees                              │
│  employees (1) ──→ (many) attendance_records                │
│  employees (1) ──→ (many) late_records                      │
│  employees (1) ──→ (many) salary_calculations               │
│  employees (many) ← (1) shifts                              │
│  shifts (1) ────→ (many) qr_codes                           │
│  shifts (1) ────→ (many) attendance_records                 │
│  qr_codes (1) ──→ (many) attendance_records                 │
│  attendance_records (1) ──→ (1) late_records                │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 4. TECHNICAL IMPLEMENTATION NOTES

### Database Normalization Compliance:

**1NF Compliance:**

- Semua attributes atomik
- Tidak ada repeating groups
- Setiap cell berisi single value

**2NF Compliance:**

- Primary keys: (id) untuk semua entities
- Composite key pada late_records & salary_calculations hanya jika diperlukan
- Foreign keys proper defined

**3NF Compliance:**

- `late_records.late_deduction_amount` - tidak tergantung pada transitive
    - Kalkulasi: (duration_minutes / 15) × (employee.late_deduction_amount)
    - Disimpan untuk audit trail & quick access

- `salary_calculations.total_deduction` - aggregate dari late_records
    - Disimpan untuk reporting & approval workflow

- Semua FK properly referenced
- Tidak ada non-prime attributes dependent pada non-prime attributes

### Distance Calculation (GPS Validation):

```php
// Haversine formula untuk mencari jarak antara 2 koordinat GPS
function calculateGPSDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // meters
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}
```

### Late Deduction Calculation:

```
Scenario: Employee expected 09:00, arrive 09:15, late deduction 50000 per 15min

Duration = 09:15 - 09:00 = 15 minutes
Blocks of 15min = 15 / 15 = 1 block
Deduction = 1 × 50000 = 50000

Scenario: Arrive 09:22
Duration = 22 minutes
Blocks of 15min = ceil(22 / 15) = 2 blocks (bisa diatur: exact atau ceil)
Deduction = 2 × 50000 = 100000
```

---

## 5. API ENDPOINTS (Planned)

### ADMIN ENDPOINTS:

- `POST /api/admin/employees` - Create employee
- `GET /api/admin/employees` - List employees
- `PUT /api/admin/employees/{id}` - Update employee
- `GET /api/admin/employees/{id}/salary` - View salary calculation

- `POST /api/admin/qr` - Create QR
- `GET /api/admin/qr` - List QR codes
- `PUT /api/admin/qr/{id}` - Update QR location
- `GET /api/admin/qr/{id}/stats` - QR statistics

- `GET /api/admin/shifts` - List shifts
- `PUT /api/admin/shifts/{id}` - Update shift

- `GET /api/admin/attendance` - Attendance reports
- `GET /api/admin/salary/process` - Process payroll
- `GET /api/admin/salary/{employeeId}/{month}` - Salary detail

### USER ENDPOINTS:

- `POST /api/user/attendance/scan` - Submit QR scan dengan GPS + QR code + timestamp
- `GET /api/user/attendance/today` - Today's status
- `GET /api/user/attendance/history` - Attendance history
- `GET /api/user/salary/deductions` - View potential deductions

---

## 6. VALIDATION RULES

### QR Scan Validation (dalam order):

1. ✓ QR code valid & terdaftar di database
2. ✓ User sudah login
3. ✓ User adalah employee
4. ✓ User dalam radius QR location (GPS distance check)
5. ✓ Sekarang dalam jam kerja shift
6. ✓ QR code dalam jam active nya
7. ✓ User belum absen hari ini (per shift - bisa multi shift)
8. ✓ Check: telat atau tidak based on expected_arrival_time

### Employee Creation Validation:

- Username unique
- Email unique
- Basic salary > 0
- Shift harus valid
- Late deduction amount > 0

---

## 7. DATABASE DESIGN & NORMALIZATION (3NF)

### 📊 Database Overview

SIAREA menggunakan database yang **sudah dinormalisasi hingga 3NF (Third Normal Form)** untuk memastikan:

- ✅ **Konsistensi data** (single source of truth)
- ✅ **Mengurangi redundansi** (tidak ada data duplikasi)
- ✅ **Integritas referensial** (FK constraints)
- ✅ **Query performance** (proper indexing)
- ✅ **Kemudahan maintenance** (mudah update data)

---

### 🔄 Proses Normalisasi: Unnormalized → 1NF → 2NF → 3NF

#### **TAHAP 0: UNNORMALIZED (Data Tidak Terstruktur)**

Bayangkan kita punya 1 spreadsheet raksasa:

```
Employees (UNNORMALIZED - TIDAK BAGUS)
┌─────────────────────────────────────────────────────────────┐
│ emp_id │ name  │ shift_info   │ attendance_data         │
├─────────────────────────────────────────────────────────────┤
│ 1      │ Budi  │ "Shift 1, 9-17" │ "2/11 09:30, 2/12 09:15" │
│ 2      │ Siti  │ "Shift 2, 17-00" │ "2/11 17:10, 2/12 17:00" │
└─────────────────────────────────────────────────────────────┘

❌ MASALAH:
  - Repeating groups (attendance di 1 kolom)
  - Sulit query dan parse
  - Redundansi data
```

#### **TAHAP 1: 1NF (First Normal Form) - Hilangkan Repeating Groups**

Tujuan: Setiap kolom hanya berisi **atomic value** (nilai tunggal)

```
USERS
┌──────────────────────────────────────┐
│ id │ email           │ password │    │
├──────────────────────────────────────┤
│ 1  │ budi@cafe.com   │ hashed   │    │
│ 2  │ siti@cafe.com   │ hashed   │    │
└──────────────────────────────────────┘

SHIFTS
┌─────────────────────────────────────┐
│ id │ name    │ start  │ end    │    │
├─────────────────────────────────────┤
│ 1  │ Shift 1 │ 09:00  │ 17:00  │    │
│ 2  │ Shift 2 │ 17:00  │ 00:00  │    │
└─────────────────────────────────────┘

EMPLOYEES
┌─────────────────────────────────────┐
│ id │ user_id │ shift_id │ salary │  │
├─────────────────────────────────────┤
│ 1  │ 1       │ 1        │ 3M     │  │
│ 2  │ 2       │ 2        │ 2.5M   │  │
└─────────────────────────────────────┘

ATTENDANCE_RECORDS
┌──────────────────────────────────────────┐
│ id │ emp_id │ date   │ check_in_time │  │
├──────────────────────────────────────────┤
│ 1  │ 1      │ 2-11   │ 09:30         │  │
│ 2  │ 1      │ 2-12   │ 09:15         │  │
│ 3  │ 2      │ 2-11   │ 17:10         │  │
└──────────────────────────────────────────┘

✅ Keuntungan: Atomic values, queryable
```

#### **TAHAP 2: 2NF (Second Normal Form) - Hilangkan Partial Dependencies**

Tujuan: Setiap kolom **non-key harus fully depend** pada **primary key**

```
Problem: ATTENDANCE punya (id, emp_id, date, shift_id)
  - shift_id hanya depend pada emp_id (partial dependency!)

Solution: Hapus shift_id dari ATTENDANCE (ambil dari EMPLOYEES saat join)

✅ Solusi: Pisahkan apa yang hanya depend pada sebagian key
```

#### **TAHAP 3: 3NF (Third Normal Form) - Hilangkan Transitive Dependencies**

Tujuan: Setiap kolom **non-key harus directly depend** pada **primary key**

```
Problem: EMPLOYEES punya (id, user_id, shift_id, basic_salary, late_deduction_amt)
  - late_deduction_amount adalah property SHIFT, bukan EMPLOYEE!
  - Transitive: id → shift_id → late_deduction_amount

Solution: Pindahkan late_deduction_amount ke SHIFTS

SHIFTS (diperbaiki)
┌──────────────────────────────────────────┐
│ id │ name    │ start  │ end   │ deduct │
├──────────────────────────────────────────┤
│ 1  │ Shift 1 │ 09:00  │ 17:00 │ 50k    │
│ 2  │ Shift 2 │ 17:00  │ 00:00 │ 50k    │
└──────────────────────────────────────────┘

✅ Keuntungan 3NF:
  - Single source of truth (shift info 1 tempat)
  - Update shift otomatis berlaku semua employee
  - Konsistensi terjaga
```

---

### 📋 Struktur Database SIAREA (3NF - FINAL)

#### **1. USERS** (Base Authentication)

```
users
├─ id (PK)
├─ email (UNIQUE)
├─ password (hashed)
├─ role (admin/employee)
├─ status (active/inactive)
└─ timestamps

✅ 3NF: Hanya auth data, no redundancy
```

#### **2. SHIFTS** (Master Jadwal Kerja)

```
shifts
├─ id (PK)
├─ name (UNIQUE) - "Shift 1", "Shift 2"
├─ start_time - "09:00"
├─ end_time - "17:00"
├─ expected_arrival_time - "09:00"
├─ late_threshold_minutes - 5
└─ timestamps

✅ 3NF: Single source of truth untuk jam kerja
```

#### **3. EMPLOYEES** (Master Karyawan)

```
employees
├─ id (PK)
├─ user_id (FK → users, UNIQUE)
├─ shift_id (FK → shifts)
├─ full_name
├─ phone
├─ basic_salary (atomic, not composite)
├─ late_deduction_amount
├─ hire_date
├─ status
└─ timestamps

✅ 3NF:
  - user_id: FK reference, not duplicate data
  - shift_id: FK reference, tidak store jam kerja
```

#### **4. QR_CODES** (Location-Based QR Points)

```
qr_codes
├─ id (PK)
├─ shift_id (FK → shifts)
├─ code (UNIQUE) - QR encoded data
├─ name - "Area Kasir"
├─ latitude - GPS coords
├─ longitude - GPS coords
├─ radius_meters - 20m radius
└─ timestamps

✅ 3NF: Tidak duplikasi shift info
```

#### **5. ATTENDANCE_RECORDS** (Check-in Records)

```
attendance_records
├─ id (PK)
├─ employee_id (FK → employees)
├─ qr_id (FK → qr_codes)
├─ shift_id (FK → shifts)
├─ date - "2026-02-11"
├─ check_in_time - "09:30:45"
├─ user_latitude - GPS saat scan
├─ user_longitude - GPS saat scan
├─ distance_from_qr - 15 meters
├─ is_late - boolean flag
├─ UNIQUE(employee_id, date, shift_id)
└─ timestamps

✅ 3NF: Hanya check-in data, no data duplication
```

#### **6. ATTENDANCE_CHECKOUTS** (Check-out Records - Optional)

```
attendance_checkouts
├─ id (PK)
├─ attendance_id (FK → attendance_records, UNIQUE)
├─ check_out_time - "17:00:30"
├─ user_latitude - GPS saat checkout
├─ user_longitude - GPS saat checkout
├─ distance_from_qr - 12 meters
├─ is_early - boolean
└─ timestamps

✅ 3NF: 1:1 optional relation, pisah dari attendance
```

#### **7. LATE_RECORDS** (Calculated Late Deductions)

```
late_records
├─ id (PK)
├─ employee_id (FK → employees)
├─ attendance_id (FK → attendance_records, UNIQUE)
├─ shift_id (FK → shifts)
├─ date - "2026-02-11"
├─ expected_time - "09:00:00"
├─ actual_time - "09:30:00"
├─ duration_minutes - 30
├─ late_deduction_amount - 100,000 (stored for audit)
└─ timestamps

✅ 3NF:
  - Pisah dari attendance
  - late_deduction_amount calculated saat record created
  - Used for audit trail & quick access
```

#### **8. SALARY_CALCULATIONS** (Monthly Salary Summary)

```
salary_calculations
├─ id (PK)
├─ employee_id (FK → employees)
├─ period_date - "2026-02-01"
├─ base_salary - 3,000,000 (from employee snapshot)
├─ total_deduction - 200,000 (SUM from late_records)
├─ final_salary - 2,800,000 (calculated difference)
├─ status - draft/approved/paid
├─ UNIQUE(employee_id, period_date)
└─ timestamps

✅ 3NF:
  - base_salary: snapshot saat period (atomic)
  - total_deduction: aggregate dari late_records
  - final_salary: calculated field
```

---

### ✅ Verifikasi 3NF - SIAREA Database

#### **Checklist 3NF:**

**1. 1NF (Atomicity)** ✅

- ✅ Semua kolom atomic (tidak repeating groups)
- ✅ attendance_records: tidak store array absen
- ✅ late_records: pisah per record
- ✅ salary_calculations: pisah per bulan

**2. 2NF (No Partial Dependencies)** ✅

- ✅ employees tidak store shift_info (hanya FK)
- ✅ attendance_records tidak store employee_name (hanya emp_id)
- ✅ Semua non-key depend fully pada PK

**3. 3NF (No Transitive Dependencies)** ✅

- ✅ late_deduction_amount → pindah dari employees ke shifts
- ✅ attendance_records tidak store qr_location (hanya qr_id)
- ✅ salary_calculations: store calculated fields saja

#### **Keuntungan 3NF di SIAREA:**

```
✅ KONSISTENSI
  - Update shift start_time → berlaku untuk semua attendance
  - Tidak perlu update banyak tempat
  - Single source of truth

✅ MENGURANGI REDUNDANSI
  - employee_info disimpan 1x
  - Reference pakai FK, bukan duplikasi

✅ INTEGRITAS REFERENSIAL
  - FK constraints enforce valid data
  - Tidak bisa delete shift yang masih dipakai

✅ QUERY PERFORMANCE
  - Indexes di FK & date columns
  - JOIN operations optimal

✅ UPDATE/DELETE SAFETY
  - ON DELETE CASCADE → cleanup otomatis
  - ON DELETE RESTRICT → cegah operasi berbahaya
```

---

### 🚫 Anti-Pattern yang DIHINDARI:

```sql
❌ JANGAN SEPERTI INI (Unnormalized):
CREATE TABLE employees_bad (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    shift_info VARCHAR(500),      -- Composite data!
    all_salaries TEXT,            -- Repeating group!
    calculated_data JSON          -- Terlalu kompleks!
);

✅ MALAH SEPERTI INI (SIAREA - 3NF):
CREATE TABLE employees (
    id INT PRIMARY KEY,
    user_id INT UNIQUE,
    shift_id INT,                 -- FK reference
    full_name VARCHAR(100),
    basic_salary DECIMAL(15,2),   -- Atomic value
    late_deduction_amount DECIMAL(15,2),
    FOREIGN KEY (shift_id) REFERENCES shifts(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE shifts (
    id INT PRIMARY KEY,
    name VARCHAR(100) UNIQUE,
    start_time TIME,
    end_time TIME,
    late_threshold_minutes INT
);
```

---

### 🎯 Kesimpulan

**SIAREA Database = 3NF Compliant** ✅

- ✅ Tidak ada repeating groups (1NF)
- ✅ Tidak ada partial dependencies (2NF)
- ✅ Tidak ada transitive dependencies (3NF)
- ✅ Semua relasi pakai Foreign Keys
- ✅ Integrity constraints di-enforce
- ✅ Query optimal dengan proper indexing
- ✅ Mudah maintain & scale untuk pertumbuhan
