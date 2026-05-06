# SISTEM ABSEN CAFE AREA - README

## 📋 Overview

**Sistem Absen Cafe Area** adalah **fullstack web application** attendance management system untuk cafe yang menggunakan:

- **QR Code scanning** dengan GPS location validation
- **Shift-based attendance** (2 shifts per hari)
- **Automatic salary deduction** untuk keterlambatan
- **Admin dashboard** untuk employee & QR management
- **Real-time calculations** dengan live dashboard

---

## 🎯 Fitur Utama

### Admin Features

✅ **Employee Management**

- Create, read, update, delete employees
- Assign shift & manage salary
- View attendance history & statistics
- Reset employee password

✅ **QR Location Setup**

- Create QR codes dengan GPS coordinates
- Set radius area (default 100m)
- Configure active hours per QR
- Monitor QR usage & scan history
- Activate/deactivate QR codes

✅ **Salary Management**

- Calculate salary dengan automatic deduction
- View salary breakdown (base - deduction = final)
- Approve/reject salary calculations
- Mark as paid & track payment history
- Generate payroll reports
- Bulk approve/pay operations

✅ **Attendance Reports**

- View detailed attendance records
- Filter by date range, employee, status
- Export reports

✅ **Dashboard Insights**

- KPI cards: total employees, today attendance, late count
- Monthly statistics
- Recent activity monitoring
- Quick action buttons

---

## 🏗️ Architecture: Fullstack Web Application

Aplikasi ini didesain sebagai **single fullstack Laravel application**:

- **Backend**: Server-side business logic dalam services
- **Frontend**: Blade template dengan Bootstrap 5
- **Authentication**: Session-based (tidak perlu API)
- **Data Access**: Direct model queries via controllers

**No separate API** - semua operasi melalui web controllers dan Laravel session management.

### Flow

```
User Input (Form) → Web Controller → Service/Model → Database → Blade View
```

---

## 🗄️ Database Architecture (3NF Normalized)

### Entity Relationship Diagram

```
┌─────────────────┐
│     users       │ (Role: admin/employee)
├─────────────────┤
│ id (PK)         │
│ name (UQ)       │
│ email (UQ)      │
│ password        │
│ role            │
│ status          │
└────────┬────────┘
         │ (1:1)
         ↓
┌─────────────────────────┐
│      employees          │
├─────────────────────────┤
│ id (PK)                 │
│ user_id (FK, UQ)        │
│ shift_id (FK)           │
│ full_name               │
│ phone                   │
│ basic_salary            │
│ late_deduction_amount   │
│ hire_date               │
│ status                  │
└──┬───────────────────┬──┘
   │ (M:1)             │ (1:M)
   ↓                   ↓
┌──────────┐    ┌──────────────────┐
│  shifts  │    │ attendance_records│
│  (1:M)   │    │    (1:M)          │
└──────────┘    └────────┬──────────┘
   ↓                     │
   │ (1:M)               │ (1:1)
   ↓                     ↓
┌──────────┐    ┌──────────────────┐
│ qr_codes │    │  late_records    │
└──────────┘    └──────────────────┘

                 ┌──────────────────┐
                 │salary_calculations
                 │    (1:M each)
                 └──────────────────┘
```

### Key Tables

| Table                 | Purpose                        | Key Fields                                      |
| --------------------- | ------------------------------ | ----------------------------------------------- |
| `users`               | Authentication & authorization | id, username, role, status                      |
| `employees`           | Employee master data           | id, user_id, shift_id, salary info              |
| `shifts`              | Work shifts definition         | id, name, start_time, end_time                  |
| `qr_codes`            | QR location setup              | id, shift_id, latitude, longitude, radius       |
| `attendance_records`  | Absen records                  | id, employee_id, qr_id, date, time, GPS coords  |
| `late_records`        | Late tracking & deduction      | id, attendance_id, duration, deduction_amount   |
| `salary_calculations` | Salary per period              | id, employee_id, base, deduction, final, status |

---

## 🔄 Business Logic Flow

### A. User Attendance Flow

```
User Login
  ↓
Open Mobile App → Dashboard
  ├─ Shows: Shift info, Expected time, Today's status
  └─ Button: "SCAN QR SEKARANG"
     ↓
  Kamera opens → Auto-detect QR code
     ↓
  System validasi:
     ├─ QR valid & active?
     ├─ GPS dalam radius?
     ├─ Waktu dalam operating hours?
     ├─ Shift sesuai?
     └─ Belum absen hari ini?
     ↓
  If ALL valid:
     ├─ Check: Telat atau on-time
     ├─ If ON TIME → Create attendance_record (is_late=false)
     └─ If LATE → Create attendance_record + late_record
        ├─ Calculate: (actual - expected) dalam menit
        ├─ Blocks = ceil(minutes / 15)
        ├─ Deduction = blocks × rate
        └─ Save late_record dengan deduction
     ↓
  Show Success Screen:
     ├─ ✓ Status (ON TIME / LATE)
     ├─ ✓ Time confirmed
     ├─ ✓ Potential deduction (if late)
     └─ ✓ Location verified
```

### B. Admin Salary Processing

```
Month End
  ↓
Admin → /admin/salary/calculate
  ├─ Select employees & period
  ├─ System queries late_records untuk bulan tersebut
  ├─ Calculate total_deduction per employee
  ├─ Create salary_calculation records
  └─ Status: "draft"

Admin → /admin/salary (management dashboard)
  ├─ View calculations by month/year
  ├─ /admin/salary/report - See summary stats
  ├─ /admin/salary/{id}/approve - Approve single
  ├─ /admin/salary/bulk-approve - Approve multiple
  ├─ /admin/salary/{id}/mark-paid - Mark paid single
  ├─ /admin/salary/bulk-mark-paid - Mark paid multiple
  └─ Status flow: draft → approved → paid
```

### C. Late Deduction Calculation Example

```
**Scenario:**
- Expected arrival: 09:00 AM
- Actual arrival: 09:20 AM
- Late deduction per 15 min: 50,000

**Calculation:**
- Duration = 09:20 - 09:00 = 20 minutes
- Blocks = ceil(20 / 15) = 2 blocks
- Deduction = 2 × 50,000 = 100,000

**Result:**
- Saved in late_records table
- Included in salary_calculations.total_deduction
- Displayed to employee in salary deduction preview
- Deducted from final salary
```

---

## 🛣️ Web Routes Architecture

```
Base URL: http://localhost:8000

PUBLIC ROUTES
├─ GET  /login                  Login form
└─ POST /login                  Process login

ADMIN ROUTES (Protected: auth middleware + admin role)
├─ GET  /admin                  Dashboard

├─ /admin/employees
│  ├─ GET  /                    List employees (search, filter, paginate)
│  ├─ GET  /create              Create form
│  ├─ POST /                    Store employee
│  ├─ GET  /{id}                Show detail (profile, stats, attendance)
│  ├─ GET  /{id}/edit           Edit form
│  ├─ PUT  /{id}                Update
│  └─ DELETE /{id}              Delete

├─ /admin/qr
│  ├─ GET  /                    List QR codes (filter by shift/status)
│  ├─ GET  /create              Create form (interactive map)
│  ├─ POST /                    Store QR code
│  ├─ GET  /{id}                Show detail (QR image, map, scans)
│  ├─ GET  /{id}/edit           Edit form (map update)
│  ├─ PUT  /{id}                Update
│  ├─ DELETE /{id}              Delete
│  └─ GET  /{id}/statistics     Statistics (performance, scan history)

├─ /admin/salary
│  ├─ GET  /                    List salary records (month/year/status filter)
│  ├─ GET  /calculate           Calculate form (select employees, period)
│  ├─ POST /                    Store calculations
│  ├─ GET  /{id}                Show detail (breakdown, deductions)
│  ├─ POST /{id}/approve        Approve single record
│  ├─ POST /bulk-approve        Bulk approve selected
│  ├─ POST /{id}/mark-paid      Mark single as paid
│  ├─ POST /bulk-mark-paid      Bulk mark paid
│  ├─ GET  /report              Salary report (summary, stats, export)
│  ├─ GET  /employee/{id}/history  Employee salary history
│  └─ GET  /export              CSV export

└─ GET /admin/attendance-report Attendance report (search, filter, stats)

SESSION MANAGEMENT
└─ POST /logout                 Logout
```

---

## 📚 Documentation Files

| File                          | Purpose                                                   |
| ----------------------------- | --------------------------------------------------------- |
| **SYSTEM_DESIGN.md**          | Architecture, ER diagram, logical flow, 3NF normalization |
| **INSTALLATION_GUIDE.md**     | Setup, configuration, deployment instructions             |
| **FRONTEND_DOCUMENTATION.md** | Frontend structure, components, styling, web routes       |
| **FRONTEND_TESTING_GUIDE.md** | Frontend testing checklist & troubleshooting              |
| **README_SISTEM.md**          | This file - Overview & quick reference                    |

---

## 🚀 Quick Start

### 1. Setup

```bash
# Clone/setup repository
cd /d/TA/SIAREA

# Install dependencies
composer install

# Create .env & configure database
cp .env.example .env
# Edit DB credentials in .env

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed initial data
php artisan db:seed

# Start server
php artisan serve
```

### 2. First Test

**Login to Admin Portal:**

```
1. Open http://localhost:8000/login
2. Enter credentials:
   - Email: admin@siarea.local
   - Password: password
3. Access dashboard at http://localhost:8000/admin
```

**Test Features:**

```
1. Create QR Code
   - Navigate: /admin/qr/create
   - Click map to select location
   - Form: name, active_from, active_until, radius_meters
   - Submit to store

2. Create Employee
   - Navigate: /admin/employees/create
   - Fill: name, email, phone, shift, salary, late_deduction
   - Select shift (Pagi/Sore)
   - Submit to store

3. Calculate Salary
   - Navigate: /admin/salary/calculate
   - Select period (month/year)
   - Select employees
   - Submit to generate calculations
```

---

## 🔐 Security Considerations

✅ **Implemented:**

- Laravel Sanctum token-based authentication
- Role-based middleware (admin/employee)
- SQL injection prevention (Eloquent ORM)
- CSRF protection ready
- Password hashing (bcrypt)

⚠️ **TODO for Production:**

- Rate limiting on auth endpoints
- Request validation & sanitization
- HTTPS enforcement
- CORS configuration
- Audit logging
- 2FA for admin accounts

---

## 📊 Data Validation Rules

### Employee Creation

- Name: required, string, max 255
- Username: required, unique, string
- Email: required, unique, email format
- Phone: required, max 20
- Shift: required, exists in shifts table
- Basic Salary: required, numeric, > 0
- Late Deduction: required, numeric, > 0
- Hire Date: required, valid date

### QR Code Creation

- Shift: required, exists
- Name: required, string
- Latitude: required, numeric, -90 to 90
- Longitude: required, numeric, -180 to 180
- Radius: optional, integer, 10-1000 (default 100)
- Active From/Until: required, time format (HH:MM:SS)

### QR Scan (Attendance)

- QR Code: required, valid & registered
- Latitude: required, -90 to 90
- Longitude: required, -180 to 180
- User must be active & have employee record
- Must not have scanned today for this shift

---

## 🔄 Key Business Rules

### Attendance Rules

1. ✓ QR code valid & active
2. ✓ User dalam GPS radius
3. ✓ Waktu dalam active hours
4. ✓ Shift cocok dengan employee
5. ✓ Belum absen untuk shift ini hari ini

### Late Rules

1. Expected time dari shift.expected_arrival_time
2. Durasi telat = actual - expected (dalam menit)
3. Dibulatkan ke atas per 15 menit (ceil)
4. Deduction = blocks × employee.late_deduction_amount

### Salary Rules

1. Salary dihitung per periode (bulanan)
2. Base salary dari employee.basic_salary
3. Deduction dari total late_records dalam periode
4. Final salary = base - deduction
5. Flow: draft → approved → paid

---

## 📈 GPS Validation Method

**Haversine Formula** untuk menghitung jarak 2 titik GPS:

```
a = sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlon/2)
c = 2 × atan2(√a, √(1−a))
distance = R × c

R = Earth radius (6,371,000 meters)
```

**Validasi:**

- User GPS distance ≤ QR radius → VALID ✓
- User GPS distance > QR radius → INVALID ✗

---

## 🛠️ Tech Stack

- **Framework**: Laravel 10+
- **Database**: MySQL/MariaDB
- **Authentication**: Session-based (Laravel auth)
- **ORM**: Eloquent
- **Language**: PHP 8.1+
- **Frontend**: Blade templates + Bootstrap 5
- **Package Manager**: Composer
- **Database Tool**: Artisan Migrations

---

## 📝 Shift Configuration

### Current Setup

```
Shift 1:
- Start: 09:00
- End: 17:00
- Expected Arrival: 09:00
- Late Threshold: 5 minutes

Shift 2:
- Start: 17:00
- End: 00:00 (midnight)
- Expected Arrival: 17:00
- Late Threshold: 5 minutes
```

### Modifiable per Shift:

- Start & end time
- Expected arrival time
- Late threshold definition

---

## 🚦 Status Codes

| Code | Meaning                              |
| ---- | ------------------------------------ |
| 200  | OK - Request successful              |
| 201  | Created - Resource created           |
| 400  | Bad Request - Validation error       |
| 401  | Unauthorized - Auth required         |
| 403  | Forbidden - Insufficient permissions |
| 404  | Not Found - Resource missing         |
| 500  | Server Error                         |

---

## 📞 Useful Commands

```bash
# Development
php artisan serve                    # Start server
php artisan tinker                   # Interactive shell

# Database
php artisan migrate                  # Run migrations
php artisan migrate:refresh          # Reset migrations
php artisan db:seed                  # Run seeders
php artisan migrate:fresh --seed     # Full reset

# Cache/Clear
php artisan cache:clear              # Clear cache
php artisan config:clear             # Clear config cache
php artisan route:clear              # Clear route cache
php artisan view:clear               # Clear view cache

# Optimization (production)
php artisan config:cache             # Cache config
php artisan route:cache              # Cache routes
php artisan view:cache               # Cache views
```

---

## 📖 Project Structure

```
siarea/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── EmployeeController.php
│   │   │   │   ├── QRController.php
│   │   │   │   └── SalaryController.php
│   │   │   └── User/
│   │   │       └── AttendanceController.php
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php
│   │   │   └── EmployeeMiddleware.php
│   │   └── Kernel.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Employee.php
│   │   ├── Shift.php
│   │   ├── QrCode.php
│   │   ├── AttendanceRecord.php
│   │   ├── LateRecord.php
│   │   └── SalaryCalculation.php
│   └── Services/
│       ├── AttendanceService.php
│       └── SalaryService.php
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── web.php                     # Web routes (admin portal)
├── SYSTEM_DESIGN.md                # Full system design
├── FRONTEND_DOCUMENTATION.md       # Frontend components & routes
├── INSTALLATION_GUIDE.md           # Setup & deployment guide
└── README_SISTEM.md                # This file
```

---

## 🎯 Deployment Checklist

- [ ] Configure production database
- [ ] Update .env (APP_ENV=production, APP_DEBUG=false)
- [ ] Generate app key
- [ ] Run migrations
- [ ] Set storage permissions
- [ ] Cache config & routes
- [ ] Setup SSL certificate
- [ ] Configure CORS
- [ ] Setup email configuration
- [ ] Monitor logs (/storage/logs/)

---

## 📞 Support & Issues

Lihat dokumentasi lengkap:

- **System Flow**: SYSTEM_DESIGN.md (section: Alur Sistem)
- **Frontend Routes**: FRONTEND_DOCUMENTATION.md (section: Web Routes)
- **Installation**: INSTALLATION_GUIDE.md (section: Setup & Troubleshooting)

---

**Status**: ✅ Ready for Development & Testing

Last Updated: February 10, 2026
