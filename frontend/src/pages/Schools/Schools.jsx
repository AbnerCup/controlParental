import React, { useState } from 'react';

const Schools = () => {
    // Mockup basado en tu tabla api_clients y schools
    const [schools, setSchools] = useState([
        {
            id: 1,
            name: "Colegio Nacional Simón Bolívar",
            code: "SB-2024",
            city: "Oruro",
            status: "active",
            gateway_status: "online",
            api_key: "KID-992837",
            students_count: 450
        },
        {
            id: 2,
            name: "Unidad Educativa Santa Ana",
            code: "SA-2024",
            city: "La Paz",
            status: "active",
            gateway_status: "offline",
            api_key: "KID-112233",
            students_count: 320
        }
    ]);

    return (
        <div className="container-fluid py-4 bg-light min-vh-100">
            <div className="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 className="fw-bold text-dark">Unidades Educativas</h1>
                    <p className="text-muted">Administración de escuelas y configuración de Gateways HMAC</p>
                </div>
                <button className="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i className="bi bi-plus-lg me-2"></i> Registrar Escuela
                </button>
            </div>

            <div className="row g-4">
                {schools.map((school) => (
                    <div className="col-xl-6" key={school.id}>
                        <div className="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <div className="card-body p-4">
                                <div className="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 className="fw-bold mb-1">{school.name}</h5>
                                        <span className="badge bg-light text-secondary border">Código: {school.code}</span>
                                    </div>
                                    <span className={`badge rounded-pill px-3 py-2 ${school.gateway_status === 'online' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'}`}>
                                        <i className={`bi bi-circle-fill me-2 small ${school.gateway_status === 'online' ? 'text-success' : 'text-danger'}`}></i>
                                        Gateway {school.gateway_status.toUpperCase()}
                                    </span>
                                </div>

                                <div className="row g-2 my-3">
                                    <div className="col-6">
                                        <div className="p-3 bg-light rounded-3">
                                            <small className="text-muted d-block">Estudiantes</small>
                                            <span className="fw-bold">{school.students_count}</span>
                                        </div>
                                    </div>
                                    <div className="col-6">
                                        <div className="p-3 bg-light rounded-3">
                                            <small className="text-muted d-block">Ubicación</small>
                                            <span className="fw-bold">{school.city}</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Sección de Seguridad HMAC (api_clients) */}
                                <div className="bg-dark bg-opacity-10 p-3 rounded-4 border border-dashed border-secondary">
                                    <div className="d-flex justify-content-between align-items-center mb-2">
                                        <small className="fw-bold text-uppercase text-muted" style={{ fontSize: '10px' }}>Credenciales de Seguridad (HMAC)</small>
                                        <button className="btn btn-link btn-sm p-0 text-decoration-none" title="Regenerar Secreto">
                                            <i className="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </div>
                                    <div className="d-flex align-items-center justify-content-between">
                                        <div className="d-flex align-items-center">
                                            <i className="bi bi-key-fill text-primary me-2"></i>
                                            <code className="text-primary small">{school.api_key}</code>
                                        </div>
                                        <button className="btn btn-sm btn-outline-dark px-3 rounded-pill">Gestionar Llaves</button>
                                    </div>
                                </div>

                                <div className="d-flex gap-2 mt-4">
                                    <button className="btn btn-light rounded-pill flex-grow-1">
                                        <i className="bi bi-gear me-2"></i> Configurar
                                    </button>
                                    <button className="btn btn-outline-primary rounded-pill flex-grow-1">
                                        <i className="bi bi-people me-2"></i> Ver Usuarios
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default Schools;