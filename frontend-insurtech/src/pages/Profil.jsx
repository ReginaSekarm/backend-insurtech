import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useState, useEffect } from 'react';
import { FaUserCircle, FaIdCard, FaFileAlt, FaKey, FaSignOutAlt, FaHome, FaFileInvoice, FaShoppingCart, FaClipboardList, FaUser } from 'react-icons/fa';

export default function Profil() {
  const navigate = useNavigate();
  const location = useLocation();
  const [showPhoneSuccess, setShowPhoneSuccess] = useState(false);

  useEffect(() => {
    if (location.state?.phoneUpdateSuccess) {
      setShowPhoneSuccess(true);
      window.history.replaceState({}, document.title);
      // Auto hilang setelah 3 detik
      const timer = setTimeout(() => setShowPhoneSuccess(false), 3000);
      return () => clearTimeout(timer);
    }
  }, [location.state]);

  const profilData = {
    fullName: 'Gendis Ayu Pratiwi',
    email: 'gendis@gmail.com',
    phone: '+62 812-3456-7890',
    ktpUploadDate: '12 Jan 2026',
    kkUploadDate: '12 Jan 2026',
    profileImage: null,
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    navigate('/login');
  };

  return (
    <div className="min-h-screen bg-gray-100 pb-20 relative">
      {/* Header */}
      <div className="bg-gradient-to-r from-sky-950 to-sky-800 text-white px-5 pt-8 pb-6 rounded-b-3xl shadow-lg">
        <div className="flex items-center gap-4">
          <div className="w-16 h-16 bg-white/30 rounded-full flex items-center justify-center">
            <FaUserCircle className="text-4xl text-white" />
          </div>
          <div>
            <h1 className="text-xl font-bold">{profilData.fullName}</h1>
            <p className="text-sm text-blue-100 flex items-center gap-1">
              <span className="inline-block w-2 h-2 bg-green-400 rounded-full"></span>
              Akun Terverifikasi
            </p>
          </div>
        </div>
      </div>

      <div className="px-5 py-6 space-y-6">
        {/* Data Diri Card */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
          <h2 className="text-md font-semibold text-gray-800 border-b pb-2 mb-3 flex items-center gap-2">
            <FaUser className="text-sky-800" size={16} /> DATA DIRI
          </h2>
          <div className="space-y-3">
            <div>
              <p className="text-xs text-gray-400">Nama Lengkap</p>
              <p className="text-gray-800 font-medium">{profilData.fullName}</p>
            </div>
            <div>
              <p className="text-xs text-gray-400">Email</p>
              <p className="text-gray-800">{profilData.email}</p>
            </div>
            <div>
              <p className="text-xs text-gray-400">No. Telepon</p>
              <p className="text-gray-800">{profilData.phone}</p>
            </div>
            <Link to="/ubah-telepon" className="text-sky-900 text-sm font-semibold flex items-center gap-1 mt-2">
              Atur Sekarang <span>›</span>
            </Link>
          </div>
        </div>

        {/* Dokumen Card */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
          <h2 className="text-md font-semibold text-gray-800 border-b pb-2 mb-3 flex items-center gap-2">
            <FaFileAlt className="text-sky-800" size={16} /> Dokumen
          </h2>
          <div className="space-y-4">
            <div className="flex justify-between items-center">
              <div>
                <p className="font-medium text-gray-700">KTP</p>
                <p className="text-xs text-gray-400">Diunggah {profilData.ktpUploadDate}</p>
              </div>
              <button className="text-sky-800 text-sm font-medium">Terverifikasi</button>
            </div>
            <div className="flex justify-between items-center">
              <div>
                <p className="font-medium text-gray-700">Kartu Keluarga</p>
                <p className="text-xs text-gray-400">Diunggah {profilData.kkUploadDate}</p>
              </div>
              <button className="text-sky-800 text-sm font-medium">Terverifikasi</button>
            </div>
          </div>
        </div>

        {/* Ubah Password & Logout */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <Link to="/ubah-password" className="w-full flex items-center justify-between p-4 border-b border-gray-100 hover:bg-gray-50 transition">
            <div className="flex items-center gap-3">
              <FaKey className="text-gray-500" />
              <span className="text-gray-700">Ubah Password</span>
            </div>
          </Link>
          <button 
            onClick={handleLogout}
            className="w-full flex items-center justify-between p-4 hover:bg-red-50 transition text-red-600"
          >
            <div className="flex items-center gap-3">
              <FaSignOutAlt />
              <span>Keluar</span>
            </div>
          </button>
        </div>
      </div>

      {/* Popup sukses ubah telepon */}
      {showPhoneSuccess && (
        <div className="fixed bottom-5 left-1/2 transform -translate-x-1/2 z-50 animate-fade-up">
          <div className="bg-green-50 border border-green-200 rounded-xl shadow-lg p-4 max-w-sm mx-auto">
            <div className="flex items-start gap-3">
              <div className="flex-shrink-0">
                <svg className="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <div>
                <h4 className="font-semibold text-green-800">Nomor Telepon Berhasil Diperbarui</h4>
                <p className="text-sm text-green-700">Nomor telepon Anda telah berhasil diubah</p>
              </div>
              <button 
                onClick={() => setShowPhoneSuccess(false)}
                className="ml-auto text-green-600 hover:text-green-800"
              >
                ✕
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}