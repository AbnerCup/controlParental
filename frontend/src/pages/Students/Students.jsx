import React, { useState } from 'react';

const Students = () => {
    // Mockup basado en tu modelo: students + device_assignments
    const [students, setStudents] = useState([
        { id: 1, name: "Mateo García", grade: "3ro Primaria", student_code: "ST-001", device_uid: "A1-B2-C3", status: "Active" },
        { id: 2, name: "Sofía López", grade: "2do Primaria", student_code: "ST-002", device_uid: null, status: "No Device" },
        { id: 3, name: "Lucas Rojas", grade: "4to Primaria", student_code: "ST-003", device_uid: "D4-E5-F6", status: "Active" },
    ]);

    const [showAssignModal, setShowAssignModal] = useState(false);
    const [selectedStudent, setSelectedStudent] = useState(null);
    const [newDeviceUid, setNewDeviceUid] = useState("");

    const openAssignModal = (student) => {
        setSelectedStudent(student);
        setShowAssignModal(true);
    };

    const handleAssignDevice = () => {
        setStudents(students.map(s =>
            s.id === selectedStudent.id ? { ...s, device_uid: newDeviceUid, status: 'Active' } : s
        ));
        // Aquí iría el POST a api/gateway/device-events o la ruta de asignación
        setShowAssignModal(false);
        setNewDeviceUid("");
    };

    return (
        <div className="container-fluid py-4 bg-light min-vh-100">
            <div className="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 className="fw-bold">Gestión de Estudiantes</h1>
                    <p className="text-muted">Asigna dispositivos y monitorea el estado de vinculación</p>
                </div>
                <button className="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i className="bi bi-person-plus me-2"></i> Nuevo Estudiante
                </button>
            </div>

            <div className="card border-0 shadow-sm rounded-4">
                <div className="card-body p-0">
                    <div className="p-3 border-bottom d-flex gap-3">
                        <div className="input-group" style={{ maxWidth: '300px' }}>
                            <span className="input-group-text bg-white border-end-0"><i className="bi bi-search"></i></span>
                            <input type="text" className="form-control border-start-0" placeholder="Buscar por nombre o código..." />
                        </div>
                        <select className="form-select w-auto">
                            <option>Todos los Grados</option>
                            <option>1ro Primaria</option>
                            <option>2do Primaria</option>
                        </select>
                    </div>

                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead className="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th className="px-4 py-3">Estudiante</th>
                                    <th>Código</th>
                                    <th>Grado</th>
                                    <th>ID Dispositivo (UID)</th>
                                    <th>Estado</th>
                                    <th className="text-end px-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {students.map((s) => (
                                    <tr key={s.id}>
                                        <td className="px-4">
                                            <div className="d-flex align-items-center">
                                                <div className="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3 fw-bold" style={{ width: '40px', height: '40px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                    {s.name.charAt(0)}
                                                </div>
                                                <span className="fw-medium">{s.name}</span>
                                            </div>
                                        </td>
                                        <td><code className="text-dark">{s.student_code}</code></td>
                                        <td>{s.grade}</td>
                                        <td>
                                            {s.device_uid ? (
                                                <span className="badge bg-light text-dark border"><i className="bi bi-cpu me-1"></i> {s.device_uid}</span>
                                            ) : (
                                                <span className="text-muted small italic">No vinculado</span>
                                            )}
                                        </td>
                                        <td>
                                            <span className={`badge rounded-pill d-inline-flex align-items-center px-3 py-2 ${s.status === 'Active' ? 'bg-success-subtle text-success border border-success' : 'bg-secondary-subtle text-secondary border border-secondary'}`}>
                                                <div className={`rounded-circle me-2 ${s.status === 'Active' ? 'bg-success' : 'bg-secondary'}`} style={{ width: '8px', height: '8px' }}></div>
                                                {s.status}
                                            </span>
                                        </td>
                                        <td className="text-end px-4">
                                            <button
                                                className={`btn btn-sm rounded-pill px-3 ${s.device_uid ? 'btn-outline-secondary' : 'btn-primary'}`}
                                                onClick={() => openAssignModal(s)}
                                            >
                                                {s.device_uid ? 'Reasignar' : 'Vincular Tag'}
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* MODAL DE VINCULACIÓN */}
            {showAssignModal && (
                <div className="modal d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg rounded-4">
                            <div className="modal-header border-0 pb-0">
                                <h5 className="modal-title fw-bold">Vincular Dispositivo</h5>
                                <button type="button" className="btn-close" onClick={() => setShowAssignModal(false)}></button>
                            </div>
                            <div className="modal-body p-4">
                                <div className="text-center mb-4">
                                    <div className="display-4 text-primary mb-2"><i className="bi bi-broadcast"></i></div>
                                    <h6>Escanea el dispositivo para {selectedStudent?.name}</h6>
                                    <p className="text-muted small">Acerca el tag al lector o ingresa el UID manualmente</p>
                                </div>
                                <div className="form-group">
                                    <label className="form-label small fw-bold">UID del Dispositivo</label>
                                    <input
                                        type="text"
                                        className="form-control form-control-lg text-center fw-mono"
                                        placeholder="XX-XX-XX-XX"
                                        value={newDeviceUid}
                                        onChange={(e) => setNewDeviceUid(e.target.value.toUpperCase())}
                                        autoFocus
                                    />
                                </div>
                            </div>
                            <div className="modal-footer border-0">
                                <button className="btn btn-light rounded-pill px-4" onClick={() => setShowAssignModal(false)}>Cancelar</button>
                                <button className="btn btn-primary rounded-pill px-4" onClick={handleAssignDevice}>Guardar Vínculo</button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default Students;