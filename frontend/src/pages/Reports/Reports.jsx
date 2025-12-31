import React, { useState } from 'react';
import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid,
    Tooltip, ResponsiveContainer, LineChart, Line, Legend
} from 'recharts';

const Reports = () => {
    // Mockup de datos (esto vendría de api/admin/reports/range)
    const dataAsistencia = [
        { name: 'Lun', presentes: 120, atrasos: 5 },
        { name: 'Mar', presentes: 115, atrasos: 10 },
        { name: 'Mie', presentes: 125, atrasos: 2 },
        { name: 'Jue', presentes: 110, atrasos: 15 },
        { name: 'Vie', presentes: 122, atrasos: 4 },
    ];

    return (
        <div className="container-fluid py-4 bg-light min-vh-100">
            <div className="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 className="fw-bold">Reportes y Análisis</h1>
                    <p className="text-muted">Visualiza las tendencias de asistencia y puntualidad</p>
                </div>
                <div className="d-flex gap-2">
                    <input type="date" className="form-control" />
                    <input type="date" className="form-control" />
                    <button className="btn btn-dark rounded-pill px-4">
                        <i className="bi bi-filter me-2"></i> Filtrar
                    </button>
                </div>
            </div>

            <div className="row g-4 mb-4">
                {/* Gráfico de Barras: Asistencia Semanal */}
                <div className="col-lg-8">
                    <div className="card border-0 shadow-sm rounded-4 p-4">
                        <h5 className="fw-bold mb-4">Flujo de Asistencia Semanal</h5>
                        <div style={{ width: '100%', height: 300 }}>
                            <ResponsiveContainer>
                                <BarChart data={dataAsistencia}>
                                    <CartesianGrid strokeDasharray="3 3" vertical={false} />
                                    <XAxis dataKey="name" axisLine={false} tickLine={false} />
                                    <YAxis axisLine={false} tickLine={false} />
                                    <Tooltip />
                                    <Legend />
                                    <Bar dataKey="presentes" fill="#0d6efd" radius={[4, 4, 0, 0]} name="Presentes" />
                                    <Bar dataKey="atrasos" fill="#ffc107" radius={[4, 4, 0, 0]} name="Atrasos" />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    </div>
                </div>

                {/* Top de Grados con más Atrasos */}
                <div className="col-lg-4">
                    <div className="card border-0 shadow-sm rounded-4 p-4 h-100">
                        <h5 className="fw-bold mb-4">Atrasos por Grado</h5>
                        <div className="list-group list-group-flush">
                            {[
                                { grado: '3ro Primaria', cant: 25, color: 'bg-danger' },
                                { grado: '6to Secundaria', cant: 18, color: 'bg-warning' },
                                { grado: '1ro Secundaria', cant: 12, color: 'bg-info' },
                                { grado: '4to Primaria', cant: 5, color: 'bg-success' }
                            ].map((item, idx) => (
                                <div key={idx} className="list-group-item px-0 border-0 mb-3">
                                    <div className="d-flex justify-content-between mb-1">
                                        <span className="small fw-bold">{item.grado}</span>
                                        <span className="small text-muted">{item.cant} casos</span>
                                    </div>
                                    <div className="progress" style={{ height: '8px' }}>
                                        <div className={`progress-bar ${item.color}`} style={{ width: `${(item.cant / 30) * 100}%` }}></div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Tabla Resumen de Reportes Críticos */}
            <div className="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div className="card-header bg-white py-3">
                    <h6 className="fw-bold mb-0">Alertas de Ausentismo (Crónicos)</h6>
                </div>
                <div className="table-responsive">
                    <table className="table align-middle mb-0">
                        <thead className="bg-light">
                            <tr className="small text-muted">
                                <th className="px-4">Estudiante</th>
                                <th>Grado</th>
                                <th>Inasistencias (Mes)</th>
                                <th>Estado</th>
                                <th className="text-end px-4">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td className="px-4 fw-medium">Carlos Vera</td>
                                <td>2do Primaria</td>
                                <td><span className="text-danger fw-bold">8 días</span></td>
                                <td><span className="badge bg-danger-subtle text-danger px-3">Riesgo Alto</span></td>
                                <td className="text-end px-4"><button className="btn btn-sm btn-outline-primary rounded-pill">Ver Historial</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};

export default Reports;