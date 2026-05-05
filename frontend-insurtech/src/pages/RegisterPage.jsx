import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function RegisterPage() {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
    phone: '',
    password: '',
    confirmPassword: '',
    ktpFile: null,
    kkFile: null,
    agreeTerms: false,
  });

  const [errors, setErrors] = useState({});
  const [isLoading, setIsLoading] = useState(false);
  const [successMessage, setSuccessMessage] = useState('');

  // Simulasi daftar email yang sudah terdaftar
  const existingEmails = ['existing@example.com', 'user@insurtech.com'];

  const handleChange = (e) => {
    const { name, value, type, checked, files } = e.target;
    if (type === 'file') {
      setFormData({ ...formData, [name]: files[0] });
    } else if (type === 'checkbox') {
      setFormData({ ...formData, [name]: checked });
    } else {
      setFormData({ ...formData, [name]: value });
    }
  
    if (errors[name]) {
      setErrors({ ...errors, [name]: '' });
    }
  };

  const validateForm = () => {
    const newErrors = {};
    if (!formData.fullName.trim()) newErrors.fullName = 'Nama lengkap harus diisi';
    if (!formData.email.trim()) {
      newErrors.email = 'Email harus diisi';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Format email tidak valid';
    } else if (existingEmails.includes(formData.email)) {
      newErrors.email = 'Email sudah terdaftar, silakan gunakan email lain';
    }
    if (!formData.phone.trim()) {
      newErrors.phone = 'Nomor telepon harus diisi';
    } else if (!/^[0-9]{10,13}$/.test(formData.phone.replace(/\s/g, ''))) {
      newErrors.phone = 'Nomor telepon harus 10-13 digit angka';
    }
    if (!formData.password) {
      newErrors.password = 'Kata sandi harus diisi';
    } else if (formData.password.length < 6) {
      newErrors.password = 'Kata sandi minimal 6 karakter';
    }
    if (formData.password !== formData.confirmPassword) {
      newErrors.confirmPassword = 'Konfirmasi kata sandi tidak cocok';
    }
    if (!formData.ktpFile) {
      newErrors.ktpFile = 'Upload KTP (foto/scan) wajib';
    } else if (formData.ktpFile.size > 2 * 1024 * 1024) {
      newErrors.ktpFile = 'Ukuran file maksimal 2MB';
    }
    if (!formData.kkFile) {
      newErrors.kkFile = 'Upload KK (foto/scan) wajib';
    } else if (formData.kkFile.size > 2 * 1024 * 1024) {
      newErrors.kkFile = 'Ukuran file maksimal 2MB';
    }
    if (!formData.agreeTerms) {
      newErrors.agreeTerms = 'Anda harus menyetujui syarat & ketentuan';
    }
    return newErrors;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const newErrors = validateForm();
    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    setIsLoading(true);
    setTimeout(() => {
      setIsLoading(false);
      setSuccessMessage('Pendaftaran berhasil! Silakan login.');
      navigate('/login'); // pindah ke halaman login
    }, 1500);
  };

  return (
    <div className="min-h-screen bg-gray-300 py-10 px-4 sm:px-6 lg:px-8">
      <div className="max-w-2xl mx-auto bg-white rounded-2xl shadow-2xl p-6 md:p-8">
        <div className="text-center mb-6">
          <h2 className="text-3xl font-extrabold text-sky-950">Daftar InsurTech</h2>
          <p className="text-gray-600 mt-2">Isi data diri untuk membuat akun nasabah</p>
        </div>

        {successMessage && (
          <div className="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {successMessage}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-5">
          {/* Nama Lengkap */}
          <div>
            <label className="block text-sm font-medium text-gray-700">Nama Lengkap <span className="text-red-500">*</span></label>
            <input
              type="text"
              name="fullName"
              value={formData.fullName}
              onChange={handleChange}
              className="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Sesuai KTP"
            />
            {errors.fullName && <p className="text-red-500 text-xs mt-1">{errors.fullName}</p>}
          </div>

          {/* Email */}
          <div>
            <label className="block text-sm font-medium text-gray-700">Email <span className="text-red-500">*</span></label>
            <input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              className="mt-1 block w-full border border-gray-300 rounded-md p-2"
              placeholder="nama@example.com"
            />
            {errors.email && <p className="text-red-500 text-xs mt-1">{errors.email}</p>}
          </div>

          {/* No Telepon */}
          <div>
            <label className="block text-sm font-medium text-gray-700">Nomor Telepon <span className="text-red-500">*</span></label>
            <input
              type="tel"
              name="phone"
              value={formData.phone}
              onChange={handleChange}
              className="mt-1 block w-full border border-gray-300 rounded-md p-2"
              placeholder="081234567890"
            />
            {errors.phone && <p className="text-red-500 text-xs mt-1">{errors.phone}</p>}
          </div>

          {/* Password */}
          <div>
            <label className="block text-sm font-medium text-gray-700">Kata Sandi <span className="text-red-500">*</span></label>
            <input
              type="password"
              name="password"
              value={formData.password}
              onChange={handleChange}
              className="mt-1 block w-full border border-gray-300 rounded-md p-2"
              placeholder="Minimal 6 karakter"
            />
            {errors.password && <p className="text-red-500 text-xs mt-1">{errors.password}</p>}
          </div>

          {/* Konfirmasi Password */}
          <div>
            <label className="block text-sm font-medium text-gray-700">Konfirmasi Kata Sandi <span className="text-red-500">*</span></label>
            <input
              type="password"
              name="confirmPassword"
              value={formData.confirmPassword}
              onChange={handleChange}
              className="mt-1 block w-full border border-gray-300 rounded-md p-2"
            />
            {errors.confirmPassword && <p className="text-red-500 text-xs mt-1">{errors.confirmPassword}</p>}
          </div>

          {/* Upload KTP */}
          <div>
            <label className="block text-sm font-medium text-gray-700">Upload KTP (foto/PDF) <span className="text-red-500">*</span></label>
            <input
              type="file"
              id="ktpFile"
              name="ktpFile"
              accept="image/*,application/pdf"
              onChange={handleChange}
              className="mt-1 block w-full text-sm text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            />
            {errors.ktpFile && <p className="text-red-500 text-xs mt-1">{errors.ktpFile}</p>}
            <p className="text-gray-400 text-xs mt-1">Maks. 2MB, format JPG/PNG/PDF</p>
          </div>

          {/* Upload KK */}
          <div>
            <label className="block text-sm font-medium text-gray-700">Upload Kartu Keluarga (foto/PDF) <span className="text-red-500">*</span></label>
            <input
              type="file"
              id="kkFile"
              name="kkFile"
              accept="image/*,application/pdf"
              onChange={handleChange}
              className="mt-1 block w-full text-sm text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            />
            {errors.kkFile && <p className="text-red-500 text-xs mt-1">{errors.kkFile}</p>}
            <p className="text-gray-400 text-xs mt-1">Maks. 2MB, format JPG/PNG/PDF</p>
          </div>

          {/* Syarat & Ketentuan */}
          <div className="flex items-start">
            <input
              type="checkbox"
              name="agreeTerms"
              checked={formData.agreeTerms}
              onChange={handleChange}
              className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1"
            />
            <label className="ml-2 block text-sm text-gray-700">
              Saya menyetujui <a href="#" className="text-blue-600 hover:underline">Syarat & Ketentuan</a> dan <a href="#" className="text-blue-600 hover:underline">Kebijakan Privasi</a> InsurTech.
            </label>
          </div>
          {errors.agreeTerms && <p className="text-red-500 text-xs -mt-2">{errors.agreeTerms}</p>}

          {/* Tombol Daftar */}
          <button
            type="submit"
            disabled={isLoading}
            className="w-full bg-sky-900 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-md transition disabled:opacity-50">
            {isLoading ? 'Memproses...' : 'Daftar Sekarang'}
          </button>

          <div className="text-center text-sm text-gray-600">
            Sudah punya akun?{' '}
            <a href="/login" className="font-semibold text-cyan-900 hover:text-cyan-500">
              Masuk di sini
            </a>
          </div>
        </form>
      </div>
    </div>
  );
}
