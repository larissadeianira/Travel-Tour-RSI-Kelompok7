<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('paket_travel', 'fasilitas')) {
            Schema::table('paket_travel', function (Blueprint $table) {
                $table->text('fasilitas')->nullable()->after('deskripsi');
            });
        }

        if (! Schema::hasColumn('pembayaran', 'metode_pembayaran')) {
            Schema::table('pembayaran', function (Blueprint $table) {
                $table->string('metode_pembayaran', 40)->default('QRIS')->after('nominal');
            });
        }
    }

    public function down(): void
    {
        Schema::table('paket_travel', function (Blueprint $table) {
            if (Schema::hasColumn('paket_travel', 'fasilitas')) {
                $table->dropColumn('fasilitas');
            }
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            if (Schema::hasColumn('pembayaran', 'metode_pembayaran')) {
                $table->dropColumn('metode_pembayaran');
            }
        });
    }
};