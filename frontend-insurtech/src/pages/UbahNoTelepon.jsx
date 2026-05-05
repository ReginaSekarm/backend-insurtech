import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';

export default function UbahTelepon() {
  const navigate = useNavigate();
  
  const [currentPhone, setCurrentPhone] = useState('+62 812-3456-7890');
  const [newPhone, setNewPhone] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);

  const validatePhone = (phone) => {
    const digits = phone.replace(/\D/g, '');
    return digits.length >= 10;
  };

  const handleSave = () => {
    if (!validatePhone(newPhone)) {
      setError('Format nomor tidak valid (min. 10 digit angka)');
      setSuccess(false);
      return;
    }
    setError('');
    
    setCurrentPhone(newPhone);
    setSuccess(true);
  
    setTimeout(() => {
     navigate('/profil', { state: { phoneUpdateSuccess: true } });
    }, 2000);
  };

  const handleCancel = () => {
    navigate('/profil');
  };

  return (
    <div className="min-h-screen bg-gray-100">
      {/* Header dengan tombol back */}
      <div className="bg-sky-950 border-b px-5 py-3 flex items-center gap-3 sticky top-0 z-10">
        <h1 className="text-lg font-semibold text-white">Ubah No. Telepon</h1>
      </div>

      <div className="max-w-md mx-auto p-5 space-y-5">
        {/* Nomor telepon saat ini (readonly) */}
        <div className="bg-white p-4 rounded-xl shadow-sm">
          <label className="block text-xs text-gray-500 mb-1">No. Telepon Saat Ini</label>
          <p className="text-gray-800 font-medium">{currentPhone}</p>
        </div>

        {/* Input nomor telepon baru */}
        <div className="bg-white p-4 rounded-xl shadow-sm">
          <label className="block text-xs text-gray-500 mb-1">No. Telepon Baru</label>
          <input
            type="tel"
            value={newPhone}
            onChange={(e) => {
              setNewPhone(e.target.value);
              setError('');
              setSuccess(false);
            }}
            placeholder="+62 8xx-xxxx-xxxx"
            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
          />
          <p className="text-xs text-gray-400 mt-1">Masukkan nomor yang aktif!</p>
          {error && (
            <div className="mt-2 text-xs text-red-600 bg-red-50 p-2 rounded">
              {error}
            </div>
          )}
        </div>

        {/* Tombol Simpan & Batal */}
        <div className="flex gap-3 pt-2">
          <button
            onClick={handleSave}
            className="flex-1 bg-sky-950 hover:bg-sky-800 text-white font-semibold py-2 rounded-xl transition">
            Simpan Perubahan
          </button>
          <button
            onClick={handleCancel}
            className="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 rounded-xl transition">
            <Link to="/profil" className="text-gray-600 hover:text-gray-900">
              Batal
            </Link>
          </button>
        </div>
      </div>
    </div>
  );
}