<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProdukLokasi extends Pivot
{
    protected $table = 'produk_lokasi';

    protected $fillable = ['produk_id', 'lokasi_id', 'stok'];
}
