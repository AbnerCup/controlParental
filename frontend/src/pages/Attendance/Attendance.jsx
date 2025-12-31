import React, { useState } from 'react';

const Attendance = () => {
    // Mockup basado en tu modelo de datos 'attendances'
    const [records, setRecords] = useState([
        { id: 1, student: "Mateo García", grade: "3ro Primaria", check_in: "07:45 AM", check_out: "02:30 PM", status: "present", method: "device_scan" },
        { id: 2, student: "Sofía López", grade: "2do Primaria", check_in: "08:15 AM", check_out: null, status: "late", method: "device_scan" },
        { id: 3, student: "Lucas Rojas", grade: "4to Primaria", check_in: null, check_out: null, status: "absent", method: null },
        { id: 4, student: "Ana Belén", grade: "1ro Primaria", check_in: "08:00 AM", check_out: null, status: "present", method: "manual" },
    ]);

    const getStatusStyle = (status) => {
        const styles = {
            present: "bg-success-subtle text-success border-success",
            late: "bg-warning-subtle text-warning-emphasis border-warning",
            absent: "bg-danger-subtle text-danger border-danger",
            left_early: "bg-info-subtle text-info-emphasis border-info"
        };
        return `badge rounded-pill border d-inline-flex align-items-center px-3 py-2 ${styles[status]}`;
    };

    return (
        <div className="container-fluid py-4 bg-light min-vh-100">
            {/* Header con Filtros Rápidos */}
            <div className="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 className="fw-bold">Control de Asistencia</h1>
                    <p className="text-muted">Registro diario local: {new Date().toLocaleDateString()}</p>
                </div>
                <div className="d-flex gap-2">
                    <button className="btn btn-outline-primary rounded-pill px-4">
                        <i className="bi bi-download me-2"></i> Exportar
                    </button>
                    <button className="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i className="bi bi-plus-circle me-2"></i> Registro Manual
                    </button>
                </div>
            </div>

            {/* Resumen de hoy */}
            <div className="row g-3 mb-4 text-center">
                <div className="col-md-3">
                    <div className="card border-0 shadow-sm p-3 rounded-4">
                        <h6 className="text-muted small mb-1">Presentes</h6>
                        <h3 className="fw-bold text-success mb-0">124</h3>
                    </div>
                </div>
                <div className="col-md-3">
                    <div className="card border-0 shadow-sm p-3 rounded-4">
                        <h6 className="text-muted small mb-1">Atrasos</h6>
                        <h3 className="fw-bold text-warning mb-0">12</h3>
                    </div>
                </div>
                <div className="col-md-3">
                    <div className="card border-0 shadow-sm p-3 rounded-4">
                        <h6 className="text-muted small mb-1">Ausentes</h6>
                        <h3 className="fw-bold text-danger mb-0">5</h3>
                    </div>
                </div>
                <div className="col-md-3">
                    <div className="card border-0 shadow-sm p-3 rounded-4 bg-primary text-white">
                        <h6 className="small mb-1 opacity-75">Asistencia Total</h6>
                        <h3 className="fw-bold mb-0">91%</h3>
                    </div>
                </div>
            </div>

            {/* Tabla de Asistencia */}
            <div className="card border-0 shadow-sm rounded-4">
                <div className="card-body p-0">
                    <div className="p-3 border-bottom d-flex flex-wrap gap-2 align-items-center">
                        <input type="date" className="form-control w-auto" defaultValue={new Date().toISOString().split('T')[0]} />
                        <select className="form-select w-auto">
                            <option>Todos los Grados</option>
                            <option>1ro Primaria</option>
                            <option>2do Primaria</option>
                        </select>
                        <div className="ms-md-auto d-flex gap-2">
                            <span className="badge bg-light text-dark border d-flex align-items-center px-3">
                                <i className="bi bi-cpu me-2 text-primary"></i> Lector Online
                            </span>
                        </div>
                    </div>

                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead className="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th className="px-4 py-3">Estudiante</th>
                                    <th>Grado</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                    <th>Estado</th>
                                    <th>Método</th>
                                    <th className="text-end px-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {records.map((r) => (
                                    <tr key={r.id}>
                                        <td className="px-4 fw-medium">{r.student}</td>
                                        <td className="text-muted small">{r.grade}</td>
                                        <td className="fw-bold text-dark">{r.check_in || '--:--'}</td>
                                        <td className="text-muted">{r.check_out || '--:--'}</td>
                                        <td>
                                            <span className={getStatusStyle(r.status)}>
                                                {r.status === 'present' && <i className="bi bi-check2-circle me-1"></i>}
                                                {r.status === 'late' && <i className="bi bi-clock-history me-1"></i>}
                                                {r.status === 'absent' && <i className="bi bi-x-circle me-1"></i>}
                                                {r.status.toUpperCase()}
                                            </span>
                                        </td>
                                        <td>
                                            <small className="text-muted">
                                                {r.method === 'device_scan' ? <i className="bi bi-phone-vibrate me-1"></i> : <i className="bi bi-keyboard me-1"></i>}
                                                {r.method === 'device_scan' ? 'Tag/NFC' : 'Manual'}
                                            </small>
                                        </td>
                                        <td className="text-end px-4">
                                            <button className="btn btn-light btn-sm rounded-circle"><i className="bi bi-three-dots-vertical"></i></button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Attendance;