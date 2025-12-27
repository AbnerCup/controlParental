import React, { useState, useEffect } from 'react';
import { apiService } from '../../services/apiService';
import './Dashboard.css';

const Dashboard = () => {
    const [stats, setStats] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        loadRealData();
    }, []);

    const loadRealData = async () => {
        try {
            setLoading(true);
            setError('');
            const response = await apiService.getDashboardStats();
            setStats(response.data.stats || response.data);
        } catch (err) {
            console.error('Error cargando datos reales:', err);

            if (err.response?.status === 401) {
                setError('Error 401: No autenticado. ¿Tienes token?');
            } else if (err.response?.status === 404) {
                setError('Error 404: Endpoint no encontrado');
            } else if (!err.response) {
                setError('No hay conexión con el backend. ¿Está corriendo Laravel?');
            } else {
                setError(`Error ${err.response.status}: ${err.response.statusText}`);
            }
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
            <div className="dashboard-header mb-4 p-4 rounded-4 shadow-sm text-white">
                <div className="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 className="fw-bold mb-1">Dashboard</h1>
                        <p className="mb-0 opacity-75">
                            Monitoreo en tiempo real
                        </p>
                    </div>
                </div>
            </div>
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
        </div>
    );
};

export default Dashboard;