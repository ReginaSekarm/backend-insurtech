import { useState } from 'react';
import {
  FaMoneyBillWave,
  FaHandHoldingUsd,
  FaFileInvoiceDollar,
  FaDownload,
} from 'react-icons/fa';

export default function RiwayatTransaksi() {
  const [transaksi] = useState([
    { id: 1, tanggal: '2026-03-01', jenis: 'Pembayaran Premi', produk: 'Polis Kesehatan', nominal: 200000 },
    { id: 2, tanggal: '2026-03-01', jenis: 'Pembayaran Premi', produk: 'Polis Kendaraan', nominal: 150000 },
    { id: 3, tanggal: '2026-02-15', jenis: 'Pencairan Klaim', produk: 'Klaim Rawat Inap', nominal: 1500000 },
    { id: 4, tanggal: '2026-02-01', jenis: 'Pembayaran Premi', produk: 'Polis Properti', nominal: 150000 },
    { id: 5, tanggal: '2026-01-10', jenis: 'Pembayaran Premi', produk: 'Polis Kesehatan', nominal: 150000 },
  ]);

  const [selectedMonth, setSelectedMonth] = useState('');

  const getIcon = (jenis) => {
    if (jenis === 'Pembayaran Premi') {
      return <FaMoneyBillWave className="text-green-500 text-2xl" />;
    } else if (jenis === 'Pencairan Klaim') {
      return <FaHandHoldingUsd className="text-blue-500 text-2xl" />;
    }
    return <FaFileInvoiceDollar className="text-gray-500 text-2xl" />;
  };

  // transaksi per bulan
  const groupByMonth = (data) => {
    const grouped = {};
    data.forEach(item => {
      const date = new Date(item.tanggal);
      const monthYear = date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }).toUpperCase();
      if (!grouped[monthYear]) grouped[monthYear] = [];
      grouped[monthYear].push(item);
    });
    return grouped;
  };

  const grouped = groupByMonth(transaksi);
  const months = Object.keys(grouped).sort((a, b) => {
    const dateA = new Date(a);
    const dateB = new Date(b);
    return dateB - dateA;
  });

  const filteredMonths = selectedMonth ? months.filter(m => m === selectedMonth) : months;

  const handleUnduhLaporan = () => {
    alert('Mengunduh laporan keuangan (simulasi PDF).');
  };

  return (
    <div className="max-w-4xl mx-auto space-y-6 pb-10">
      {/* Header */}
      <div className="flex flex-wrap justify-between items-center gap-3">
        <h1 className="text-2xl font-bold text-gray-800">Riwayat Transaksi</h1>
        <div className="flex flex-wrap items-center gap-3">
          <select
            value={selectedMonth}
            onChange={(e) => setSelectedMonth(e.target.value)}
            className="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Periode</option>
            <option value="JANUARI">Januari</option>
            <option value="FEBRUARI">Februari</option>
            <option value="MARET">Maret</option>
            <option value="APRIL">April</option>
            <option value="MEI">Mei</option>
            <option value="JUNI">Juni</option>
            <option value="JULI">Juli</option>
            <option value="AGUSTUS">Agustus</option>
            <option value="SEPTEMBER">September</option>
            <option value="OKTOBER">Oktober</option>
            <option value="NOVEMBER">November</option>
            <option value="DESEMBER">Desember</option>
          </select>
        </div>
      </div>

      {/* Daftar transaksi per bulan */}
      <div className="space-y-6">
        {filteredMonths.map((month) => (
          <div key={month} className="bg-white rounded-xl shadow-md p-5">
            <h2 className="text-lg font-bold text-gray-800 border-b pb-2 mb-3">{month}</h2>
            <div className="space-y-4">
              {grouped[month].map((trx, idx) => (
                <div key={idx} className="flex gap-4 items-start">
                  <div className="mt-1">{getIcon(trx.jenis)}</div>
                  <div className="flex-1">
                    <div className="flex flex-wrap justify-between items-start">
                      <div>
                        <p className="font-semibold text-gray-800">{trx.jenis}</p>
                        <p className="text-xs text-gray-500 mt-0.5">
                          {trx.produk} · {new Date(trx.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}
                        </p>
                      </div>
                      <p className={`font-bold ${trx.jenis === 'Pencairan Klaim'}`}>
                        {trx.jenis === 'Pencairan Klaim'} Rp {trx.nominal.toLocaleString('id-ID')}
                      </p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        ))}
        {filteredMonths.length === 0 && (
          <div className="text-center py-12 bg-white rounded-xl shadow">
            <p className="text-gray-500">Tidak ada transaksi untuk periode tersebut.</p>
          </div>
        )}
      </div>
    </div>
  );
}