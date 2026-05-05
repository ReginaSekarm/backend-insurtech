import { FaCalendarWeek, FaClock, FaCheckCircle, FaUpload, FaTimesCircle, FaMoneyBillWave, FaExclamationTriangle } from 'react-icons/fa';

export default function Notifikasi() {
  const notifications = {
    hariIni: [
      {
        id: 1,
        title: 'Jatuh Tempo Premi Hari Ini',
        message:
          'Premi Asuransi Properti Anda jatuh tempo hari ini. Segera lakukan pembayaran sebesar Rp 200.000 untuk menjaga polis tetap aktif.',
        date: '09 April 2026',
        type: 'warning',
        icon: <FaClock className="text-yellow-500 text-2xl" />,
      },
      {
        id: 2,
        title: 'Klaim Disetujui',
        message:
          'Pengajuan klaim Anda dengan nomor #KL-20260408 telah disetujui. Dana akan ditransfer dalam 3 hari kerja.',
        date: '09 April 2026',
        type: 'success',
        icon: <FaCheckCircle className="text-green-500 text-2xl" />,
      },
      {
        id: 3,
        title: 'Verifikasi Dokumen Berhasil',
        message:
          'KTP dan Kartu Keluarga Anda telah berhasil diverifikasi. Akun Anda kini berstatus Terverifikasi.',
        date: '09 April 2026',
        type: 'success',
        icon: <FaUpload className="text-blue-500 text-2xl" />,
      },
      {
        id: 4,
        title: 'Klaim Ditolak',
        message:
          'Pengajuan klaim Anda dengan nomor #KL-20260408 ditolak karena dokumen kurang memenuhi syarat.',
        date: '09 April 2026',
        type: 'error',
        icon: <FaTimesCircle className="text-red-500 text-2xl" />,
      },
    ],
    kemarin: [
      {
        id: 5,
        title: 'Pembayaran Premi Berhasil',
        message:
          'Pembayaran premi Asuransi Kesehatan sebesar Rp 200.000 telah berhasil dikonfirmasi.',
        date: '08 April 2026',
        type: 'success',
        icon: <FaMoneyBillWave className="text-green-500 text-2xl" />,
      },
      {
        id: 6,
        title: 'Pembayaran Premi Berhasil',
        message:
          'Pembayaran premi Asuransi Kesehatan sebesar Rp 150.000 telah berhasil dikonfirmasi.',
        date: '08 April 2026',
        type: 'success',
        icon: <FaMoneyBillWave className="text-green-500 text-2xl" />,
      },
      {
        id: 7,
        title: 'Pengingat: Lengkapi Profil',
        message:
          'Segera lengkapi data profil Anda untuk mendapatkan layanan yang lebih optimal dari InsurTech.',
        date: '08 April 2026',
        type: 'info',
        icon: <FaExclamationTriangle className="text-orange-500 text-2xl" />,
      },
    ],
  };

  return (
    <div className="max-w-4xl mx-auto space-y-6 pb-10">
      <div className="flex items-center gap-3 border-b pb-3">
        <h1 className="text-2xl font-bold text-gray-800">Notifikasi</h1>
      </div>

      {/* HARI INI */}
      <div>
        <div className="flex items-center gap-2 mb-3">
          <h2 className="text-lg font-semibold text-gray-700">HARI INI</h2>
        </div>
        <div className="space-y-3">
          {notifications.hariIni.map((notif) => (
            <div
              key={notif.id}
              className="bg-white rounded-xl shadow-sm p-4 flex gap-4 items-start"
            >
              <div className="mt-1">{notif.icon}</div>
              <div className="flex-1">
                <h3 className="font-bold text-gray-800">{notif.title}</h3>
                <p className="text-sm text-gray-600 mt-1">{notif.message}</p>
                <p className="text-xs text-gray-400 mt-2">{notif.date}</p>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* KEMARIN */}
      <div>
        <div className="flex items-center gap-2 mb-3">
          <h2 className="text-lg font-semibold text-gray-700">KEMARIN</h2>
        </div>
        <div className="space-y-3">
          {notifications.kemarin.map((notif) => (
            <div
              key={notif.id}
              className="bg-white rounded-xl shadow-sm p-4 flex gap-4 items-start"
            >
              <div className="mt-1">{notif.icon}</div>
              <div className="flex-1">
                <h3 className="font-bold text-gray-800">{notif.title}</h3>
                <p className="text-sm text-gray-600 mt-1">{notif.message}</p>
                <p className="text-xs text-gray-400 mt-2">{notif.date}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}