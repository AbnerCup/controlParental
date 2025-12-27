// src/components/Dashboard.jsx - MODIFÍCALO
import React, { useState, useEffect } from 'react';
import { apiService } from '../../services/apiService';

const Dashboard = () => {
    const [stats, setStats] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    // 1. CARGAR DATOS REALES DEL BACKEND
    useEffect(() => {
        loadRealData();
    }, []);

    const loadRealData = async () => {
        try {
            setLoading(true);
            setError('');

            // Llamada REAL a tu API
            const response = await apiService.getDashboardStats();

            console.log('Datos reales recibidos:', response.data);
            setStats(response.data.stats || response.data);

        } catch (err) {
            console.error('Error cargando datos reales:', err);

            // Mostrar error específico
            if (err.response?.status === 401) {
                setError('Error 401: No autenticado. ¿Tienes token?');
            } else if (err.response?.status === 404) {
                setError('Error 404: Endpoint no encontrado');
            } else if (!err.response) {
                setError('No hay conexión con el backend. ¿Está corriendo Laravel?');
            } else {
                setError(`Error ${err.response.status}: ${err.response.statusText}`);
            }

            // Datos de respaldo (opcional)
            setStats({
                total_students: 0,
                present_today: 0,
                absent_today: 0,
                late_today: 0,
                school_name: 'Error de conexión'
            });
        } finally {
            setLoading(false);
        }
    };

    // 2. FUNCIÓN PARA MARCAR ASISTENCIA MANUAL
    const markAttendance = async (studentId, status) => {
        try {
            await apiService.markAttendance({
                student_id: studentId,
                status: status,
                date: new Date().toISOString().split('T')[0]
            });
            alert('Asistencia registrada exitosamente');
            loadRealData(); // Recargar datos
        } catch (err) {
            alert('Error registrando asistencia: ' + err.message);
        }
    };

    // 3. FUNCIÓN PARA REPORTES
    const generateReport = async (type) => {
        try {
            let report;
            if (type === 'daily') {
                report = await apiService.getDailyReport(new Date().toISOString().split('T')[0]);
            } else if (type === 'absences') {
                report = await apiService.getAbsenceReport();
            }
            console.log('Reporte generado:', report.data);
            alert('Reporte generado (ver consola)');
        } catch (err) {
            alert('Error generando reporte');
        }
    };

    if (loading) {
        return (
            <div className="text-center py-5">
                <div className="spinner-border text-primary" role="status">
                    <span className="visually-hidden">Cargando datos reales...</span>
                </div>
                <p className="mt-2">Conectando con backend Laravel...</p>
            </div>
        );
    }

    return (
        <div className="container-fluid py-4">
            {/* Header */}
            <div className="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 className="h3 mb-0">Dashboard REAL</h1>
                    <p className="text-muted mb-0">Datos en vivo desde Laravel</p>
                </div>
                <div>
                    <button className="btn btn-primary me-2" onClick={loadRealData}>
                        <i className="bi bi-arrow-clockwise"></i> Actualizar
                    </button>
                    <button className="btn btn-success" onClick={() => generateReport('daily')}>
                        <i className="bi bi-file-earmark-text"></i> Reporte
                    </button>
                </div>
            </div>

            {/* Error Message */}
            {error && (
                <div className="alert alert-danger">
                    <strong>Error de conexión:</strong> {error}
                    <div className="mt-2">
                        <small>
                            Verifica:
                            1) Laravel corriendo (php artisan serve)
                            2) Endpoint: http://localhost:8000/api/admin/dashboard
                            3) Autenticación si es requerida
                        </small>
                    </div>
                </div>
            )}

            {/* Stats Cards */}
            <div className="row g-3 mb-4">
                {stats && (
                    <>
                        <div className="col-md-3 col-6">
                            <div className="card border-start border-primary border-4">
                                <div className="card-body">
                                    <h6 className="text-muted">Total Estudiantes</h6>
                                    <h3>{stats.total_students || 0}</h3>
                                    <small className="text-muted">{stats.school_name || 'Escuela'}</small>
                                </div>
                            </div>
                        </div>

                        <div className="col-md-3 col-6">
                            <div className="card border-start border-success border-4">
                                <div className="card-body">
                                    <h6 className="text-muted">Presentes Hoy</h6>
                                    <h3>{stats.present_today || 0}</h3>
                                    <small className="text-success">
                                        {stats.total_students ?
                                            Math.round((stats.present_today / stats.total_students) * 100) + '%' :
                                            '0%'}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div className="col-md-3 col-6">
                            <div className="card border-start border-danger border-4">
                                <div className="card-body">
                                    <h6 className="text-muted">Ausentes Hoy</h6>
                                    <h3>{stats.absent_today || 0}</h3>
                                    <small className="text-danger">
                                        {stats.total_students ?
                                            Math.round((stats.absent_today / stats.total_students) * 100) + '%' :
                                            '0%'}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div className="col-md-3 col-6">
                            <div className="card border-start border-warning border-4">
                                <div className="card-body">
                                    <h6 className="text-muted">Tardíos Hoy</h6>
                                    <h3>{stats.late_today || 0}</h3>
                                    <small className="text-warning">
                                        {stats.total_students ?
                                            Math.round((stats.late_today / stats.total_students) * 100) + '%' :
                                            '0%'}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </>
                )}
            </div>

            {/* Acciones rápidas con endpoints reales */}
            <div className="row mb-4">
                <div className="col-md-4">
                    <div className="card">
                        <div className="card-body text-center">
                            <h5>Endpoint /admin/dashboard</h5>
                            <p className="text-muted">GET: Estadísticas diarias</p>
                            <code className="d-block text-truncate">
                                http://localhost:8000/api/admin/dashboard
                            </code>
                        </div>
                    </div>
                </div>

                <div className="col-md-4">
                    <div className="card">
                        <div className="card-body text-center">
                            <h5>Endpoint /admin/attendance</h5>
                            <p className="text-muted">GET: Lista asistencias</p>
                            <button
                                className="btn btn-outline-primary mt-2"
                                onClick={async () => {
                                    try {
                                        const res = await apiService.getAttendance({ date: new Date().toISOString().split('T')[0] });
                                        console.log('Asistencias:', res.data);
                                        alert(`Total registros: ${res.data.length || 0}`);
                                    } catch (err) {
                                        alert('Error: ' + err.message);
                                    }
                                }}
                            >
                                Probar Endpoint
                            </button>
                        </div>
                    </div>
                </div>

                <div className="col-md-4">
                    <div className="card">
                        <div className="card-body text-center">
                            <h5>Endpoint /panic/trigger</h5>
                            <p className="text-muted">POST: Disparar alarma</p>
                            <button
                                className="btn btn-outline-danger mt-2"
                                onClick={async () => {
                                    if (confirm('¿Disparar evento de pánico de prueba?')) {
                                        try {
                                            await apiService.triggerPanic({
                                                device_id: 'test-device-001',
                                                student_id: 1,
                                                location: 'Aula 101'
                                            });
                                            alert('Alarma activada');
                                        } catch (err) {
                                            alert('Error activando alarma');
                                        }
                                    }
                                }}
                            >
                                <i className="bi bi-exclamation-triangle"></i> Prueba Pánico
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Info de conexión */}
            <div className="alert alert-info">
                <h5><i className="bi bi-plug me-2"></i>Estado de conexión</h5>
                <p>
                    Frontend React: <span className="badge bg-success">Conectado</span><br />
                    Backend Laravel: <span className={error ? 'badge bg-danger' : 'badge bg-success'}>
                        {error ? 'Desconectado' : 'Conectado'}
                    </span><br />
                    Puerto Frontend: 5173 | Puerto Backend: 8000
                </p>
            </div>
        </div>
    );
};

export default Dashboard;