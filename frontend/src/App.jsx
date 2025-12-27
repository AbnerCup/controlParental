// App.jsx - VERSIÓN CORREGIDA (usa esto)
import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Login from './pages/Login/Login';
import Dashboard from './pages/Dashboard/Dashboard';
import './App.css';

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

  if (loading) {
    return (
      <div className="d-flex justify-content-center align-items-center min-vh-100">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Cargando...</span>
        </div>
      </div>
    );
  }

  return (
    <Router>
      <div className="App">
        {isAuthenticated && (
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
                <button className="btn btn-outline-light btn-sm" onClick={handleLogout}>
                  <i className="bi bi-box-arrow-right me-1"></i>
                  Salir
                </button>
              </div>
            </div>
          </nav>
        )}

        <Routes>
          <Route
            path="/login"
            element={
              !isAuthenticated ?
                <Login onLogin={handleLogin} /> :
                <Navigate to="/dashboard" replace />
            }
          />
          <Route
            path="/dashboard"
            element={
              isAuthenticated ?
                <Dashboard user={user} /> :
                <Navigate to="/login" replace />
            }
          />

          <Route
            path="/"
            element={
              <Navigate to={isAuthenticated ? "/dashboard" : "/login"} replace />
            }
          />
        </Routes>

        {isAuthenticated && (
          <footer className="mt-5 py-3 text-center text-muted border-top">
            <small>
              <i className="bi bi-code-slash me-1"></i>
              SchoolTrack v1.0 | Usuario: {user?.email || 'demo'}
            </small>
          </footer>
        )}
      </div>
    </Router>
  );
}

export default App;