@extends('layouts.app')

@section('title', 'Profil Admin - SIAREA')

@section('content')
    <div class="page-header mb-4">
        <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">Pengaturan Akun</h1>
        <p class="text-muted mb-0">Kelola informasi kredensial dan keamanan akun Administrator</p>
    </div>

    <div class="row gx-4">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4 p-md-5">
                    <h5 class="fw-bold mb-4" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">
                        <i class="bi bi-key-fill text-cafe me-2"></i> Perbarui Kata Sandi
                    </h5>

                    <form action="{{ route('admin.profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold text-muted">Password Saat Ini</label>
                            <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                <span class="input-group-text border-end-0"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" class="form-control border-start-0 ps-0" id="current_password" name="current_password" required placeholder="••••••••">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold text-muted">Password Baru</label>
                            <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                <span class="input-group-text border-end-0"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control border-start-0 ps-0" id="new_password" name="new_password" required placeholder="••••••••">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="new_password_confirmation" class="form-label fw-semibold text-muted">Konfirmasi Password Baru</label>
                            <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                <span class="input-group-text border-end-0"><i class="bi bi-key-fill"></i></span>
                                <input type="password" class="form-control border-start-0 ps-0" id="new_password_confirmation" name="new_password_confirmation" required placeholder="••••••••">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-cafe w-100 rounded-3 py-2 shadow-sm fw-bold d-flex justify-content-center align-items-center gap-2">
                            <i class="bi bi-check2-circle"></i> Perbarui Kata Sandi
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(to bottom right, #fff, var(--cafe-light)); border: 1px solid var(--cafe-border);">
                <div class="card-body p-4 p-md-5">
                    <h5 class="fw-bold mb-4" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">
                        <i class="bi bi-info-circle-fill text-cafe me-2"></i> Kredensial Saat Ini
                    </h5>

                    <div class="mb-4 d-flex align-items-center gap-3">
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ $user->id }}&backgroundColor=FBF9F6" alt="Avatar" class="rounded-circle border" style="width: 60px; height: 60px;" />
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">{{ $user->name }}</h6>
                            <small class="text-muted">Hak Akses: Administrator</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted d-block">Username</label>
                        <p class="fw-bold text-dark fs-6">{{ $user->username ?? '-' }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted d-block">Email Terdaftar</label>
                        <p class="fw-bold text-dark fs-6">{{ $user->email }}</p>
                    </div>

                    <div class="mt-4 pt-3 border-top dashed text-muted">
                        <small><i class="bi bi-shield-fill-check text-success me-1"></i> Sesi akun Anda dienkripsi dan dilindungi oleh protokol keamanan standar SIAREA.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
