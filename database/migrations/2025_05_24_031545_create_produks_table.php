<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::create('produks', function (Blueprint $table) {
        $table->id();
        $table->string('nama_produk');
        $table->string('kode_produk')->unique();
        $table->string('kategori');
        $table->string('satuan');
        $table->text('deskripsi')->nullable(); // opsional
        $table->integer('harga_satuan')->default(0); // opsional
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
