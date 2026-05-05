import { useState } from 'react';
import { useNavigate } from 'react-router-dom'; 

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [role, setRole] = useState('nasabah'); // 'nasabah' atau 'admin'
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const navigate = useNavigate();

  const dummyUsers = {
    nasabah: { email: 'nasabah@insurtech.com', password: '123456', dashboard: '/dashboard' },
    admin: { email: 'admin@insurtech.com', password: 'admin123', dashboard: '/admin-dashboard' }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setError('');
    setIsLoading(true);

    // proses login
    setTimeout(() => {
      const user = dummyUsers[role];
      if (email === user.email && password === user.password) {
        if (role === 'admin') {
          navigate('/admin-dashboard');
        } else {
          navigate('/dashboard');
        }
      } else {
        setError('Email atau kata sandi salah. Silakan coba lagi.');
      }
      setIsLoading(false);
    }, 1000);
  };


  return (
    <div className="min-h-screen bg-gray-300 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8 space-y-6">
        {/* Header */}
        <div className="text-center">
          <h2 className="text-3xl font-extrabold text-sky-950">InsurTech</h2>
          <p className="mt-2 text-sm text-gray-600">Platform Asuransi Digital</p>
          <p className="text-xs text-gray-500 mt-1">Silakan masuk ke akun Anda</p>
        </div>

        {/* Form Login */}
        <form className="space-y-5" onSubmit={handleSubmit}>
          <div>
            <label htmlFor="email" className="block text-sm font-medium text-gray-700">
              Alamat Email
            </label>
            <input
              id="email"
              name="email"
              type="email"
              autoComplete="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="contoh@email.com"
            />
          </div>

          <div>
            <label htmlFor="password" className="block text-sm font-medium text-gray-700">
              Kata Sandi
            </label>
            <input
              id="password"
              name="password"
              type="password"
              autoComplete="current-password"
              required
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          {error && (
            <div className="bg-red-50 border-l-4 border-red-500 p-3 text-sm text-red-700">
              {error}
            </div>
          )}

          <div className="flex items-center justify-between">
            <div className="flex items-center">
              <input
                id="remember"
                name="remember"
                type="checkbox"
                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label htmlFor="remember" className="ml-2 block text-sm text-gray-700">
                Ingat saya
              </label>
            </div>
            <div className="text-sm">
              <a href="#" className="font-medium text-sky-950 hover:text-sky-700">
                Lupa kata sandi?
              </a>
            </div>
          </div>

          <button
            type="submit"
            disabled={isLoading}
            className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-sky-900 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {isLoading ? 'Memproses...' : 'Masuk'}
          </button>
        </form>

        {/* Tautan Registrasi */}
        <div className="text-center text-sm">
          <span className="text-gray-600">Belum punya akun? </span>
          <a href="#" className="font-medium text-sky-900 hover:text-sky-700">
            Daftar Sekarang
          </a>
        </div>

        {/* Informasi demo */}
        <div className="mt-4 p-3 bg-gray-50 rounded-md text-xs text-gray-500 text-center">
          <p className="font-semibold">🔐 Demo Login</p>
          <p>Nasabah: nasabah@insurtech.com / 123456</p>
          <p>Admin: admin@insurtech.com / admin123</p>
        </div>
      </div>
    </div>
  );
}