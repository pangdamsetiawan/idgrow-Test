<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Produk;
use App\Models\Lokasi;
use App\Models\Mutasi;

/*
|--------------------------------------------------------------------------
| AUTH LOGIN
|--------------------------------------------------------------------------
*/
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $user->tokens()->delete(); // hapus token lama (optional)

    return response()->json([
        'token' => $user->createToken('api-token')->plainTextToken,
    ]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // ========== CRUD USER ==========
    Route::get('/users', fn() => User::all());

    Route::get('/users/{id}', fn($id) => User::findOrFail($id));

    Route::post('/users', function (Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json($user, 201);
    });

    Route::put('/users/{id}', function (Request $request, $id) {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        $user->update($request->only('name', 'email'));

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
            $user->save();
        }

        return response()->json($user);
    });

    Route::delete('/users/{id}', fn($id) => tap(User::findOrFail($id))->delete());

    // ========== CRUD PRODUK ==========
    Route::get('/produks', fn() => Produk::all());

    Route::get('/produks/{id}', fn($id) => Produk::findOrFail($id));

    Route::post('/produks', function (Request $request) {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kode_produk' => 'required|string|max:100|unique:produks,kode_produk',
            'kategori' => 'nullable|string|max:255',
            'satuan' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'harga_satuan' => 'nullable|integer|min:0',
        ]);

        $produk = Produk::create($request->all());
        return response()->json($produk, 201);
    });

    Route::put('/produks/{id}', function (Request $request, $id) {
        $produk = Produk::findOrFail($id);

        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kode_produk' => 'required|string|max:100|unique:produks,kode_produk,' . $produk->id,
            'kategori' => 'nullable|string|max:255',
            'satuan' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'harga_satuan' => 'nullable|integer|min:0',
        ]);

        $produk->update($request->all());
        return response()->json($produk);
    });

    Route::delete('/produks/{id}', fn($id) => tap(Produk::findOrFail($id))->delete());

    // ========== CRUD LOKASI ==========
    Route::get('/lokasis', fn() => Lokasi::all());

    Route::get('/lokasis/{id}', fn($id) => Lokasi::findOrFail($id));

    Route::post('/lokasis', function (Request $request) {
        $request->validate([
            'kode_lokasi' => 'required|string|max:50|unique:lokasis',
            'nama_lokasi' => 'required|string|max:100',
        ]);

        $lokasi = Lokasi::create($request->only('kode_lokasi', 'nama_lokasi'));
        return response()->json($lokasi, 201);
    });

    Route::put('/lokasis/{id}', function (Request $request, $id) {
        $lokasi = Lokasi::findOrFail($id);

        $request->validate([
            'kode_lokasi' => 'required|string|max:50|unique:lokasis,kode_lokasi,' . $lokasi->id,
            'nama_lokasi' => 'required|string|max:100',
        ]);

        $lokasi->update($request->only('kode_lokasi', 'nama_lokasi'));
        return response()->json($lokasi);
    });

    Route::delete('/lokasis/{id}', fn($id) => tap(Lokasi::findOrFail($id))->delete());

    // ========== STOK PRODUK PER LOKASI ==========
    Route::post('/produk/{produkId}/lokasi/{lokasiId}/stok', function ($produkId, $lokasiId, Request $request) {
        $request->validate([
            'stok' => 'required|integer|min:0',
        ]);

        $produk = Produk::findOrFail($produkId);
        $produk->lokasis()->syncWithoutDetaching([
            $lokasiId => ['stok' => $request->stok],
        ]);

        return response()->json(['message' => 'Stok diperbarui']);
    });

    Route::get('/produk/{produkId}/stok', function ($produkId) {
        $produk = Produk::with('lokasis')->findOrFail($produkId);

        $data = $produk->lokasis->map(function ($lokasi) {
            return [
                'lokasi_id' => $lokasi->id,
                'nama_lokasi' => $lokasi->nama_lokasi,
                'stok' => $lokasi->pivot->stok,
            ];
        });

        return response()->json($data);
    });

    // ========== MUTASI ==========
    Route::get('/mutasis', function () {
        return Mutasi::with(['user', 'produk', 'lokasi'])->get();
    });

    Route::post('/mutasis', function (Request $request) {
        $request->validate([
            'tanggal' => 'required|date',
            'jenis_mutasi' => 'required|in:masuk,keluar',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
            'produk_id' => 'required|exists:produks,id',
            'lokasi_id' => 'required|exists:lokasis,id',
        ]);

        $user = $request->user();
        $produk = Produk::findOrFail($request->produk_id);
        $lokasiId = $request->lokasi_id;

        $currentStock = $produk->lokasis()->where('lokasi_id', $lokasiId)->first()?->pivot->stok ?? 0;

        if ($request->jenis_mutasi === 'masuk') {
            $newStock = $currentStock + $request->jumlah;
        } else {
            if ($request->jumlah > $currentStock) {
                return response()->json(['message' => 'Stok tidak mencukupi'], 400);
            }
            $newStock = $currentStock - $request->jumlah;
        }

        $produk->lokasis()->syncWithoutDetaching([
            $lokasiId => ['stok' => $newStock],
        ]);

        $mutasi = Mutasi::create([
            'user_id' => $user->id,
            'produk_id' => $produk->id,
            'lokasi_id' => $lokasiId,
            'tanggal' => $request->tanggal,
            'jenis_mutasi' => $request->jenis_mutasi,
            'jumlah' => $request->jumlah,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json($mutasi->load(['user', 'produk', 'lokasi']), 201);
    });

    // ========== HISTORY MUTASI PRODUK ==========
    Route::get('/mutasis/produk/{produkId}', function ($produkId) {
        $mutasis = Mutasi::with(['user', 'produk', 'lokasi'])
            ->where('produk_id', $produkId)
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json($mutasis);
    });

    // ========== HISTORY MUTASI USER ==========
    Route::get('/mutasis/user/{userId}', function ($userId) {
        $mutasis = Mutasi::with(['user', 'produk', 'lokasi'])
            ->where('user_id', $userId)
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json($mutasis);
    });
});
