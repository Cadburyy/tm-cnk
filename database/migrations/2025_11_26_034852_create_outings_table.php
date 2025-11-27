<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outings', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('akun')->nullable();
            $table->string('voucher')->nullable();
            $table->string('nama')->nullable();
            $table->string('nama_pt')->nullable();
            $table->string('part')->nullable();
            $table->string('keterangan')->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outings');
    }
};