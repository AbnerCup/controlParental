import React, { useState } from 'react';

const PanicRoom = () => {
    // Estado para los eventos (Simulando lo que vendría de api/panic/events)
    const [events, setEvents] = useState([
        { id: 1, student_name: "Mateo García", status: "open", source: "device", occurred_at: "2025-12-30 10:30:00", guardian_phone: "+591 70000000" },
        { id: 2, student_name: "Sofía López", status: "acknowledged", source: "app", occurred_at: "2025-12-30 09:15:00", guardian_phone: "+591 71111111" }
    ]);

    // Estados para el Modal
    const [selectedEvent, setSelectedEvent] = useState(null);
    const [resolutionNote, setResolutionNote] = useState("");

    // Función para cambiar estado (Simula POST api/panic/events/{id}/resolve)
    const handleResolve = () => {
        if (!resolutionNote.trim()) return alert("Por favor, ingrese una nota de resolución.");

        setEvents(events.map(ev =>
            ev.id === selectedEvent.id ? { ...ev, status: 'resolved' } : ev
        ));

        // Aquí iría tu llamada a la API:
        // axios.post(`/api/panic/events/${selectedEvent.id}/resolve`, { note: resolutionNote })

        console.log(`Evento ${selectedEvent.id} resuelto con nota: ${resolutionNote}`);
        setSelectedEvent(null);
        setResolutionNote("");
    };

    const getStatusBadge = (status) => {
        const configs = {
            open: "bg-danger animate-pulse text-white",
            acknowledged: "bg-warning text-dark",
            resolved: "bg-success text-white"
        };

        return `badge rounded-pill d-inline-flex align-items-center justify-content-center px-3 py-2 ${configs[status] || 'bg-secondary'}`;
    };

    return (
        <div className="container-fluid py-4 bg-light min-vh-100">
            {/* HEADER */}
            <div className="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 className="fw-bold text-danger">
                        <i className="bi bi-exclamation-octagon-fill me-2"></i>
                        Central de Emergencias
                    </h1>
                    <p className="text-muted">Gestión de alertas de pánico y protocolos de seguridad</p>
                </div>
            </div>

            <div className="row g-4">
                {/* LISTA DE ALERTAS */}
                <div className="col-lg-8">
                    {events.filter(e => e.status !== 'resolved').length === 0 ? (
                        <div className="text-center p-5 bg-white rounded-4 shadow-sm">
                            <i className="bi bi-shield-check text-success display-1"></i>
                            <h3 className="mt-3">Sin alertas activas</h3>
                            <p className="text-muted">Todo está bajo control en las unidades educativas.</p>
                        </div>
                    ) : (
                        events.filter(e => e.status !== 'resolved').map(event => (
                            <div key={event.id} className={`card mb-3 border-0 shadow-sm border-start border-5 ${event.status === 'open' ? 'border-danger' : 'border-warning'}`}>
                                <div className="card-body p-4">
                                    <div className="d-flex justify-content-between">
                                        <div className="d-flex align-items-start">
                                            <div className={`p-3 rounded-circle me-3 ${event.status === 'open' ? 'bg-danger text-white' : 'bg-warning'}`}>
                                                <i className="bi bi-megaphone-fill fs-4"></i>
                                            </div>
                                            <div>
                                                <h4 className="fw-bold mb-1">{event.student_name}</h4>
                                                <div className="small text-muted">
                                                    <i className="bi bi-clock me-1"></i> {event.occurred_at} | Origen: {event.source}
                                                </div>
                                            </div>
                                        </div>
                                        <span className={getStatusBadge(event.status)}>{event.status.toUpperCase()}</span>
                                    </div>

                                    <hr />

                                    <div className="d-flex justify-content-end gap-2">
                                        <button className="btn btn-outline-dark rounded-pill px-4" onClick={() => window.open(`tel:${event.guardian_phone}`)}>
                                            <i className="bi bi-telephone me-2"></i> Llamar
                                        </button>
                                        <button
                                            className="btn btn-success rounded-pill px-4 fw-bold"
                                            onClick={() => setSelectedEvent(event)}
                                        >
                                            <i className="bi bi-check-lg me-2"></i> Resolver
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))
                    )}
                </div>

                {/* SIDEBAR DE CONTROL */}
                <div className="col-lg-4">
                    <div className="card border-0 shadow-sm bg-dark text-white rounded-4 mb-4">
                        <div className="card-body p-4 text-center">
                            <h6 className="opacity-75">Alertas de Hoy</h6>
                            <h2 className="display-4 fw-bold mb-0">{events.length}</h2>
                        </div>
                    </div>
                </div>
            </div>

            {/* MODAL DE RESOLUCIÓN (BOOTSTRAP STYLE) */}
            {selectedEvent && (
                <div className="modal d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg rounded-4">
                            <div className="modal-header bg-success text-white">
                                <h5 className="modal-title">Resolver Incidente: {selectedEvent.student_name}</h5>
                                <button type="button" className="btn-close btn-close-white" onClick={() => setSelectedEvent(null)}></button>
                            </div>
                            <div className="modal-body p-4">
                                <label className="form-label fw-bold">Notas de resolución (Panic Event Action)</label>
                                <textarea
                                    className="form-control"
                                    rows="4"
                                    placeholder="Describa las acciones tomadas..."
                                    value={resolutionNote}
                                    onChange={(e) => setResolutionNote(e.target.value)}
                                ></textarea>
                                <small className="text-muted mt-2 d-block italic">
                                    Esta nota quedará registrada permanentemente para auditoría.
                                </small>
                            </div>
                            <div className="modal-footer border-0">
                                <button type="button" className="btn btn-light" onClick={() => setSelectedEvent(null)}>Cancelar</button>
                                <button type="button" className="btn btn-success px-4" onClick={handleResolve}>Confirmar Resolución</button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default PanicRoom;