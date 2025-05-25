<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('produk_lokasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
            $table->foreignId('lokasi_id')->constrained('lokasis')->onDelete('cascade');
            $table->integer('stok')->default(0);
            $table->timestamps();

            $table->unique(['produk_id', 'lokasi_id']); // hindari duplikat relasi
        });
    }

    public function down()
    {
        Schema::dropIfExists('produk_lokasi');
    }
};
