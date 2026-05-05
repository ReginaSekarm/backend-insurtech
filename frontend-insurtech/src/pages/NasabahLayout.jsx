import { Outlet } from 'react-router-dom';
import Navbar from '../pages/Navbar';

export default function NasabahLayout() {
  return (
    <div className="min-h-screen bg-gray-100">
      <Navbar />
      <main className="p-6 ml-0 md:ml-0 transition-all">
        <Outlet />
      </main>
    </div>
  );
}