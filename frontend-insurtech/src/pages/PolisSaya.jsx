import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { FaSearch, FaHome, FaHeartbeat, FaCar, FaGraduationCap } from 'react-icons/fa';

export default function PolisSaya() {
  const navigate = useNavigate();
  const [showSearch, setShowSearch] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');

  const polisList = [
    {
      id: 1,
      jenis: 'Asuransi Properti',
      noPolis: 'AJ-20240101',
      premi: 200000,
      premiFormatted: 'Rp 200.000',
      periode: '/ bulan',
      icon: <FaHome className="text-blue-300" />,
    },
    {
      id: 2,
      jenis: 'Asuransi Kesehatan',
      noPolis: 'AK-20240215',
      premi: 150000,
      premiFormatted: 'Rp 150.000',
      periode: '/ bulan',
      icon: <FaHeartbeat className="text-red-500" />,
    },
    {
      id: 3,
      jenis: 'Asuransi Kendaraan',
      noPolis: 'AV-20231201',
      premi: 300000,
      premiFormatted: 'Rp 300.000',
      periode: '/ bulan',
      icon: <FaCar className="text-amber-300" />,
    },
  ];

  const filteredPolis = polisList.filter(
    (polis) =>
      polis.jenis.toLowerCase().includes(searchTerm.toLowerCase()) ||
      polis.noPolis.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      {/* Header: Judul di kiri, ikon search di kanan */}
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-800">Polis Saya</h1>
        <button
          onClick={() => setShowSearch(!showSearch)}
          className="text-gray-500 hover:text-blue-600 transition"
        >
          <FaSearch size={22} />
        </button>
      </div>

      {/* Total polis */}
      <div>
        <span className="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
          Polis Saya ({polisList.length})
        </span>
      </div>

      {/* Input pencarian muncul jika showSearch true */}
      {showSearch && (
        <div className="relative">
          <input
            type="text"
            placeholder="Cari polis (nama atau nomor polis)..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
            autoFocus
          />
          <FaSearch className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
        </div>
      )}

      {/* Daftar Polis hasil filter */}
      <div className="space-y-4">
        {filteredPolis.map((polis) => (
          <div key={polis.id} className="space-y-2">
            {/* Card Informasi Polis dengan ikon di kiri */}
            <div className="bg-white rounded-xl shadow-md border border-gray-100 p-5 flex items-start gap-4">
              {/* Ikon besar di kiri */}
              <div className="text-3xl mt-1">{polis.icon}</div>
              {/* Detail polis */}
              <div className="flex-1">
                <h3 className="text-xl font-bold text-sky-900">{polis.jenis}</h3>
                <p className="text-sm text-gray-500 mt-1">No. Polis: {polis.noPolis}</p>
                <div className="mt-2">
                  <span className="text-2xl font-extrabold text-sky-900">
                    {polis.premiFormatted}
                  </span>
                  <span className="text-gray-500 text-sm">{polis.periode}</span>
                </div>
              </div>
            </div>

            {/* Card Tombol Aksi */}
            <div className="bg-white rounded-xl shadow-md border border-gray-100 p-3">
              <div className="flex flex-col sm:flex-row gap-3">
                <Link
                  to="/ajukan-klaim"
                  state={{ polisId: polis.noPolis, polisJenis: polis.jenis }}
                  className="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 border border-sky-950 text-sky-900 font-medium rounded-lg hover:bg-blue-300 transition"
                >
                  + Ajukan Klaim
                </Link>
               <Link
                  to="/bayar-premi"
                  state={{
                    jenis: polis.jenis,
                    noPolis: polis.noPolis,
                    premi: polis.premi,
                    premiFormatted: polis.premiFormatted,
                    periode: polis.periode
                  }}
                  className="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-sky-900 hover:bg-gray-500 text-white font-medium rounded-lg transition shadow-sm"
                >
                  Bayar Premi
                </Link>
              </div>
            </div>
          </div>
        ))}

        {filteredPolis.length === 0 && (
          <div className="text-center py-12 bg-white rounded-xl shadow">
            <p className="text-gray-500">Tidak ada polis yang cocok.</p>
          </div>
        )}
      </div>
    </div>
  );
}