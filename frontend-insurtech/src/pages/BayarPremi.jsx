import { useLocation, useNavigate } from 'react-router-dom';
import { FaArrowLeft } from 'react-icons/fa';
import { FaHome } from 'react-icons/fa';

export default function BayarPremi() {
  const location = useLocation();
  const navigate = useNavigate();
  const { jenis, noPolis, premi, premiFormatted, periode } = location.state || {};

  const detailTagihan = {
    tanggalJatuhTempo: '10 Jun 2026',
    tanggalPembayaranTerakhir: '10 Mei 2026',
    status: 'Belum dibayar',
    uangPertanggungan: 'Rp 500.000.000',
  };

  if (!jenis) {
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center">
        <div className="bg-white p-6 rounded-xl shadow-md text-center">
          <p className="text-gray-600">Data polis tidak ditemukan.</p>
          <button onClick={() => navigate(-1)} className="mt-3 text-blue-600">Kembali</button>
        </div>
      </div>
    );
  }

  const biayaAdmin = 2500;
  const diskon = 0;
  const totalBayar = (premi || 0) + biayaAdmin - diskon;

  const handleBayarNow = () => {
  if (!jenis || !noPolis) {
    alert('Data polis tidak lengkap');
    return;
  }
  const transactionId = `NMD-ID${Date.now()}${Math.floor(Math.random() * 1000)}`;
  navigate('/pembayaran-premi', {
    state: {
      jenis: jenis,
      noPolis: noPolis,
      total: totalBayar,
      transactionId: transactionId,
    },
  });
  };

  return (
    <div className="min-h-screen bg-gray-100 py-8 px-4">
      <div className="max-w-lg mx-auto bg-white rounded-xl shadow-md overflow-hidden">
        <div className="p-4 border-b flex items-center gap-3">
          <button onClick={() => navigate(-1)} className="text-gray-600 hover:text-gray-900">
          </button>
          <h1 className="text-xl font-bold text-gray-800">Bayar Premi</h1>
        </div>

        <div className="p-5 space-y-5">
            <div className="border-b pb-3 flex items-center gap-3">
                <FaHome className="text-blue-300 text-3xl" />
                <div>
                    <h2 className="text-lg font-semibold text-gray-800">{jenis}</h2>
                    <p className="text-sm text-gray-500">No. Polis: {noPolis}</p>
                </div>
                </div>

          <div className="text-center">
            <p className="text-gray-500 text-sm">Total premi yang harus dibayar bulan ini</p>
            <p className="text-3xl font-extrabold text-sky-900">
              Rp {(premi || 0).toLocaleString('id-ID')}
            </p>
          </div>

          <div className="bg-gray-50 p-4 rounded-lg space-y-3">
            <h3 className="font-semibold text-gray-700 text-sm">DETAIL TAGIHAN</h3>
            <div className="grid grid-cols-2 gap-3 text-sm">
              <div><p className="text-gray-500">Tanggal jatuh tempo</p><p className="font-medium">{detailTagihan.tanggalJatuhTempo}</p></div>
              <div><p className="text-gray-500">Pembayaran terakhir</p><p className="font-medium">{detailTagihan.tanggalPembayaranTerakhir}</p></div>
              <div><p className="text-gray-500">Status</p><p className="font-medium text-red-600">{detailTagihan.status}</p></div>
              <div><p className="text-gray-500">Uang pertanggungan</p><p className="font-medium">{detailTagihan.uangPertanggungan}</p></div>
            </div>
          </div>

          <div className="bg-gray-50 p-4 rounded-lg space-y-3">
            <h3 className="font-semibold text-gray-700 text-sm">RINGKASAN PEMBAYARAN</h3>
            <div className="space-y-2 text-sm">
              <div className="flex justify-between"><span className="text-gray-600">Premi</span><span>Rp {(premi || 0).toLocaleString('id-ID')}</span></div>
              <div className="flex justify-between"><span className="text-gray-600">Biaya admin</span><span>Rp {biayaAdmin.toLocaleString('id-ID')}</span></div>
              <div className="flex justify-between"><span className="text-gray-600">Diskon</span><span>-Rp {diskon.toLocaleString('id-ID')}</span></div>
              <div className="flex justify-between pt-2 border-t font-semibold"><span>Total bayar</span><span className="font-bold text-sky-900">Rp {totalBayar.toLocaleString('id-ID')}</span></div>
            </div>
          </div>

          <button onClick={handleBayarNow} className="w-full bg-sky-950 hover:bg-gray-500 text-white font-semibold py-3 rounded-lg transition shadow-md">
            Bayar Sekarang
          </button>
        </div>
      </div>
    </div>
  );
}