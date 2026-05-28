<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Polis as PolisModel; // Menggunakan alias agar VS Code Intelephense tidak error/merah

class PolisController extends Controller
{
    /**
     * Menampilkan daftar polis milik pengguna yang sedang login.
     * Sudah disesuaikan agar me-return format camelCase yang dicari oleh React Frontend.
     */
    public function polisSaya(Request $request)
    {
        $user = auth()->user();
        $userId = $user->ID_Pengguna ?? $user->id_pengguna ?? $user->id;

        // Mengambil semua data polis milik user yang sedang aktif login
        $polis = PolisModel::where('ID_Pengguna', $userId)->get()->map(function ($item) {
            
            // 1. Ambil data produk aslinya dari tabel produk secara dinamis
            $produk = Produk::where('ID_Produk', $item->ID_Produk)->first();
            
            // 2. Normalisasi penamaan kategori untuk menentukan Icon di frontend
            $kategori = $produk && $produk->Kategori_Produk ? $produk->Kategori_Produk : 'Kesehatan';

            // 3. Suntikkan properti virtual (Alias) yang dicari oleh filteredPolis.map() di React
            $item->id = $item->ID_Polis;
            $item->noPolis = $item->ID_Polis; 
            $item->jenis = 'Asuransi ' . $kategori;
            $item->premiFormatted = 'Rp ' . number_format($item->Total_Premi ?? 0, 0, ',', '.');
            $item->periode = '/ bulan';
            $item->premi = $item->Total_Premi;

            return $item;
        });

        return response()->json(['data' => $polis]);
    }

    /**
     * Menampilkan detail dari satu polis spesifik.
     */
    public function detail($id)
    {
        $user = auth()->user();
        $userId = $user->ID_Pengguna ?? $user->id_pengguna ?? $user->id;

        $polis = PolisModel::where('ID_Polis', $id)
            ->where('ID_Pengguna', $userId)
            ->firstOrFail();
            
        $polis->id = $polis->ID_Polis;
        return response()->json(['data' => $polis]);
    }

    /**
     * Memproses pembelian produk asuransi baru dari sisi nasabah.
     */
    public function beli(Request $request)
    {
        $request->validate([
            'ID_Produk'      => 'required|exists:produk,ID_Produk',
            'Harga_Premi'    => 'nullable|numeric',
            'Nama_Penerima'  => 'nullable|string',
            'NIK_Penerima'   => 'nullable|string',
            'Transaction_Id' => 'nullable|string'
        ]); // Perbaikan sintaks di sini (Kurung siku dan titik koma aman)

        $user = auth()->user();
        $userId = $user->ID_Pengguna ?? $user->id_pengguna ?? $user->id;

        // Aturan validasi status verifikasi KTP dari sistem Anda
        if ($user->verifikasi_status !== 'verified' && $user->Verifikasi_Status !== 'verified') {
            return response()->json([
                'message' => 'Akun belum terverifikasi, silakan tunggu verifikasi KTP/KK dari admin'
            ], 403);
        }

        // Cari data produk yang ingin dibeli
        $produk = Produk::where('ID_Produk', $request->ID_Produk)->first();
        if (!$produk) {
            $produk = Produk::findOrFail($request->ID_Produk);
        }

        // Pastikan produk berstatus dipublikasikan (published/aktif)
        $statusProduk = strtolower($produk->status);
        if ($statusProduk !== 'published' && $statusProduk !== 'aktif') {
            return response()->json([
                'message' => 'Produk tidak tersedia'
              ], 400);
        }

        // Tangkap nomor ID transaksi dari QRIS frontend, atau generate baru jika kosong
        $newId = $request->Transaction_Id;
        if (!$newId) {
            $count = PolisModel::count() + 1;
            $newId = 'POL' . str_pad($count, 5, '0', STR_PAD_LEFT);
        }

        // Simpan data transaksi baru langsung ke database MySQL Anda
        $polis = PolisModel::create([
            'ID_Polis'        => $newId,
            'ID_Pengguna'     => $userId,
            'ID_Produk'       => $request->ID_Produk,
            'Tanggal_Mulai'   => now()->toDateString(),
            'Tanggal_Selesai' => now()->addYear()->toDateString(),
            'Status_Polis'    => 'Aktif', // Status langsung diset aktif agar tampil di user
            'Total_Premi'     => $request->Harga_Premi ?? $produk->Harga_Premi,
        ]);

        $polis->id = $polis->ID_Polis;
        return response()->json([
            'message' => 'Polis berhasil dibuat',
            'data'    => $polis
        ], 201);
    }

    /**
     * Route Virtual Pembayaran untuk memenuhi trigger hitung mundur halaman QRIS.
     */
    public function bayar(Request $request, $id)
    {
        return response()->json([
            'message' => 'Pembayaran QRIS berhasil dikonfirmasi secara otomatis.'
        ], 200);
    }

    /**
     * Mengambil data statistik dinamis dan NAMA DEPAN pengguna untuk dashboard.
     */
    public function dashboardStats(Request $request)
    {
        $user = auth()->user();
        $userId = $user->ID_Pengguna ?? $user->id_pengguna ?? $user->id;

        // 1. Hitung jumlah baris polis aktif milik user ini
        $totalPolis = PolisModel::where('ID_Pengguna', $userId)
            ->where('Status_Polis', 'Aktif')
            ->count();

        // 2. Hitung jumlah total premi dari polis-polis yang aktif
        $totalPremi = PolisModel::where('ID_Pengguna', $userId)
            ->where('Status_Polis', 'Aktif')
            ->sum('Total_Premi');

        // 3. AMANKAN HITUNGAN KLAIM
        $totalKlaim = 0;
        try {
            $daftarIdPolis = PolisModel::where('ID_Pengguna', $userId)->pluck('ID_Polis')->toArray();

            if (!empty($daftarIdPolis)) {
                $totalKlaim = \DB::table('klaim')->whereIn('ID_Polis', $daftarIdPolis)->count();
            }
        } catch (\Exception $e) {
            $totalKlaim = 0;
        }

        // 4. PEMOTONGAN NAMA DEPAN
        $namaLengkap = $user->Nama_Lengkap ?? $user->nama_lengkap ?? $user->Nama_Pengguna ?? $user->nama_pengguna ?? $user->Nama ?? $user->nama ?? $user->name ?? 'Nasabah';
        $pisahkanNama = explode(' ', trim($namaLengkap));
        $namaDepan = !empty($pisahkanNama[0]) ? $pisahkanNama[0] : 'Nasabah';

        return response()->json([
            'status' => 'success',
            'data' => [
                'name' => $namaDepan,
                'nama' => $namaDepan,
                'total_polis' => $totalPolis,
                'total_premi' => 'Rp ' . number_format($totalPremi, 0, ',', '.'),
                'total_tunggakan' => 0, // Nilai default angka 0 lunas
                'total_klaim' => $totalKlaim
            ]
        ]);
    }

    /**
     * Mengambil data total tunggakan dan jumlah tagihan nasabah.
     */
    public function tunggakanNasabah(Request $request)
    {
        return response()->json([
            'totalTunggakan' => 0,
            'jumlahTagihan' => 0
        ]);
    }

    /**
     * Menangani request pembayaran premi berkala/rutin dari frontend.
     * Mengembalikan status sukses agar frontend diizinkan lanjut ke halaman QRIS.
     */
    public function bayarPremiRutin(Request $request, $id)
    {
        $polis = PolisModel::where('ID_Polis', $id)->first();

        if (!$polis) {
            return response()->json([
                'message' => 'Data nomor polis tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi pembayaran premi berhasil dibuat.',
            'transaction_id' => 'PAY-' . strtoupper(bin2hex(random_bytes(4))),
            'amount' => $polis->Total_Premi
        ], 200);
    }

    /**
     * Mengambil detail data iuran premi untuk discan di halaman QRIS frontend.
     */
    public function detailPembayaranPremi($transactionId)
    {
        $user = auth()->user();
        $userId = $user->ID_Pengguna ?? $user->id_pengguna ?? $user->id;

        // Cari data polis aktif milik user sebagai basis tagihan
        $polis = PolisModel::where('ID_Pengguna', $userId)->where('Status_Polis', 'Aktif')->first();

        // Jika user belum punya polis aktif, ambil polis apa saja yang dia miliki
        if (!$polis) {
            $polis = PolisModel::where('ID_Pengguna', $userId)->first();
        }

        if (!$polis) {
            return response()->json(['message' => 'Polis tidak ditemukan'], 404);
        }

        // Ambil data produk aslinya untuk nama kategori jenis asuransi
        $produk = Produk::where('ID_Produk', $polis->ID_Produk)->first();
        $kategori = $produk && $produk->Kategori_Produk ? $produk->Kategori_Produk : 'Kesehatan';

        return response()->json([
            'transactionId' => $transactionId,
            'noPolis' => $polis->ID_Polis,
            'jenis' => 'Asuransi ' . $kategori,
            'total' => (int) $polis->Total_Premi
        ], 200);
    }

    /**
     * Mengambil data notifikasi dinamis terpisah berdasarkan kategori waktu (Hari Ini & Kemarin).
     */
    public function notifikasiNasabah(Request $request)
    {
        $user = auth()->user();
        $userId = $user->ID_Pengguna ?? $user->id_pengguna ?? $user->id;

        // Cek data jumlah polis untuk membedakan isi pesan notifikasi
        $jumlahPolis = PolisModel::where('ID_Pengguna', $userId)->count();

        // 1. Data Notifikasi Hari Ini
        $hariIni = [
            [
                'id' => 101,
                'type' => 'info',
                'title' => 'Selamat Datang di InsurTech!',
                'message' => 'Akun Anda berhasil diverifikasi. Silakan jelajahi pilihan produk asuransi terbaik kami.',
                'date' => now()->translatedFormat('d M Y, H:i') . ' WIB'
            ]
        ];

        // Jika user sudah punya polis, tambahkan notifikasi sukses proteksi aktif hari ini
        if ($jumlahPolis > 0) {
            array_unshift($hariIni, [
                'id' => 102,
                'type' => 'success',
                'title' => 'Polis Aktif Dilindungi',
                'message' => 'Proteksi perlindungan asuransi Anda saat ini sudah berjalan penuh dan berstatus AKTIF.',
                'date' => now()->subHours(2)->translatedFormat('d M Y, H:i') . ' WIB'
            ]);
        }

        // 2. Data Notifikasi Kemarin
        $kemarin = [
            [
                'id' => 201,
                'type' => 'warning',
                'title' => 'Lengkapi Profil Anda',
                'message' => 'Mohon pastikan nomor telepon dan data penanggung cadangan di profil Anda sudah terisi dengan benar.',
                'date' => now()->subDay()->translatedFormat('d M Y') . ' • 14:20 WIB'
            ]
        ];

        return response()->json([
            'hariIni' => $hariIni,
            'kemarin' => $kemarin
        ], 200);
    }

    /**
     * Mengambil riwayat transaksi pembelian polis milik pengguna yang sedang login.
     */
    public function riwayatTransaksi(Request $request)
    {
        $user = auth()->user();
        $userId = $user->ID_Pengguna ?? $user->id_pengguna ?? $user->id;

        // Ambil data polis milik user ini
        $polisList = PolisModel::where('ID_Pengguna', $userId)
            ->orderBy('Tanggal_Mulai', 'desc')
            ->get()
            ->map(function ($item) {
                $produk = Produk::where('ID_Produk', $item->ID_Produk)->first();
                $kategori = $produk && $produk->Kategori_Produk ? $produk->Kategori_Produk : 'Kesehatan';

                return [
                    'id' => $item->ID_Polis,
                    'noPolis' => $item->ID_Polis,
                    'jenis' => 'Asuransi ' . $kategori,
                    'nominal' => (int) $item->Total_Premi,
                    'nominalFormatted' => 'Rp ' . number_format($item->Total_Premi ?? 0, 0, ',', '.'),
                    'tanggal' => \Carbon\Carbon::parse($item->Tanggal_Mulai)->translatedFormat('d M Y'),
                    'status' => strtolower($item->Status_Polis) === 'aktif' ? 'Berhasil' : 'Pending',
                ];
    });

        return response()->json([
            'status' => 'success',
            'data' => $polisList
        ], 200);
    }
}