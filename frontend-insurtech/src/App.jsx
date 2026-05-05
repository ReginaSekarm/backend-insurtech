import { BrowserRouter, Routes, Route } from 'react-router-dom';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import DashboardNasabah from './pages/DashboardNasabah';
import Profil from './pages/Profil';
import ProdukAsuransi from './pages/ProdukAsuransi';
import PolisSaya from './pages/PolisSaya';
import AjukanKlaim from './pages/AjukanKlaim';
import StatusKlaim from './pages/StatusKlaim';
import Notifikasi from './pages/Notifikasi';
import PembayaranPolis from './pages/PembayaranPolis';
import BayarPremi from './pages/BayarPremi';
import PembayaranPremi from './pages/PembayaranPremi';
import LaporanKeuangan from './pages/LaporanKeuangan';
import UbahNoTelepon from './pages/UbahNoTelepon';
import UbahPassword from './pages/UbahPassword';
import RiwayatTransaksi from './pages/RiwayatTransaksi';
import NasabahLayout from './pages/NasabahLayout';


function App() {
  return (
    <BrowserRouter>
      <Routes>
        {/* Public routes */}
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />

        {/* Protected routes untuk Nasabah */}
        <Route element={<NasabahLayout />}>
        <Route path="/dashboard" element={<DashboardNasabah />} />
        <Route path="/profil" element={<Profil />} />
        <Route path="/produk" element={<ProdukAsuransi />} />
        <Route path="/polis-saya" element={<PolisSaya />} />
        <Route path="/ajukan-klaim" element={<AjukanKlaim />} />
        <Route path="/notifikasi" element={<Notifikasi />} />
        <Route path="/pembayaran-polis" element={<PembayaranPolis />} />
        <Route path="/bayar-premi" element={<BayarPremi />} />
        <Route path="/pembayaran-premi" element={<PembayaranPremi />} />
        <Route path="/laporan-keuangan" element={<LaporanKeuangan />} />

        <Route path="/status-klaim" element={<StatusKlaim />} />
        <Route path="/ubah-telepon" element={<UbahNoTelepon />} />
        <Route path="/ubah-password" element={<UbahPassword />} />
        <Route path="/riwayat-transaksi" element={<RiwayatTransaksi />} />

        </Route>
      </Routes>
    </BrowserRouter>
  );
}

export default App