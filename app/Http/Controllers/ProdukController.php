<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // TAMBAHAN: Wajib untuk fitur upload file PDF

class ProdukController extends Controller
{
    public function index()
    {
        // Ambil semua produk dan transformasikan agar ramah dengan frontend nasabah maupun admin
        $produk = Produk::all()->map(function ($item) {
            // 1. Selipkan 'id' alias agar loop key React nasabah & admin tidak pecah
            $item->id = $item->ID_Produk; 
            
            // 2. Konversi status database menjadi kapital standar frontend ('Aktif', 'Draft', 'Nonaktif')
            $rawStatus = strtolower($item->status);
            if ($rawStatus === 'published' || $rawStatus === 'aktif') {
                $item->status = 'Aktif';
            } elseif ($rawStatus === 'archived' || $rawStatus === 'nonaktif') {
                $item->status = 'Nonaktif';
            } else {
                $item->status = 'Draft';
            }

            // 3. NORMALISASI MULTI-ALIAS DATA (Mengantisipasi perbedaan pemanggilan variabel di sisi nasabah)
            $item->nama = $item->Nama_Produk;
            $item->title = $item->Nama_Produk;
            $item->name = $item->Nama_Produk;

            $item->deskripsi = $item->Deskripsi_Produk;
            $item->description = $item->Deskripsi_Produk;

            $item->premi = $item->Harga_Premi;
            $item->price = $item->Harga_Premi;
            $item->harga = $item->Harga_Premi;
            $item->priceFormatted = 'Rp ' . number_format($item->Harga_Premi, 0, ',', '.');

            $item->kategori = $item->Kategori_Produk;
            $item->category = $item->Kategori_Produk;

            $item->maks = $item->Maksimal_Klaim;
            $item->maxClaim = $item->Maksimal_Klaim;
            
            $item->masa_tunggu = $item->Masa_Tunggu;

            // TAMBAHAN: Kirimkan path file PDF agar terbaca di Frontend
            $item->file_snk = $item->file_snk;

            return $item;
        });

        return response()->json($produk);
    }

    public function show($id)
    {
        $produk = Produk::where('ID_Produk', $id)->first();
        if (!$produk) {
            $produk = Produk::findOrFail($id);
        }

        $produk->id = $produk->ID_Produk;
        
        // Normalisasi status dan alias untuk data tunggal (single view)
        $rawStatus = strtolower($produk->status);
        if ($rawStatus === 'published' || $rawStatus === 'aktif') {
            $produk->status = 'Aktif';
        } else {
            $produk->status = 'Draft';
        }

        $produk->nama = $produk->Nama_Produk;
        $produk->title = $produk->Nama_Produk;
        $produk->deskripsi = $produk->Deskripsi_Produk;
        $produk->description = $produk->Deskripsi_Produk;
        $produk->premi = $produk->Harga_Premi;
        $produk->price = $produk->Harga_Premi;
        $produk->kategori = $produk->Kategori_Produk;
        $produk->category = $produk->Kategori_Produk;
        
        // TAMBAHAN
        $produk->file_snk = $produk->file_snk;

        return response()->json($produk);
    }

    public function store(Request $request)
    {
        $request->validate([
            'Nama_Produk'      => 'required|string|max:100',
            'Deskripsi_Produk' => 'nullable|string',
            'Harga_Premi'      => 'required|numeric|min:0',
            'Kategori_Produk'  => 'nullable|string',
            'Maksimal_Klaim'   => 'nullable|numeric',
            'Masa_Tunggu'      => 'nullable|numeric',
            // 'pdfFile' => 'nullable|mimes:pdf|max:10240', // Opsional validasi PDF
        ]);

        $count = Produk::count() + 1;
        $newId = 'PRD' . str_pad($count, 5, '0', STR_PAD_LEFT);

        // Ambil status input, simpan sebagai lowercase 'published' jika frontend mengirim 'aktif'
        $statusInput = strtolower($request->input('status', 'draft'));
        if ($statusInput === 'aktif') {
            $statusInput = 'published';
        }

        // Siapkan array data yang akan dimasukkan ke Database
        $dataCreate = [
            'ID_Produk'        => $newId,
            'Nama_Produk'      => $request->Nama_Produk,
            'Deskripsi_Produk' => $request->Deskripsi_Produk,
            'Harga_Premi'      => $request->Harga_Premi,
            'Kategori_Produk'  => $request->Kategori_Produk,
            'Maksimal_Klaim'   => $request->Maksimal_Klaim,
            'Masa_Tunggu'      => $request->Masa_Tunggu,
            'status'           => $statusInput,
            'published_at'     => $statusInput === 'published' ? now() : null,
            'created_by'       => auth()->user() ? auth()->user()->ID_Pengguna : null,
        ];

        // TAMBAHAN: LOGIKA SIMPAN FILE PDF
        if ($request->hasFile('pdfFile')) {
            $file = $request->file('pdfFile');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Simpan ke storage/app/public/pdfs
            $path = $file->storeAs('pdfs', $filename, 'public');
            
            // Tambahkan path ke array agar tersimpan di database
            $dataCreate['file_snk'] = $path;
        }

        $produk = Produk::create($dataCreate);

        $produk->id = $produk->ID_Produk;
        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'data'    => $produk
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::where('ID_Produk', $id)->first();
        if (!$produk) {
            $produk = Produk::findOrFail($id);
        }

        $request->validate([
            'Nama_Produk'      => 'sometimes|string|max:100',
            'Deskripsi_Produk' => 'nullable|string',
            'Harga_Premi'      => 'sometimes|numeric|min:0',
            'Kategori_Produk'  => 'nullable|string',
            'Maksimal_Klaim'   => 'nullable|numeric',
            'Masa_Tunggu'      => 'nullable|numeric',
            'status'           => 'sometimes|in:draft,published,archived,Aktif,aktif'
        ]);

        $dataUpdate = $request->only([
            'Nama_Produk', 'Deskripsi_Produk', 'Harga_Premi', 'Kategori_Produk', 'Maksimal_Klaim', 'Masa_Tunggu', 'status'
        ]);

        if (isset($dataUpdate['status'])) {
            $statusCheck = strtolower($dataUpdate['status']);
            if ($statusCheck === 'published' || $statusCheck === 'aktif') {
                $dataUpdate['status'] = 'published';
                $dataUpdate['published_at'] = now();
            } else {
                $dataUpdate['status'] = $statusCheck;
            }
        }

        // TAMBAHAN: LOGIKA UPDATE FILE PDF
        if ($request->hasFile('pdfFile')) {
            // Hapus file lama jika produk tersebut sudah punya file sebelumnya
            if ($produk->file_snk && Storage::disk('public')->exists($produk->file_snk)) {
                Storage::disk('public')->delete($produk->file_snk);
            }

            // Simpan file baru
            $file = $request->file('pdfFile');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('pdfs', $filename, 'public');
            
            // Masukkan nama file baru ke data update
            $dataUpdate['file_snk'] = $path;
        }

        $produk->update($dataUpdate);
        $produk->id = $produk->ID_Produk;

        return response()->json([
            'message' => 'Produk berhasil diupdate',
            'data'    => $produk
        ]);
    }

    public function destroy($id)
    {
        $produk = Produk::where('ID_Produk', $id)->first();
        if (!$produk) {
            $produk = Produk::findOrFail($id);
        }
        
        // TAMBAHAN: Hapus file fisik PDF dari server saat produk dihapus agar storage tidak penuh
        if ($produk->file_snk && Storage::disk('public')->exists($produk->file_snk)) {
            Storage::disk('public')->delete($produk->file_snk);
        }

        $produk->delete();
        return response()->json(['message' => 'Produk berhasil dihapus']);
    }

    public function publish($id)
    {
        $produk = Produk::where('ID_Produk', $id)->first();
        if (!$produk) {
            $produk = Produk::findOrFail($id);
        }

        $produk->update([
            'status'       => 'published',
            'published_at' => now()
        ]);

        return response()->json([
            'message' => 'Produk berhasil dipublikasikan',
            'data'    => $produk
        ]);
    }
}