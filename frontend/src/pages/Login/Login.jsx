// src/components/Login.jsx - MODIFÍCALO
import React, { useState } from 'react';
import api from '../../config/axios';
import './Login.css';
import { useNavigate } from 'react-router-dom';

const Login = ({ onLogin }) => {
    const navigate = useNavigate();
    const [credentials, setCredentials] = useState({
        email: '',
        password: ''
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const handleSubmit = async (e) => {

        e.preventDefault();
        setLoading(true);
        setError('');

        try {
            const response = await api.post('/login', credentials);

            if (response.data.success) {
                localStorage.setItem('token', response.data.token);
                localStorage.setItem('user', JSON.stringify(response.data.user));

                onLogin(response.data.user);

                navigate('/dashboard');
            }
        } catch (err) {
            console.error(err);
            setError('Error al iniciar sesión');
        }
        finally {
            setLoading(false);
        }
    };

    return (
        <div className="login-card">
            <div className="login-left">
                <div className="lock-box">
                    <div className="lock-icon">🔐</div>
                    <h2 style={{ marginBottom: '20px', fontSize: '40px' }}>Control Parental</h2>
                    <p style={{ opacity: 0.8, textAlign: 'center', lineHeight: 1.6 }}>
                        Sistema de gestión escolar
                        <br />
                        Panel Administrativo
                    </p>
                </div>
            </div>

            <div className="login-right">
                <h3>Iniciar Sesión</h3>
                <p>Ingresa tus credenciales para acceder al sistema</p>

                {error && (
                    <div className="error-message">
                        {error}
                        <button type="button" onClick={() => setError('')}>×</button>
                    </div>
                )}

                <form onSubmit={handleSubmit}>
                    <input
                        className="input dark"
                        placeholder="Correo electrónico"
                        type="email"
                        value={credentials.email}
                        onChange={(e) => setCredentials({ ...credentials, email: e.target.value })}
                        required
                        disabled={loading}
                    />

                    <input
                        className="input dark"
                        placeholder="Contraseña"
                        type="password"
                        value={credentials.password}
                        onChange={(e) => setCredentials({ ...credentials, password: e.target.value })}
                        required
                        disabled={loading}
                    />

                    <div className="row">
                        <label>
                            <input type="checkbox" /> Recordarme
                        </label>
                        <a href="#" onClick={(e) => { e.preventDefault(); alert('Contacta al administrador'); }}>
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <button
                        className={`btn-login ${loading ? 'loading' : ''}`}
                        type="submit"
                        disabled={loading}
                    >
                        {loading ? '' : 'INICIAR SESIÓN'}
                    </button>
                </form>


                <p className="register">
                    ¿No tienes una cuenta? <span onClick={() => alert('Contacta al administrador para registrarte')}>Solicitar acceso</span>
                </p>
            </div>
        </div>
    );
};

export default Login;