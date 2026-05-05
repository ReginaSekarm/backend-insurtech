import { useLocation, useNavigate } from 'react-router-dom';
import { QRCodeSVG } from 'qrcode.react';
import { FaDownload, FaHome } from 'react-icons/fa';

export default function PembayaranPremi() {
  const location = useLocation();
  const navigate = useNavigate();
  const { jenis, noPolis, total, transactionId } = location.state || {
    jenis: 'Asuransi Properti',
    noPolis: 'AJ-20240101',
    total: 200000,
    transactionId: 'NMD-ID2025444802321',
  };

  const handleSimpanQR = () => {
    alert('QR Code disimpan (simulasi).');
  };

  const handleKembali = () => {
    navigate(-1);
  };

  return (
    <div className="min-h-screen bg-gray-100 py-8 px-4">
      <div className="max-w-lg mx-auto bg-white rounded-xl shadow-md overflow-hidden">
        {/* Header */}
        <div className="p-4 border-b flex items-center gap-3">
          <button onClick={handleKembali} className="text-gray-600 hover:text-gray-900">
          </button>
          <h1 className="text-xl font-bold text-gray-800">Pembayaran Premi</h1>
        </div>

        <div className="p-5 space-y-5">
            <div className="border-b pb-3 flex items-center gap-3">
                <FaHome className="text-blue-300 text-3xl" />
                <div>
                    <h2 className="text-lg font-semibold text-gray-800">{jenis}</h2>
                    <p className="text-sm text-gray-500">No. Polis: {noPolis}</p>
                </div>
            </div>

          {/* Total Pembayaran */}
          <div className="text-center">
            <p className="text-gray-500 text-sm">Total premi yang harus dibayar bulan ini</p>
            <p className="text-3xl font-extrabold text-sky-950">
              Rp {total.toLocaleString('id-ID')}
            </p>
          </div>

          {/* QR Code */}
          <div className="flex flex-col items-center border-y py-4">
            <QRCodeSVG value={transactionId} size={180} />
            <p className="text-xs text-gray-500 mt-2 break-all text-center px-4">
              {transactionId}
            </p>
          </div>

          {/* Petunjuk Pembayaran QRIS */}
          <div className="text-sm space-y-2">
            <h3 className="font-semibold text-gray-700">Petunjuk Pembayaran QRIS</h3>
            <ol className="list-decimal pl-5 space-y-1 text-gray-600 text-xs">
              <li>Simpan atau screenshot Kode QR, yang berlaku selama 20 menit. Kamu bisa muat ulang untuk dapatkan kode baru.</li>
              <li>Scan Kode QR dengan m-banking, dompet elektronik, atau aplikasi pembayaran lain.</li>
              <li>Pastikan rincian pembayaran telah sesuai, lalu lanjutkan pembayaran.</li>
              <li>Transaksi akan secara otomatis terbayar dan diperbarui setelah pembayaran berhasil.</li>
              <li>Simpan bukti pembayaran untuk verifikasi lebih lanjut jika diperlukan.</li>
              <li>Pembayaran tidak dapat diproses jika menggunakan metode pembayaran yang tidak sah.</li>
            </ol>
          </div>

          {/* Tombol Simpan QR Code */}
          <button
            onClick={handleSimpanQR}
            className="w-full bg-sky-950 hover:bg-gray-500 text-white font-semibold py-3 rounded-lg flex items-center justify-center gap-2 transition"
          >
            <FaDownload size={16} />
            Simpan Kode QR
          </button>
        </div>
      </div>
    </div>
  );
}