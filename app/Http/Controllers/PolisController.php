<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Polis as PolisModel;
use App\Models\Klaim;

class PolisController extends Controller
{
    public function polisSaya(Request $request)
    {
        $user = auth()->user();
        $userId = $user->ID_Pengguna ?? $user->id_pengguna ?? $user->id;

        $polis = PolisModel::where('ID_Pengguna', $userId)->get()->map(function ($item) {
            $produk = Produk::where('ID_Produk', $item->ID_Produk)->first();
            $kategori = $produk && $produk->Kategori_Produk ? $produk->Kategori_Produk : 'Kesehatan';
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

    public function beli(Request $request)
    {
        $request->validate([
            'ID_Produk'      => 'required|exists:produk,ID_Produk',
            'Harga_Premi'    => 'nullable|numeric',
            'Nama_Penerima'  => 'nullable|string',
            'NIK_Penerima'   => 'nullable|string',
            'Transaction_Id' => 'nullable|string'
        ]);

        $user = auth()->user();
        $userId = $user->ID_Pengguna ?? $user->id_pengguna ?? $user->id;

        if ($user->verifikasi_status !== 'verified' && $user->Verifikasi_Status !== 'verified') {
            return response()->json([
                'message' => 'Akun belum terverifikasi, silakan tunggu verifikasi KTP/KK dari admin'
            ], 403);
        }

        $produk = Produk::where('ID_Produk', $request->ID_Produk)->first();
        if (!$produk) {
            $produk = Produk::findOrFail($request->ID_Produk);
        }

        $statusProduk = strtolower($produk->status);
        if ($statusProduk !== 'published' && $statusProduk !== 'aktif') {
            return response()->json(['message' => 'Produk tidak tersedia'], 400);
        }

        $newId = $request->Transaction_Id;
        if (!$newId) {
            $count = PolisModel::count() + 1;
            $newId = 'POL' . str_pad($count, 5, '0', STR_PAD_LEFT);
        }

        $polis = PolisModel::create([
            'ID_Polis'        => $newId,
            'ID_Pengguna'     => $userId,
            'ID_Produk'       => $request->ID_Produk,
            'Tanggal_Mulai'   => now()->toDateString(),
            'Tanggal_Selesai' => now()->addYear()->toDateString(),
            'Status_Polis'    => 'Aktif',
            'Total_Premi'     => $request->Harga_Premi ?? $produk->Harga_Premi,
        ]);

        $polis->id = $polis->ID_Polis;
        return response()->json([
            'message' => 'Polis berhasil dibuat',
            'data'    => $polis
        ], 201);
    }

    public function bayar(Request $request, $id)
    {
        return response()->json([
            'message' => 'Pembayaran QRIS berhasil dikonfirmasi secara otomatis.'
        ], 200);
    }

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

        // 5. TAMBAHAN: Ambil 3 aktivitas terbaru (polis + klaim)
        $aktivitas = collect();

        // Dari polis (pembelian)
        $polisTerbaru = PolisModel::where('ID_Pengguna', $userId)
            ->orderBy('Tanggal_Mulai', 'desc')
            ->take(3)
            ->get();

        foreach ($polisTerbaru as $polis) {
            $produk = Produk::where('ID_Produk', $polis->ID_Produk)->first();
            $namaProduk = $produk ? $produk->Nama_Produk : 'Produk Asuransi';
            $aktivitas->push([
                'type'        => 'premi',
                'title'       => 'Pembelian Polis Berhasil',
                'product'     => $namaProduk,
                'description' => $namaProduk,
                'amount'      => 'Rp ' . number_format($polis->Total_Premi ?? 0, 0, ',', '.'),
                'date'        => \Carbon\Carbon::parse($polis->Tanggal_Mulai)->translatedFormat('d M Y'),
                'timestamp'   => $polis->Tanggal_Mulai,
            ]);
        }

        // Dari klaim
        try {
            $daftarIdPolisArr = PolisModel::where('ID_Pengguna', $userId)->pluck('ID_Polis')->toArray();
            if (!empty($daftarIdPolisArr)) {
                $klaimTerbaru = Klaim::whereIn('ID_Polis', $daftarIdPolisArr)
                    ->orderBy('Tanggal_Pengajuan', 'desc')
                    ->take(3)
                    ->get();

                foreach ($klaimTerbaru as $klaim) {
                    $statusLabel = match($klaim->Status_Klaim) {
                        'Selesai' => 'Klaim Disetujui',
                        'Ditolak' => 'Klaim Ditolak',
                        default   => 'Klaim Sedang Diproses',
                    };
                    $aktivitas->push([
                        'type'        => 'klaim',
                        'title'       => $statusLabel,
                        'product'     => 'Jenis: ' . $klaim->Jenis_Klaim,
                        'description' => 'Jenis: ' . $klaim->Jenis_Klaim,
                        'amount'      => null,
                        'date'        => \Carbon\Carbon::parse($klaim->Tanggal_Pengajuan)->translatedFormat('d M Y'),
                        'timestamp'   => $klaim->Tanggal_Pengajuan,
                    ]);
                }
            }
        } catch (\Exception $e) {}

        // Urutkan dan ambil 3 terbaru
        $aktivitasTerbaru = $aktivitas
            ->sortByDesc('timestamp')
            ->take(3)
            ->values()
            ->map(function ($item) {
                unset($item['timestamp']);
                return $item;
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'name'            => $namaDepan,
                'nama'            => $namaDepan,
                'total_polis'     => $totalPolis,
                'total_premi'     => 'Rp ' . number_format($totalPremi, 0, ',', '.'),
                'total_tunggakan' => 0,
                'total_klaim'     => $totalKlaim,
                'aktivitas'       => $aktivitasTerbaru,
            ]
        ]);
    }

    public function tunggakanNasabah(Request $request)
    {
        return response()->json([
            'totalTunggakan' => 0,
            'jumlahTagihan' => 0
        ]);
    }

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

    public function detailPembayaranPremi($transactionId)
    {
        $user = auth()->user();
        $userId = $user->ID_Pengguna ?? $user->id_pengguna ?? $user->id;

        $polis = PolisModel::where('ID_Pengguna', $userId)->where('Status_Polis', 'Aktif')->first();

        if (!$polis) {
            $polis = PolisModel::where('ID_Pengguna', $userId)->first();
        }

        if (!$polis) {
            return response()->json(['message' => 'Polis tidak ditemukan'], 404);
        }

        $produk = Produk::where('ID_Produk', $polis->ID_Produk)->first();
        $kategori = $produk && $produk->Kategori_Produk ? $produk->Kategori_Produk : 'Kesehatan';

        return response()->json([
            'transactionId' => $transactionId,
            'noPolis' => $polis->ID_Polis,
            'jenis' => 'Asuransi ' . $kategori,
            'total' => (int) $polis->Total_Premi
        ], 200);
    }

    public function notifikasiNasabah(Request $request)
    {
        try {
            $user = auth()->user();
            $userId = $user->ID_Pengguna ?? $user->id_pengguna ?? $user->id;
            $notifs = collect();

            $statusVerifikasi = $user->Verifikasi_Status ?? $user->verifikasi_status ?? 'pending';
            if (strtolower($statusVerifikasi) === 'verified') {
                $notifs->push([
                    'id'        => 'akun_' . $userId,
                    'type'      => 'info',
                    'title'     => 'Selamat Datang di InsurTech!',
                    'message'   => 'Akun Anda berhasil diverifikasi. Silakan jelajahi pilihan produk asuransi terbaik kami.',
                    'timestamp' => \Carbon\Carbon::now(),
                    'date'      => \Carbon\Carbon::now()->translatedFormat('d M Y, H:i') . ' WIB'
                ]);
            } else {
                $notifs->push([
                    'id'        => 'akun_unverified_' . $userId,
                    'type'      => 'warning',
                    'title'     => 'Lengkapi Profil Anda',
                    'message'   => 'Mohon pastikan nomor telepon dan data penanggung cadangan di profil Anda sudah terisi dengan benar.',
                    'timestamp' => \Carbon\Carbon::now()->subDays(1),
                    'date'      => \Carbon\Carbon::now()->subDays(1)->translatedFormat('d M Y, H:i') . ' WIB'
                ]);
            }

            $polisList = PolisModel::with('produk')->where('ID_Pengguna', $userId)->get();
            foreach ($polisList as $polis) {
                $namaProduk = $polis->produk->Nama_Produk ?? 'Asuransi';
                $statusPolis = $polis->Status_Polis ?? '';

                if ($statusPolis === 'Aktif') {
                    $notifs->push([
                        'id'        => 'polis_' . $polis->ID_Polis,
                        'type'      => 'success',
                        'title'     => 'Polis Aktif Dilindungi',
                        'message'   => "Proteksi asuransi {$namaProduk} Anda saat ini sudah berjalan penuh dan berstatus AKTIF.",
                        'timestamp' => \Carbon\Carbon::parse($polis->Tanggal_Mulai ?? now()),
                        'date'      => \Carbon\Carbon::parse($polis->Tanggal_Mulai ?? now())->translatedFormat('d M Y, H:i') . ' WIB'
                    ]);
                } elseif (in_array($statusPolis, ['Pending', 'Menunggu Pembayaran'])) {
                    $notifs->push([
                        'id'        => 'polis_pending_' . $polis->ID_Polis,
                        'type'      => 'warning',
                        'title'     => 'Menunggu Pembayaran',
                        'message'   => "Selesaikan pembayaran premi untuk mengaktifkan proteksi {$namaProduk} Anda.",
                        'timestamp' => \Carbon\Carbon::now(),
                        'date'      => \Carbon\Carbon::now()->translatedFormat('d M Y, H:i') . ' WIB'
                    ]);
                }
            }

            $klaimList = Klaim::with('polis.produk')->whereHas('polis', function($q) use ($userId) {
                $q->where('ID_Pengguna', $userId);
            })->get();

            foreach ($klaimList as $klaim) {
                $namaProduk = $klaim->polis->produk->Nama_Produk ?? 'Asuransi';
                $statusKlaim = strtoupper($klaim->Status_Klaim ?? '');

                if ($statusKlaim === 'SELESAI' || $statusKlaim === 'DISETUJUI') {
                    $notifs->push([
                        'id'        => 'klaim_selesai_' . $klaim->ID_Klaim,
                        'type'      => 'success',
                        'title'     => 'Klaim Disetujui!',
                        'message'   => "Pengajuan klaim {$namaProduk} Anda telah disetujui. Cek menu status klaim untuk info pencairan.",
                        'timestamp' => \Carbon\Carbon::parse($klaim->Tanggal_Pencairan ?? now()),
                        'date'      => \Carbon\Carbon::parse($klaim->Tanggal_Pencairan ?? now())->translatedFormat('d M Y, H:i') . ' WIB'
                    ]);
                } elseif ($statusKlaim === 'DITOLAK') {
                    $notifs->push([
                        'id'        => 'klaim_tolak_' . $klaim->ID_Klaim,
                        'type'      => 'error',
                        'title'     => 'Klaim Ditolak',
                        'message'   => "Pengajuan klaim {$namaProduk} Anda ditolak. Lihat menu status klaim untuk detail alasannya.",
                        'timestamp' => \Carbon\Carbon::now(),
                        'date'      => \Carbon\Carbon::now()->translatedFormat('d M Y, H:i') . ' WIB'
                    ]);
                } else {
                    $notifs->push([
                        'id'        => 'klaim_proses_' . $klaim->ID_Klaim,
                        'type'      => 'warning',
                        'title'     => 'Klaim Sedang Diproses',
                        'message'   => "Pengajuan klaim {$namaProduk} Anda sedang dalam proses verifikasi oleh tim kami.",
                        'timestamp' => \Carbon\Carbon::parse($klaim->Tanggal_Pengajuan ?? now()),
                        'date'      => \Carbon\Carbon::parse($klaim->Tanggal_Pengajuan ?? now())->translatedFormat('d M Y, H:i') . ' WIB'
                    ]);
                }
            }

            $sortedNotifs = $notifs->sortByDesc('timestamp')->values();

            $hariIni = [];
            $kemarin = [];
            $today = \Carbon\Carbon::today();
            $yesterday = \Carbon\Carbon::yesterday();

            foreach ($sortedNotifs as $notif) {
                $notifDate = \Carbon\Carbon::parse($notif['timestamp'])->startOfDay();
                unset($notif['timestamp']);

                if ($notifDate->equalTo($today)) {
                    $hariIni[] = $notif;
                } elseif ($notifDate->lessThanOrEqualTo($yesterday)) {
                    $kemarin[] = $notif;
                }
            }

            return response()->json([
                'data' => [
                    'hariIni' => $hariIni,
                    'kemarin' => $kemarin
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error backend: ' . $e->getMessage()
            ], 500);
        }
    }

    public function riwayatTransaksi(Request $request)
    {
        $user = auth()->user();
        $userId = $user->ID_Pengguna ?? $user->id_pengguna ?? $user->id;

        $polisList = PolisModel::where('ID_Pengguna', $userId)
            ->orderBy('Tanggal_Mulai', 'desc')
            ->get()
            ->map(function ($item) {
                $produk = Produk::where('ID_Produk', $item->ID_Produk)->first();
                $kategori = $produk && $produk->Kategori_Produk ? $produk->Kategori_Produk : 'Kesehatan';

                return [
                    'id'               => $item->ID_Polis,
                    'noPolis'          => $item->ID_Polis,
                    'jenis'            => 'Asuransi ' . $kategori,
                    'nominal'          => (int) $item->Total_Premi,
                    'nominalFormatted' => 'Rp ' . number_format($item->Total_Premi ?? 0, 0, ',', '.'),
                    'tanggal'          => \Carbon\Carbon::parse($item->Tanggal_Mulai)->translatedFormat('d M Y'),
                    'status'           => strtolower($item->Status_Polis) === 'aktif' ? 'Berhasil' : 'Pending',
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $polisList
        ], 200);
    }
}