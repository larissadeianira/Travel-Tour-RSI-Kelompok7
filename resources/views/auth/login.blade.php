@extends('layouts.app')
@section('title', 'Sign In - Travel Tour')

@section('content')
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-brand">
            <div>
                <div class="brand-logo">T✦</div>
                <h1>TRAVEL TOUR</h1>
                <p>Jelajahi Keindahan Indonesia. Rasakan setiap pengalaman baru di berbagai destinasi wisata bersama layanan perjalanan terorganisir.</p>
                <div class="brand-decoration">
                    <span>EXPLORE</span>
                    <span>DISCOVER</span>
                    <span>TRAVEL</span>
                </div>
            </div>
            <a href="{{ route('travel.katalog') }}" class="auth-guest-link">
                ← Lanjut sebagai Umum (Lihat Katalog)
            </a>
        </div>

        <div class="auth-card">
            <div class="auth-heading">
                <span class="eyebrow">WELCOME BACK</span>
                <h2>Sign In</h2>
                <p>Masuk dengan kredensial akun atau gunakan akun demo per role.</p>
            </div>

            @if (session('success'))
                <div class="tt-notice">✓ {{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="tt-error" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" id="loginForm">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                <div class="form-options">
                    <label class="remember">
                        <input type="checkbox" name="remember" value="1"> Ingat saya
                    </label>
                </div>
                <button type="submit" class="btn-primary-auth">
                    <span>MASUK SEKARANG</span>
                    <span>↗</span>
                </button>
            </form>

            <div class="demo-box">
                <p class="demo-box-title">Akun Demo (Klik untuk langsung masuk)</p>
                <div class="demo-grid">
                    <button type="button" class="demo-chip" onclick="quickDemo('admin@traveltour.test')">Admin</button>
                    <button type="button" class="demo-chip" onclick="quickDemo('staff@traveltour.test')">Staff</button>
                    <button type="button" class="demo-chip" onclick="quickDemo('customer@traveltour.test')">Customer</button>
                    <button type="button" class="demo-chip" onclick="quickDemo('driver@traveltour.test')">Driver</button>
                    <button type="button" class="demo-chip" onclick="quickDemo('manager@traveltour.test')">Manager</button>
                </div>
                <p class="demo-hint">Password default semua akun demo: <code>password</code></p>
            </div>

            <p class="auth-footer">
                Belum punya akun Customer? <a href="{{ route('register') }}">Sign Up sekarang</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function quickDemo(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'password';
    document.getElementById('loginForm').submit();
}
</script>
@endpush