<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Klaim;
use App\Models\Polis;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class KlaimController extends Controller
{
    // Ajukan klaim (pengguna)
    public function ajukan(Request $request)
    {
        try {
            Log::info('Mulai proses ajukan klaim', $request->all());
            
            $user = $request->user();
            
            Log::info('User yang login:', ['id' => $user->ID_Pengguna ?? 'unknown']);

            $request->validate([
                'ID_Polis'          => 'required|exists:polis,ID_Polis',
                'Jenis_Klaim'       => 'required|string|max:50',
                'Jumlah_Klaim'      => 'required|numeric|min:1',
                'Tanggal_Kejadian'  => 'required|date',
                'Deskripsi'         => 'required|string|min:10',
                'Dokumen.*'         => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);

            $polis = Polis::where('ID_Polis', $request->ID_Polis)
                ->where('ID_Pengguna', $user->ID_Pengguna)
                ->first();

            if (!$polis) {
                return response()->json(['message' => 'Polis tidak ditemukan'], 404);
            }

            // Generate ID Klaim
            $lastKlaim = Klaim::orderBy('ID_Klaim', 'desc')->first();
            $lastId    = $lastKlaim ? intval(substr($lastKlaim->ID_Klaim, 3)) : 0;
            $newId     = 'KLM' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
            
            Log::info('Generated ID Klaim: ' . $newId);

            // Proses upload file dokumen
            $uploadedFiles = [];
            if ($request->hasFile('Dokumen')) {
                $files = $request->file('Dokumen');
                Log::info('Jumlah file yang diupload: ' . count($files));
                
                foreach ($files as $index => $file) {
                    try {
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $fileName = $newId . '_' . ($index + 1) . '_' . time() . '.' . $extension;
                        $path = $file->storeAs('dokumen_klaim/' . $newId, $fileName, 'public');
                        $uploadedFiles[] = [
                            'original_name' => $file->getClientOriginalName(),
                            'file_path' => $path,
                            'file_type' => $file->getMimeType(),
                            'file_size' => $file->getSize()
                        ];
                        Log::info('File tersimpan: ' . $path);
                    } catch (\Exception $e) {
                        Log::error('Error upload file: ' . $e->getMessage());
                    }
                }
            }

            // Simpan ke database
            $klaim = Klaim::create([
                'ID_Klaim'          => $newId,
                'ID_Polis'          => $request->ID_Polis,
                'Jenis_Klaim'       => $request->Jenis_Klaim,
                'jumlah_klaim'      => $request->Jumlah_Klaim,
                'Tanggal_Pengajuan' => now()->toDateString(),
                'Tanggal_Kejadian'  => $request->Tanggal_Kejadian,
                'Deskripsi'         => $request->Deskripsi,
                'Dokumen'           => json_encode($uploadedFiles),
                'Status_Klaim'      => 'Proses',
            ]);

            Log::info('Klaim berhasil disimpan dengan ID: ' . $klaim->ID_Klaim);

            return response()->json([
                'success' => true,
                'message' => 'Klaim berhasil diajukan',
                'data'    => $klaim
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error dalam ajukan klaim: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    // Lihat klaim milik pengguna yang login
    public function klaimSaya(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['message' => 'User tidak ditemukan', 'data' => []], 401);
            }
            
            $userId = $user->ID_Pengguna ?? $user->id;
            Log::info('Mengambil klaim untuk user ID: ' . $userId);
            
            $klaim = Klaim::whereHas('polis', function ($q) use ($userId) {
                $q->where('ID_Pengguna', $userId);
            })
            ->orderBy('Tanggal_Pengajuan', 'desc')
            ->get();
            
            Log::info('Jumlah klaim ditemukan: ' . $klaim->count());
            
            $result = $klaim->map(function ($item) {
                $status = $item->Status_Klaim;
                $displayStatus = $status;
                
                if ($status === 'Selesai') $displayStatus = 'DISETUJUI';
                if ($status === 'Ditolak') $displayStatus = 'DITOLAK';
                if ($status === 'Proses')  $displayStatus = 'DIPROSES';
                
                $dokumenList = [];
                if ($item->Dokumen) {
                    $dokumenArray = json_decode($item->Dokumen, true);
                    if (is_array($dokumenArray)) {
                        foreach ($dokumenArray as $doc) {
                            $dokumenList[] = [
                                'original_name' => $doc['original_name'] ?? 'dokumen',
                                'url' => Storage::url($doc['file_path']),
                                'type' => $doc['file_type'] ?? 'application/octet-stream'
                            ];
                        }
                    }
                }
                
                return [
                    'ID_Klaim' => $item->ID_Klaim,
                    'ID_Polis' => $item->ID_Polis,
                    'Jenis_Klaim' => $item->Jenis_Klaim,
                    'jumlah_klaim' => $item->jumlah_klaim,
                    'Tanggal_Pengajuan' => $item->Tanggal_Pengajuan,
                    'Tanggal_Kejadian' => $item->Tanggal_Kejadian,
                    'Deskripsi' => $item->Deskripsi,
                    'Status_Klaim' => $displayStatus,
                    'Tanggal_Pencairan' => $item->Tanggal_Pencairan,
                    'alasan_penolakan' => $item->alasan_penolakan,
                    'dokumen_list' => $dokumenList,
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error klaimSaya: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error mengambil data klaim: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // Lihat status klaim by ID
    public function status($id, Request $request)
    {
        try {
            $klaim = Klaim::with('polis')
                ->where('ID_Klaim', $id)
                ->whereHas('polis', function ($q) use ($request) {
                    $q->where('ID_Pengguna', $request->user()->ID_Pengguna);
                })
                ->firstOrFail();

            $dokumenList = [];
            if ($klaim->Dokumen) {
                $dokumenArray = json_decode($klaim->Dokumen, true);
                if (is_array($dokumenArray)) {
                    foreach ($dokumenArray as $doc) {
                        $dokumenList[] = [
                            'original_name' => $doc['original_name'] ?? 'dokumen',
                            'url' => Storage::url($doc['file_path']),
                            'type' => $doc['file_type'] ?? 'application/octet-stream'
                        ];
                    }
                }
            }

            return response()->json([
                'status'            => $klaim->Status_Klaim,
                'tanggal_pengajuan' => $klaim->Tanggal_Pengajuan,
                'jenis_klaim'       => $klaim->Jenis_Klaim,
                'jumlah_klaim'      => $klaim->jumlah_klaim,
                'tanggal_kejadian'  => $klaim->Tanggal_Kejadian,
                'deskripsi'         => $klaim->Deskripsi,
                'dokumen'           => $dokumenList,
            ]);
        } catch (\Exception $e) {
            Log::error('Error status klaim: ' . $e->getMessage());
            return response()->json(['message' => 'Klaim tidak ditemukan'], 404);
        }
    }

    // ==================== VERSI SEDERHANA UNTUK ADMIN ====================
    
    // PERBAIKAN: Lihat semua klaim (admin) - DENGAN JOIN AMBIL NAMA NASABAH
    public function index()
{
    try {
        $klaim = DB::table('klaim')
            ->leftJoin('polis', 'klaim.ID_Polis', '=', 'polis.ID_Polis')
            ->leftJoin('pengguna', 'polis.ID_Pengguna', '=', 'pengguna.ID_Pengguna')
            ->leftJoin('produk', 'polis.ID_Produk', '=', 'produk.ID_Produk')
            ->select(
                'klaim.*',
                'pengguna.Nama_Lengkap as nasabah_nama',
                'pengguna.No_Telepon as nasabah_telepon',
                'pengguna.Email as nasabah_email',
                'produk.Nama_Produk as produk_nama'
            )
            ->orderBy('klaim.Tanggal_Pengajuan', 'desc')
            ->get();

        Log::info('Jumlah semua klaim: ' . $klaim->count());

        return response()->json([
            'success' => true,
            'data' => $klaim
        ]);

    } catch (\Exception $e) {
        Log::error('Error index klaim: ' . $e->getMessage());

        $klaim = DB::table('klaim')
            ->select('*')
            ->orderBy('Tanggal_Pengajuan', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $klaim
        ]);
    }
}

    // Lihat klaim pending (admin) - VERSION PALING SEDERHANA
    public function pendingKlaim()
    {
        try {
            // Ambil klaim dengan status Proses/Pending
            $klaim = DB::table('klaim')
                ->select('*')
                ->whereIn('Status_Klaim', ['Proses', 'proses', 'Pending', 'pending'])
                ->orderBy('Tanggal_Pengajuan', 'desc')
                ->get();
            
            Log::info('Jumlah klaim pending: ' . $klaim->count());
            
            return response()->json([
                'success' => true,
                'data' => $klaim
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error pendingKlaim: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // STATISTIK untuk dashboard admin
    public function stats()
    {
        try {
            $totalPengguna = DB::table('pengguna')->count();
            $polisAktif = DB::table('polis')->where('Status_Polis', 'Aktif')->count();
            $klaimPending = DB::table('klaim')
                ->whereIn('Status_Klaim', ['Proses', 'proses', 'Pending', 'pending'])
                ->count();
            
            // Premi bulan ini
            $premiBulanIni = DB::table('polis')
                ->join('produk', 'polis.ID_Produk', '=', 'produk.ID_Produk')
                ->sum('produk.Harga_Premi') ?? 0;
            
            Log::info('Stats - pengguna: ' . $totalPengguna . ', polisAktif: ' . $polisAktif . ', klaimPending: ' . $klaimPending);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'pengguna' => $totalPengguna,
                    'polisAktif' => $polisAktif,
                    'klaimPending' => $klaimPending,
                    'premiBulanIni' => $premiBulanIni
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => [
                    'pengguna' => 0,
                    'polisAktif' => 0,
                    'klaimPending' => 0,
                    'premiBulanIni' => 0
                ]
            ]);
        }
    }

    // Review klaim (admin)
    public function review(Request $request, $id)
    {
        try {
            $request->validate([
                'status'            => 'required|string|in:DISETUJUI,DITOLAK',
                'alasan_penolakan'  => 'required_if:status,DITOLAK|nullable|string',
                'tanggal_pencairan' => 'required_if:status,DISETUJUI|nullable|date',
            ]);

            $klaim = Klaim::where('ID_Klaim', $id)->first();
            if (!$klaim) {
                return response()->json(['message' => 'Klaim tidak ditemukan'], 404);
            }

            $dbStatus = $request->status;
            if ($dbStatus === 'DISETUJUI') $dbStatus = 'Selesai';
            if ($dbStatus === 'DITOLAK')   $dbStatus = 'Ditolak';

            $updateData = ['Status_Klaim' => $dbStatus];

            if ($dbStatus === 'Ditolak') {
                $updateData['alasan_penolakan'] = $request->alasan_penolakan;
            }

            if ($dbStatus === 'Selesai' && $request->filled('tanggal_pencairan')) {
                $updateData['Tanggal_Pencairan'] = $request->tanggal_pencairan;
            }

            $klaim->update($updateData);

            return response()->json([
                'message' => 'Klaim berhasil direview',
                'data' => $klaim
            ]);
        } catch (\Exception $e) {
            Log::error('Error review klaim: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    // Unduh Surat Klaim (PDF)
    public function unduh($id, Request $request)
    {
        try {
            $klaim = Klaim::with(['polis.pengguna', 'polis.produk'])
                ->where('ID_Klaim', $id)
                ->whereHas('polis', function ($q) use ($request) {
                    $q->where('ID_Pengguna', $request->user()->ID_Pengguna);
                })
                ->firstOrFail();

            $namaNasabah = $klaim->polis->pengguna->Nama_Lengkap ?? 'Nasabah';
            $namaProduk = $klaim->polis->produk->Nama_Produk ?? 'Asuransi Kesehatan';
            $nilaiKlaim = 'Rp ' . number_format((float) ($klaim->jumlah_klaim ?? 0), 0, ',', '.');
            
            $statusDisplay = $klaim->Status_Klaim;
            if ($statusDisplay === 'Selesai') $statusDisplay = 'DISETUJUI';
            if ($statusDisplay === 'Ditolak') $statusDisplay = 'DITOLAK';
            if ($statusDisplay === 'Proses')  $statusDisplay = 'DIPROSES';

            $tglPengajuan = $klaim->Tanggal_Pengajuan ? \Carbon\Carbon::parse($klaim->Tanggal_Pengajuan)->translatedFormat('d F Y') : '-';
            $tglPencairan = $klaim->Tanggal_Pencairan ? \Carbon\Carbon::parse($klaim->Tanggal_Pencairan)->translatedFormat('d F Y') : '-';
            
            $catatanAdmin = '';
            if ($statusDisplay === 'DISETUJUI') {
                $catatanAdmin = 'Klaim Anda telah kami terima dan akan/telah diproses sesuai ketentuan polis yang berlaku.';
            } elseif ($statusDisplay === 'DIPROSES') {
                $catatanAdmin = 'Klaim Anda sedang dalam proses verifikasi oleh tim kami. Status akan diperbarui dalam 24-48 jam.';
            } elseif ($statusDisplay === 'DITOLAK') {
                $catatanAdmin = $klaim->alasan_penolakan ?? 'Klaim Anda ditolak karena dokumen tidak memenuhi persyaratan. Silakan hubungi layanan pelanggan.';
            }

            $html = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Surat Klaim - {$klaim->ID_Klaim}</title>
                <style>
                    @page { margin: 2.5cm; }
                    body { font-family: 'Times New Roman', Arial, sans-serif; line-height: 1.6; color: #333; }
                    .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0284c7; padding-bottom: 20px; }
                    .header h1 { color: #0284c7; font-size: 24px; margin: 0; }
                    .header p { margin: 5px 0 0 0; font-size: 12px; color: #666; }
                    .content { margin-top: 30px; }
                    .greeting { margin-bottom: 20px; }
                    .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    .info-table tr td { padding: 10px 0; vertical-align: top; }
                    .info-table tr td:first-child { width: 180px; font-weight: bold; }
                    .status-approved { color: green; font-weight: bold; }
                    .status-rejected { color: red; font-weight: bold; }
                    .status-pending { color: orange; font-weight: bold; }
                    .catatan { margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #0284c7; }
                    .footer { margin-top: 50px; text-align: right; border-top: 1px solid #ddd; padding-top: 20px; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h1>SURAT KEPUTUSAN KLAIM ASURANSI</h1>
                    <p>InsurTech Indonesia</p>
                </div>
                <div class='content'>
                    <div class='greeting'>
                        <p>Diberitahukan kepada nasabah yang terhormat,</p>
                        <p>Berdasarkan hasil peninjauan dokumen dan polis yang berlaku, berikut adalah detail dan status pengajuan klaim Anda:</p>
                    </div>
                    <table class='info-table'>
                        <tr><td>No. Klaim</td><td>: {$klaim->ID_Klaim}</td></tr>
                        <tr><td>Nama Nasabah</td><td>: {$namaNasabah}</td></tr>
                        <tr><td>Produk Asuransi</td><td>: {$namaProduk}</td></tr>
                        <tr><td>Nilai Klaim</td><td>: <strong>{$nilaiKlaim}</strong></td></tr>
                        <tr><td>Status Klaim</td><td>: <span class='status-" . strtolower($statusDisplay) . "'>{$statusDisplay}</span></td></tr>
                        <tr><td>Tanggal Pengajuan</td><td>: {$tglPengajuan}</td></tr>
                        <tr><td>Tanggal Pencairan</td><td>: " . ($statusDisplay === 'DISETUJUI' ? $tglPencairan : '-') . "</td></tr>
                    </table>
                    <div class='catatan'><strong>Catatan Admin:</strong><br>{$catatanAdmin}</div>
                </div>
                <div class='footer'><p>Hormat kami,</p><p>Tim Manajemen InsurTech</p></div>
            </body>
            </html>
            ";

            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            return $pdf->download('Surat_Klaim_' . $klaim->ID_Klaim . '.pdf');
            
        } catch (\Exception $e) {
            Log::error('Error unduh PDF: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal membuat PDF: ' . $e->getMessage()], 500);
        }
    }
}