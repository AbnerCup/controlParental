// src/App.jsx - REEMPLAZA TODO
import React, { useState, useEffect } from 'react';
import Login from './components/Login/Login';
import Dashboard from './components/Dashboard/Dashboard';
import './App.css';

function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [user, setUser] = useState(null);

  // Verificar si ya está logueado al cargar
  useEffect(() => {
    const token = localStorage.getItem('token');
    const savedUser = localStorage.getItem('user');

    if (token && savedUser) {
      setUser(JSON.parse(savedUser));
      setIsAuthenticated(true);
    }
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
  };

  if (!isAuthenticated) {
    return <Login onLogin={handleLogin} />;
  }

  return (
    <div className="App">
      {/* Navbar */}
      <nav className="navbar navbar-dark bg-primary shadow-sm">
        <div className="container-fluid">
          <div className="d-flex align-items-center">
            <i className="bi bi-shield-check text-white fs-4 me-2"></i>
            <span className="navbar-brand mb-0">SchoolTrack</span>
          </div>

          <div className="d-flex align-items-center">
            <div className="text-white me-3">
              <i className="bi bi-person-circle me-1"></i>
              {user?.name || 'Administrador'}
            </div>
            <button
              className="btn btn-outline-light btn-sm"
              onClick={handleLogout}
            >
              <i className="bi bi-box-arrow-right me-1"></i>
              Salir
            </button>
          </div>
        </div>
      </nav>

      {/* Dashboard */}
      <Dashboard />

      {/* Footer */}
      <footer className="mt-5 py-3 text-center text-muted border-top">
        <small>
          <i className="bi bi-code-slash me-1"></i>
          SchoolTrack v1.0 | Usuario: {user?.email || 'demo'}
        </small>
      </footer>
    </div>
  );
}

export default App;