import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { FaChevronRight, FaHeartbeat, FaHome, FaCar, FaGraduationCap } from 'react-icons/fa';

export default function ProdukAsuransi() {
  const navigate = useNavigate();

  const [activeCategory, setActiveCategory] = useState('Kesehatan');
  const [showModal, setShowModal] = useState(false);
  const [selectedProduct, setSelectedProduct] = useState(null);
  const [formData, setFormData] = useState({
    nilaiPertanggungan: '',
    namaPenerima: '',
    nikPenerima: '',
  });
  const [errorPopup, setErrorPopup] = useState({ show: false, message: '' });

  const categories = [
    { name: 'Kesehatan', icon: <FaHeartbeat />, iconColor: 'text-red-500' },
    { name: 'Properti', icon: <FaHome />, iconColor: 'text-blue-300' },
    { name: 'Kendaraan', icon: <FaCar />, iconColor: 'text-amber-300' },
    { name: 'Pendidikan', icon: <FaGraduationCap />, iconColor: 'text-zinc-600' }
  ];

  const products = {
    Kesehatan: [
      { id: 1, name: 'InsurHealth Premium', price: 200000, priceFormatted: 'Rp 200.000', period: '/ bulan', badge: 'Populer', benefits: ['Rawat inap hingga Rp 500jt', 'Tanpa batas kunjungan dokter', 'Proteksi jiwa + kecelakaan'] },
      { id: 2, name: 'InsurHealth Basic', price: 150000, priceFormatted: 'Rp 150.000', period: '/ bulan', badge: null, benefits: ['Rawat jalan & rawat inap', 'Klaim mudah via aplikasi'] }
    ],
    Properti: [
      { id: 3, name: 'InsurHome Plus', price: 250000, priceFormatted: 'Rp 250.000', period: '/ bulan', badge: 'Populer', benefits: ['Perlindungan kebakaran & banjir', 'Asuransi isi rumah', 'Tanggung jawab hukum'] }
    ],
    Kendaraan: [
      { id: 4, name: 'InsurDrive All Risk', price: 300000, priceFormatted: 'Rp 300.000', period: '/ bulan', badge: 'Populer', benefits: ['Perlindungan All Risk', 'Banjir & gempa', 'Towing gratis'] }
    ],
    Pendidikan: [
      { id: 5, name: 'InsurEdu Plus', price: 350000, priceFormatted: 'Rp 350.000', period: '/ bulan', badge: 'Populer', benefits: ['Dana pendidikan hingga Rp 1M', 'Bebas memilih sekolah', 'Proteksi orang tua'] }
    ]
  };

  const pertanggunganOptions = {
    'InsurHealth Premium': [{ label: 'Rp 100.000.000', value: 100000000 }, { label: 'Rp 250.000.000', value: 250000000 }, { label: 'Rp 500.000.000', value: 500000000 }],
    'InsurHealth Basic': [{ label: 'Rp 50.000.000', value: 50000000 }, { label: 'Rp 100.000.000', value: 100000000 }],
    'InsurHome Plus': [{ label: 'Rp 200.000.000', value: 200000000 }, { label: 'Rp 500.000.000', value: 500000000 }],
    'InsurDrive All Risk': [{ label: 'Rp 150.000.000', value: 150000000 }, { label: 'Rp 300.000.000', value: 300000000 }],
    'InsurEdu Plus': [{ label: 'Rp 500.000.000', value: 500000000 }, { label: 'Rp 1.000.000.000', value: 1000000000 }]
  };

  const currentProducts = products[activeCategory] || [];

  const handlePilihClick = (product) => {
    setSelectedProduct(product);
    setFormData({ nilaiPertanggungan: '', namaPenerima: '', nikPenerima: '' });
    setErrorPopup({ show: false, message: '' });
    setShowModal(true);
  };

  const handleFormChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const hitungPremi = () => {
    if (!selectedProduct) return 0;
    const basePremi = selectedProduct.price;
    const selectedValue = formData.nilaiPertanggungan;
    if (!selectedValue) return basePremi;
    const extra = (parseFloat(selectedValue) / 100000000) * 50000;
    return Math.round(basePremi + extra);
  };

  const handleBeliPolis = () => {
    const { nilaiPertanggungan, namaPenerima, nikPenerima } = formData;
    if (!nilaiPertanggungan || !namaPenerima || !nikPenerima) {
      setErrorPopup({
        show: true,
        message: 'Terdapat data yang belum diisi atau tidak valid. Silahkan periksa kembali:'
      });
      return;
    }

    // Buat ID transaksi unik
    const transactionId = `NMIID-ID${Date.now()}${Math.floor(Math.random() * 1000)}`;

    navigate('/pembayaran-polis', {
      state: {
        total: hitungPremi(),
        productName: selectedProduct.name,
        transactionId: transactionId,
        namaPenerima: namaPenerima,
        nikPenerima: nikPenerima
      }
    });

    setShowModal(false);
    setSelectedProduct(null);
  };

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      <h1 className="text-2xl font-bold text-gray-800">Beli Produk Baru</h1>

      {/* Kategori grid */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        {categories.map((cat) => (
          <button key={cat.name} onClick={() => setActiveCategory(cat.name)}
            className={`flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all ${activeCategory === cat.name ? 'border-sky-950 bg-sky-100 shadow-md' : 'border-gray-200 bg-white hover:shadow-md'}`}>
            <div className={`text-2xl ${cat.iconColor}`}>{cat.icon}</div>
            <span className={`font-medium ${activeCategory === cat.name ? 'text-sky-950' : 'text-gray-600'}`}>Asuransi {cat.name}</span>
          </button>
        ))}
      </div>

      {/* Daftar produk */}
      <div className="mt-6 space-y-4">
        <div className="flex justify-between items-center mb-2">
          <h2 className="text-lg font-semibold text-gray-800">Daftar Produk - {activeCategory}</h2>
          <span className="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{currentProducts.length} produk</span>
        </div>
        {currentProducts.map((product) => (
          <div key={product.id} className="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
            {product.badge && <div className="bg-yellow-400 text-yellow-900 text-xs font-bold px-3 py-1 inline-block rounded-br-lg">{product.badge}</div>}
            <div className="p-5">
              <div className="flex justify-between items-start flex-wrap gap-2">
                <div>
                  <h3 className="text-xl font-bold text-gray-800">{product.name}</h3>
                  <div className="mt-1">
                    <span className="text-2xl font-extrabold text-sky-950">{product.priceFormatted}</span>
                    <span className="text-gray-500 text-sm">{product.period}</span>
                  </div>
                </div>
                <button onClick={() => handlePilihClick(product)} className="bg-sky-950 hover:bg-sky-700 text-white font-semibold px-5 py-2 rounded-lg transition flex items-center gap-1">
                  Pilih 
                </button>
              </div>
              <div className="mt-4 space-y-1">
                {product.benefits.map((benefit, idx) => <p key={idx} className="text-gray-600 text-sm flex items-center gap-2"><span className="text-green-500">✓</span> {benefit}</p>)}
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Modal beli polis */}
      {showModal && selectedProduct && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div className="p-5 space-y-4">
              <div className="bg-blue-50 p-3 rounded-lg text-sm text-sky-950">Pastikan data yang Anda masukkan benar. Polis akan aktif setelah pembayaran pertama dikonfirmasi.</div>
              <div><label className="block text-sm font-medium text-gray-700 mb-1">Jenis Polis</label><input type="text" value={selectedProduct.name} disabled className="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-600" /></div>
              <div><label className="block text-sm font-medium text-gray-700 mb-1">Nilai Pertanggungan (Rp)</label><select name="nilaiPertanggungan" value={formData.nilaiPertanggungan} onChange={handleFormChange} className="w-full border border-gray-300 rounded-lg px-3 py-2"><option value="">Pilih nilai pertanggungan</option>{pertanggunganOptions[selectedProduct.name]?.map(opt => <option key={opt.value} value={opt.value}>{opt.label}</option>)}</select></div>
              <div><label className="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label><input type="text" name="namaPenerima" value={formData.namaPenerima} onChange={handleFormChange} placeholder="Nama lengkap sesuai KTP" className="w-full border border-gray-300 rounded-lg px-3 py-2" /></div>
              <div><label className="block text-sm font-medium text-gray-700 mb-1">NIK Penerima</label><input type="text" name="nikPenerima" value={formData.nikPenerima} onChange={handleFormChange} placeholder="16 digit NIK" className="w-full border border-gray-300 rounded-lg px-3 py-2" /></div>
              <div className="bg-gray-50 p-3 rounded-lg"><p className="text-xs text-gray-500">Premi dihitung berdasarkan jenis polis dan nilai pertanggungan</p><p className="text-2xl font-bold text-sky-950 mt-1">Rp {hitungPremi().toLocaleString('id-ID')}</p></div>
              <div className="flex gap-3 pt-2"><button onClick={() => setShowModal(false)} className="flex-1 border border-gray-300 text-gray-700 font-semibold py-2 rounded-lg">Batal</button><button onClick={handleBeliPolis} className="flex-1 bg-sky-950 hover:bg-gray-500 text-white font-semibold py-2 rounded-lg">Beli Polis Baru</button></div>
            </div>
          </div>
        </div>
      )}

      {/* Popup error data tidak valid */}
      {errorPopup.show && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-5">
            <div className="text-center">
              <div className="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg className="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 className="text-lg font-bold text-gray-800">Data Tidak Valid</h3>
              <p className="text-gray-600 text-sm mt-2">{errorPopup.message}</p>
              <button onClick={() => setErrorPopup({ show: false, message: '' })} className="mt-4 bg-sky-950 hover:bg-gray-500 text-white px-6 py-2 rounded-lg font-medium">Tutup</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}