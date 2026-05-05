import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { FaCheckCircle, FaTimesCircle, FaEye, FaEyeSlash } from 'react-icons/fa';

export default function UbahPassword() {
  const navigate = useNavigate();
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [showSuccessPopup, setShowSuccessPopup] = useState(false);
  const [showErrorPopup, setShowErrorPopup] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  // Validasi password
  const hasMinLength = password.length >= 8;
  const hasUpperCase = /[A-Z]/.test(password);
  const hasNumber = /[0-9]/.test(password);
  const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

  const isPasswordValid = hasMinLength && hasUpperCase && hasNumber && hasSpecialChar;
  const isPasswordMatch = password === confirmPassword && password !== '';

  const getPasswordStrength = () => {
    if (!password) return { text: '', color: '' };
    const metCount = [hasMinLength, hasUpperCase, hasNumber, hasSpecialChar].filter(Boolean).length;
    if (metCount === 4) return { text: 'Kuat', color: 'text-green-600' };
    return { text: 'Lemah', color: 'text-red-600' };
  };

  const strength = getPasswordStrength();

  const handleSave = () => {
    if (!isPasswordValid) {
      setErrorMessage('Kata sandi Anda belum memenuhi ketentuan.');
      setShowErrorPopup(true);
      return;
    }
    if (!isPasswordMatch) {
      setErrorMessage('Konfirmasi kata sandi tidak cocok.');
      setShowErrorPopup(true);
      return;
    }
    setShowSuccessPopup(true);
  };

  const handleSuccessOk = () => {
    setShowSuccessPopup(false);
    navigate('/profil');
  };

  const handleErrorOk = () => {
    setShowErrorPopup(false);
  };

  return (
    <div className="max-w-md mx-auto space-y-6 pb-10">
      {/* Header */}
      <div className="flex items-center gap-3">
        <button onClick={() => navigate(-1)} className="text-gray-600 hover:text-gray-900"></button>
        <h1 className="text-2xl font-bold text-gray-800">Ubah Password</h1>
      </div>

      <div className="bg-white rounded-xl shadow-md p-5 space-y-5">
        {/* Password Baru dengan icon mata */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
          <div className="relative">
            <input
              type={showPassword ? 'text' : 'password'}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:ring-2 focus:ring-blue-500"
              placeholder="Masukkan password baru"
            />
            <button
              type="button"
              onClick={() => setShowPassword(!showPassword)}
              className="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700"
            >
              {showPassword ? <FaEyeSlash size={18} /> : <FaEye size={18} />}
            </button>
          </div>
          {password && (
            <p className={`text-sm mt-1 font-semibold ${strength.color}`}>
              {strength.text}
            </p>
          )}
        </div>

        {/* Konfirmasi Password Baru dengan icon mata */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
          <div className="relative">
            <input
              type={showConfirmPassword ? 'text' : 'password'}
              value={confirmPassword}
              onChange={(e) => setConfirmPassword(e.target.value)}
              className="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:ring-2 focus:ring-blue-500"
              placeholder="Konfirmasi password baru"
            />
            <button
              type="button"
              onClick={() => setShowConfirmPassword(!showConfirmPassword)}
              className="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700"
            >
              {showConfirmPassword ? <FaEyeSlash size={18} /> : <FaEye size={18} />}
            </button>
          </div>
          {confirmPassword && (
            <p className={`text-sm mt-1 ${password === confirmPassword ? 'text-green-600' : 'text-red-600'}`}>
              {password === confirmPassword ? 'Kata sandi cocok' : 'Kata sandi tidak cocok'}
            </p>
          )}
        </div>

        {/* Syarat Password */}
        <div className="bg-gray-50 p-3 rounded-lg space-y-1">
          <p className="text-xs font-semibold text-gray-600">Syarat Password</p>
          <ul className="text-xs space-y-1 text-gray-600">
            <li className="flex items-center gap-2">
              {hasMinLength ? <FaCheckCircle className="text-green-500" /> : <FaTimesCircle className="text-gray-400" />}
              Minimal 8 Karakter
            </li>
            <li className="flex items-center gap-2">
              {hasUpperCase ? <FaCheckCircle className="text-green-500" /> : <FaTimesCircle className="text-gray-400" />}
              Mengandung huruf besar (A-Z)
            </li>
            <li className="flex items-center gap-2">
              {hasNumber ? <FaCheckCircle className="text-green-500" /> : <FaTimesCircle className="text-gray-400" />}
              Mengandung angka (0-9)
            </li>
            <li className="flex items-center gap-2">
              {hasSpecialChar ? <FaCheckCircle className="text-green-500" /> : <FaTimesCircle className="text-gray-400" />}
              Mengandung karakter unik (!@#$%)
            </li>
          </ul>
        </div>

        {/* Tombol */}
        <div className="flex gap-3 pt-2">
          <button
            onClick={handleSave}
            className="flex-1 bg-sky-950 hover:bg-gray-500 text-white font-semibold py-2 rounded-lg transition"
          >
            Simpan Perubahan
          </button>
          <button
            onClick={() => navigate(-1)}
            className="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 rounded-lg transition"
          >
            Batal
          </button>
        </div>
      </div>

      {/* Popup Error */}
      {showErrorPopup && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-5">
            <div className="text-center">
              <div className="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg className="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 className="text-lg font-bold text-gray-800">Kata Sandi Tidak Memenuhi Syarat</h3>
              <p className="text-gray-600 text-sm mt-2">{errorMessage}</p>
              <button onClick={handleErrorOk} className="mt-4 bg-white hover:bg-gray-300 text-black px-6 py-2 rounded-lg font-medium shadow-md border border-gray-800 overflow-hidden ">
                Coba Lagi
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Popup Success */}
      {showSuccessPopup && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-5">
            <div className="text-center">
              <div className="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <h3 className="text-lg font-bold text-gray-800">Kata Sandi Berhasil Diubah</h3>
              <p className="text-gray-600 text-sm mt-2">Kata sandi Anda telah berhasil diperbarui.</p>
              <button onClick={handleSuccessOk} className="mt-4 bg-white hover:bg-gray-300 text-black px-6 py-2 rounded-lg font-medium shadow-md border border-gray-800 overflow-hidden">
                Oke, Mengerti
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}