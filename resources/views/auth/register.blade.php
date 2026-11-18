@extends('layouts.app')
@section('title', 'Sign Up - Travel Tour')

@section('content')
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-brand">
            <div>
                <div class="brand-logo">T✦</div>
                <h1>TRAVEL TOUR</h1>
                <p>Mulai petualangan barumu bersama Travel Tour. Buat akun Customer untuk memesan paket wisata dengan mudah.</p>
                <div class="brand-decoration">
                    <span>EXPLORE</span>
                    <span>DISCOVER</span>
                    <span>TRAVEL</span>
                </div>
            </div>
            <a href="{{ route('travel.katalog') }}" class="auth-guest-link">
                ← Kembali ke Katalog Paket
            </a>
        </div>

        <div class="auth-card">
            <div class="auth-heading">
                <span class="eyebrow">JOIN US</span>
                <h2>Sign Up</h2>
                <p>Lengkapi data diri berikut untuk mendaftar sebagai Customer.</p>
            </div>

            @if ($errors->any())
                <div class="tt-error" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.submit') }}">
                @csrf
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Nama lengkap Anda" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="email@contoh.com" required>
                </div>
                <div class="form-group">
                    <label for="nomor_telepon">Nomor Telepon</label>
                    <input type="tel" id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon') }}" placeholder="08xxxxxxxxxx" required>
                </div>
                <div class="tt-inline">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Min. 8 karakter" required>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password" required>
                    </div>
                </div>
                <button type="submit" class="btn-primary-auth">
                    <span>DAFTAR SEKARANG</span>
                    <span>↗</span>
                </button>
            </form>

            <p class="auth-footer">
                Sudah punya akun? <a href="{{ route('login') }}">Sign In sekarang</a>
            </p>
        </div>
    </div>
</div>
@endsection