// App.jsx - VERSIÓN CORREGIDA (usa esto)
import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Login from './pages/Login/Login';
import Dashboard from './pages/Dashboard/Dashboard';
import './App.css';
import MainLayout from './components/MainLayout/MainLayout';
import PanicRoom from './pages/PanicRoom/PanicRoom';
import Students from './pages/Students/Students';
import Attendance from './pages/Attendance/Attendance';
import Reports from './pages/Reports/Reports';
import Schools from './pages/Schools/Schools';
import ProtectedRoute from './components/ProtectedRoute/ProtectedRoute';

function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('token');
    const savedUser = localStorage.getItem('user');

    if (token && savedUser) {
      setUser(JSON.parse(savedUser));
      setIsAuthenticated(true);
    }
    setLoading(false);
  }, []);

  const handleLogin = (userData) => {
    setUser(userData);
    setIsAuthenticated(true);
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    setUser(null);
    setIsAuthenticated(false);
    window.location.href = '/login';
  };

  if (loading) return <div className="spinner-border"></div>;

  return (
    <Router>
      <div className="App">
        <Routes>
          {/* RUTA PÚBLICA */}
          <Route path="/login" element={
            !isAuthenticated ? <Login onLogin={handleLogin} /> : <Navigate to="/dashboard" replace />
          } />

          {/* RUTAS PROTEGIDAS CON SIDEBAR */}
          {isAuthenticated ? (
            <Route element={<MainLayout user={user} handleLogout={handleLogout} />}>

              {/* 1. Rutas Accesibles por TODOS los autenticados */}
              <Route path="/dashboard" element={<Dashboard user={user} />} />

              {/* 2. Rutas solo para ADMIN, SCHOOL_ADMIN y OPERATOR */}
              <Route element={
                <ProtectedRoute isAllowed={['admin', 'school_admin', 'operator'].includes(user?.primary_role?.toLowerCase())} redirectTo="/dashboard" />
              }>
                <Route path="/panicRoom" element={<PanicRoom />} />
                <Route path="/students" element={<Students />} />
                <Route path="/attendance" element={<Attendance />} />
              </Route>

              {/* 3. Rutas solo para ADMIN y SCHOOL_ADMIN (Reportes) */}
              <Route element={
                <ProtectedRoute isAllowed={['admin', 'school_admin'].includes(user?.primary_role?.toLowerCase())} redirectTo="/dashboard" />
              }>
                <Route path="/reports" element={<Reports />} />
              </Route>

              {/* 4. Rutas EXCLUSIVAS del SuperAdmin (ADMIN GLOBAL) */}
              <Route element={
                <ProtectedRoute isAllowed={user?.primary_role?.toLowerCase() === 'admin'} redirectTo="/dashboard" />
              }>
                <Route path="/schools" element={<Schools />} />
                <Route path="/gateways" element={<div>Página de Gateways</div>} />
              </Route>

            </Route>
          ) : (
            <Route path="*" element={<Navigate to="/login" replace />} />
          )}

          <Route path="/" element={<Navigate to={isAuthenticated ? "/dashboard" : "/login"} replace />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App;