@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $role = $user?->role ?? 'guest';
@endphp

<div class="tt-mobile-bar">
    <a href="{{ route('travel.katalog') }}" class="font-display" style="color:var(--lime);font-weight:800;font-size:17px;">Travel Tour✦</a>
    <button type="button" class="tt-mobile-toggle" onclick="document.getElementById('mainSidebar').classList.toggle('open')">Menu ☰</button>
</div>

<div class="tt-shell">
    <aside class="tt-sidebar" id="mainSidebar">
        <a href="{{ $role === 'guest' || $role === 'customer' ? route('travel.katalog') : route('dashboard') }}" class="tt-logo">
            Travel<br>Tour<span>✦</span>
        </a>

        <nav class="tt-nav">
            @if ($role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'selected' : '' }}">▦ &nbsp; Dashboard</a>
                <a href="{{ route('admin.paket.index') }}" class="{{ request()->routeIs('admin.paket.*','admin.driver.*','admin.kendaraan.*') ? 'selected' : '' }}">▤ &nbsp; Data Master</a>
                <a href="{{ route('admin.penugasan.index') }}" class="{{ request()->routeIs('admin.penugasan.*') ? 'selected' : '' }}">◎ &nbsp; Penugasan</a>
            @elseif ($role === 'staff')
                <a href="{{ route('staff.dashboard') }}" class="{{ request()->routeIs('staff.dashboard') ? 'selected' : '' }}">▦ &nbsp; Dashboard</a>
                <a href="{{ route('pelanggan.index') }}" class="{{ request()->routeIs('pelanggan.*') ? 'selected' : '' }}">☺ &nbsp; Pelanggan</a>
                <a href="{{ route('staff.pemesanan.index') }}" class="{{ request()->routeIs('staff.pemesanan.*') ? 'selected' : '' }}">▤ &nbsp; Pemesanan</a>
                <a href="{{ route('staff.pembayaran.index') }}" class="{{ request()->routeIs('staff.pembayaran.*','staff.verifikasi') ? 'selected' : '' }}">◈ &nbsp; Pembayaran</a>
                <a href="{{ route('staff.laporan.index') }}" class="{{ request()->routeIs('staff.laporan.*') ? 'selected' : '' }}">▥ &nbsp; Laporan</a>
            @elseif ($role === 'driver')
                <a href="{{ route('driver.jadwal.index') }}" class="{{ request()->routeIs('driver.jadwal.*') ? 'selected' : '' }}">▦ &nbsp; Jadwal Tour</a>
            @elseif ($role === 'manager')
                <a href="{{ route('manager.laporan.index') }}" class="{{ request()->routeIs('manager.laporan.*') ? 'selected' : '' }}">▥ &nbsp; Laporan Transaksi</a>
                <a href="{{ route('pelanggan.index') }}" class="{{ request()->routeIs('pelanggan.*') ? 'selected' : '' }}">☺ &nbsp; Data Pelanggan</a>
            @else
                <a href="{{ route('travel.katalog') }}" class="{{ request()->routeIs('travel.katalog','travel.detail','customer.pemesanan.*') ? 'selected' : '' }}">✦ &nbsp; Paket Travel</a>
                @if ($role === 'customer')
                    <a href="{{ route('customer.pesanan.index') }}" class="{{ request()->routeIs('customer.pesanan.*') ? 'selected' : '' }}">▤ &nbsp; Pesanan Saya</a>
                    <a href="{{ route('customer.profil.edit') }}" class="{{ request()->routeIs('customer.profil.*') ? 'selected' : '' }}">☺ &nbsp; Profil &amp; Identitas</a>
                @endif
            @endif
        </nav>

        <div class="tt-bottom">
            @auth
                <div class="tt-user-pill">
                    <strong>{{ $user->name }}</strong>
                    <small>{{ ucfirst($role) }}</small>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="tt-logout-btn">Keluar</button>
                </form>
            @else
                <div class="tt-user-pill">
                    <strong>Guest (Umum)</strong>
                    <small>Pengunjung Publik</small>
                </div>
                <div class="tt-guest-actions">
                    <a href="{{ route('login') }}" class="tt-guest-btn">Masuk</a>
                    <a href="{{ route('register') }}" class="tt-guest-btn outline">Daftar</a>
                </div>
            @endauth
        </div>
    </aside>

    <main class="tt-main">
        @if (session('success'))
            <div class="tt-notice" role="status">✓ {{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="tt-warning" role="status">! {{ session('warning') }}</div>
        @endif
        @if (session('error'))
            <div class="tt-error" role="alert">✕ {{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="tt-error" role="alert">
                <strong>Periksa kembali data yang dimasukkan:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('panel-content')
    </main>
</div>
@endsection
```

##### `resources/views/vendor/pagination/travel.blade.php`
```blade
@if ($paginator->hasPages())
    <nav class="tt-pagination" aria-label="Navigasi halaman">
        <ul class="pagination-bar">
            @if ($paginator->onFirstPage())
                <li class="disabled"><span>&laquo;</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="disabled"><span>{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a></li>
            @else
                <li class="disabled"><span>&raquo;</span></li>
            @endif
        </ul>
    </nav>
@endif