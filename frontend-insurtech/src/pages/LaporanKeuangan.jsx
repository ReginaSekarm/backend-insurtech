import { useState } from 'react';
import { FaDownload } from 'react-icons/fa';

export default function LaporanKeuangan() {
  const [activeCategory, setActiveCategory] = useState('Kesehatan');

  // transaksi per kategori
  const transaksiData = {
    Kesehatan: [
      { id: 1, deskripsi: 'Premi Kesehatan', tanggal: '11 Mar 2025', nominal: 200000, status: 'Pembayaran Bulanan' },
      { id: 2, deskripsi: 'Klaim Dana Rawat Jalan', tanggal: '30 Feb 2025', nominal: 1500000, status: 'Klaim disetujui' },
      { id: 3, deskripsi: 'Klaim Dana Rawat Jalan', tanggal: '30 Feb 2025', nominal: 1000000, status: 'Klaim disetujui' },
    ],
    Properti: [
      { id: 4, deskripsi: 'Premi Properti', tanggal: '5 Mar 2025', nominal: 250000, status: 'Pembayaran Bulanan' },
      { id: 5, deskripsi: 'Klaim Kebakaran', tanggal: '20 Feb 2025', nominal: 5000000, status: 'Klaim disetujui' },
    ],
    Kendaraan: [
      { id: 6, deskripsi: 'Premi Kendaraan', tanggal: '1 Mar 2025', nominal: 300000, status: 'Pembayaran Bulanan' },
      { id: 7, deskripsi: 'Klaim Kecelakaan', tanggal: '10 Feb 2025', nominal: 2500000, status: 'Klaim diproses' },
    ],
    Pendidikan: [
      { id: 8, deskripsi: 'Premi Pendidikan', tanggal: '15 Mar 2025', nominal: 350000, status: 'Pembayaran Bulanan' },
    ],
  };

  const categories = ['Kesehatan', 'Properti', 'Kendaraan', 'Pendidikan'];

  const handleUnduh = () => {
    alert('Mengunduh laporan keuangan (PDF) – simulasi');
  };

  const formatRupiah = (nominal) => {
    return nominal.toLocaleString('id-ID');
  };

  return (
    <div className="max-w-4xl mx-auto space-y-6 pb-10">
      {/* Header */}
      <div className="flex flex-wrap justify-between items-center gap-3">
        <h1 className="text-2xl font-bold text-sky-950">Laporan Keuangan</h1>
      </div>

      {/* Kategori sebagai tab */}
      <div className="flex flex-wrap gap-2 border-b pb-2">
        {categories.map((cat) => (
          <button
            key={cat}
            onClick={() => setActiveCategory(cat)}
            className={`px-4 py-2 text-sm font-medium rounded-full transition ${
              activeCategory === cat
                ? 'bg-blue-600 text-white shadow-md'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            {cat}
          </button>
        ))}
      </div>

      {/* Rincian Transaksi */}
      <div className="bg-white rounded-xl shadow-md p-5">
        <div className="flex justify-between items-center border-b pb-2 mb-3">
          <h2 className="text-lg font-semibold text-gray-800">Rincian Transaksi</h2>
          <span className="text-sm text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
            Total {transaksiData[activeCategory].length} transaksi
          </span>
        </div>
        <div className="space-y-4">
          {transaksiData[activeCategory].map((trx) => (
            <div key={trx.id} className="flex justify-between items-start border-b border-gray-100 pb-3 last:border-0">
              <div>
                <p className="font-medium text-gray-800">{trx.deskripsi}</p>
                <p className="text-xs text-gray-500 mt-1">{trx.tanggal}</p>
                <p className="text-xs text-gray-400 mt-0.5">{trx.status}</p>
              </div>
              <p className="font-semibold text-gray-800">
                Rp {formatRupiah(trx.nominal)}
              </p>
            </div>
          ))}
          {transaksiData[activeCategory].length === 0 && (
            <div className="text-center py-6 text-gray-500">Tidak ada transaksi untuk kategori ini.</div>
          )}
        </div>
      </div>
    </div>
  );
}