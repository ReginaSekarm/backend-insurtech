import { NavLink } from 'react-router-dom';
import { FaHome, FaFileInvoice, FaShoppingCart, FaClipboardList, FaUser, FaTimes } from 'react-icons/fa';

export default function Sidebar({ isOpen, onClose }) {
  const navItems = [
    { to: '/dashboard', label: 'Beranda', icon: FaHome },
    { to: '/polis-saya', label: 'Polis Saya', icon: FaFileInvoice },
    { to: '/produk', label: 'Beli Produk', icon: FaShoppingCart },
    { to: '/status-klaim', label: 'Klaim', icon: FaClipboardList },
    { to: '/profil', label: 'Profil', icon: FaUser },
  ];

  return (
    <>
      {isOpen && <div className="fixed inset-0 bg-black bg-opacity-50 z-40" onClick={onClose} />}
      <aside className={`fixed top-0 left-0 h-full bg-white shadow-2xl z-50 transition-transform duration-300 ease-in-out w-64 ${isOpen ? 'translate-x-0' : '-translate-x-full'}`}>
        <div className="flex justify-between items-center p-4 border-b">
          <h2 className="text-lg font-bold text-sky-950">Menu</h2>
          <button onClick={onClose} className="text-gray-500 hover:text-gray-800">
            <FaTimes size={20} />
          </button>
        </div>
        <nav className="py-4">
          {navItems.map((item) => (
            <NavLink key={item.to} to={item.to} onClick={onClose} className={({ isActive }) => `flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-blue-50 ${isActive ? 'bg-blue-100 text-blue-800 border-r-4 border-sky-900' : ''}`}>
              <item.icon size={20} />
              <span>{item.label}</span>
            </NavLink>
          ))}
        </nav>
      </aside>
    </>
  );
}