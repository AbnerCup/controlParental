import React from 'react';
import { Outlet } from 'react-router-dom';
import SideBar from '../Sidebar/Sidebar';

const MainLayout = ({ user, handleLogout }) => {
    return (
        <div className="d-flex" style={{ minHeight: '100vh', backgroundColor: '#f8f9fa' }}>
            {/* SideBar Lateral */}
            <SideBar user={user} onLogout={handleLogout} />

            {/* Área de Contenido */}
            <div className="flex-grow-1 d-flex flex-column" style={{ height: '100vh', overflowY: 'auto' }}>
                {/* Aquí puedes mantener tu Navbar superior si quieres, o eliminarlo si el SideBar ya tiene los datos */}
                <header className="bg-white shadow-sm p-3 d-flex justify-content-between align-items-center">
                    <h4 className="mb-0 text-primary fw-bold">SchoolTrack</h4>
                    <div className="text-muted small">Bienvenido, {user?.name}</div>
                </header>

                <main className="p-4">
                    <Outlet /> {/* Aquí se renderizará el Dashboard */}
                </main>

                <footer className="mt-auto py-3 text-center text-muted border-top bg-white">
                    <small>SchoolTrack v1.0 | {user?.email}</small>
                </footer>
            </div>
        </div>
    );
};

export default MainLayout;