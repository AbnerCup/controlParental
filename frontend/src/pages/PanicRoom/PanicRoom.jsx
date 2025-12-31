import React, { useState, useEffect, useCallback } from 'react';
import { apiService } from '../../services/apiService';

const PanicRoom = () => {
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedEvent, setSelectedEvent] = useState(null);
    const [resolutionNote, setResolutionNote] = useState("");

    const fetchEvents = useCallback(async () => {
        try {
            const response = await apiService.getPanicEvents();
            // Extraer el array de la respuesta paginada de Laravel
            const data = response.data?.data || response.data;
            setEvents(Array.isArray(data) ? data : []);
        } catch (error) {
            console.error("Error al obtener eventos:", error);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchEvents();
        const interval = setInterval(fetchEvents, 10000);
        return () => clearInterval(interval);
    }, [fetchEvents]);

    // Determina si alguien ya presionó "Atender" (Existe un ID de usuario pero no está resuelto)
    const isAcknowledge = (event) => !event.resolved && event.resolved_by_user_id;

    // Determina si la alerta es nueva (No está resuelta y nadie la ha tomado)
    const isOpen = (event) => !event.resolved && !event.resolved_by_user_id;

    const getVisualConfig = (event) => {
        if (event.resolved) return { color: "bg-success", text: "RESUELTO", icon: "bi-check-circle-fill" };
        if (isAcknowledge(event)) return { color: "bg-warning text-dark", text: "ATENDIENDO", icon: "bi-person-walking" };
        return { color: "bg-danger animate-pulse text-white", text: "ABIERTO", icon: "bi-megaphone-fill" };
    };

    const handleAcknowledge = async (id) => {
        try {
            await apiService.acknowledgePanic(id);
            fetchEvents();
        } catch (error) {
            console.error("Error al atender");
        }
    };

    const handleResolve = async () => {
        if (resolutionNote.length < 10) return alert("La nota debe tener al menos 10 caracteres.");
        try {
            await apiService.resolvePanic(selectedEvent.id, { resolution_note: resolutionNote });
            setEvents(prev => prev.filter(ev => ev.id !== selectedEvent.id));
            setSelectedEvent(null);
            setResolutionNote("");
        } catch (error) {
            alert("Error al resolver el evento.");
        }
    };

    if (loading && events.length === 0) return <div className="text-center p-5">Cargando Central de Emergencias...</div>;

    return (
        <div className="container-fluid py-4 bg-light min-vh-100">
            {/* Encabezado */}
            <div className="mb-4">
                <h1 className="fw-bold text-danger">
                    <i className="bi bi-exclamation-octagon-fill me-2"></i>
                    Central de Emergencias
                </h1>
                <p className="text-muted">Gestión de alertas en tiempo real vinculada al servidor</p>
            </div>

            <div className="row g-4">
                <div className="col-lg-8">
                    {events.length === 0 ? (
                        <div className="text-center p-5 bg-white rounded-4 shadow-sm">
                            <i className="bi bi-shield-check text-success display-1"></i>
                            <h3 className="mt-3">Sin alertas activas</h3>
                            <p className="text-muted">Todo está bajo control en este momento.</p>
                        </div>
                    ) : (
                        events.map(event => {
                            // Calculamos la configuración visual para CADA evento
                            const config = getVisualConfig(event);

                            return (
                                <div key={event.id} className={`card mb-3 border-0 shadow-sm border-start border-5 ${isOpen(event) ? 'border-danger' : 'border-warning'}`}>
                                    <div className="card-body p-4">
                                        <div className="d-flex justify-content-between align-items-center">
                                            <div className="d-flex align-items-center">
                                                {/* Cápsula del icono dinámica */}
                                                <div className={`p-3 rounded-circle me-3 d-flex align-items-center justify-content-center ${config.color}`} style={{ width: '60px', height: '60px' }}>
                                                    <i className={`bi ${config.icon} fs-4`}></i>
                                                </div>

                                                <div>
                                                    <h4 className="fw-bold mb-1">{event.student_first_name} {event.student_last_name}</h4>
                                                    <div className="small text-muted">
                                                        <i className="bi bi-clock me-1"></i> {event.occurred_at || event.triggered_at}
                                                        <span className="ms-2">| Origen: <strong>{event.source?.toUpperCase()}</strong></span>
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Badge de estado dinámico */}
                                            <span className={`badge rounded-pill px-3 py-2 ${config.color}`}>
                                                {config.text}
                                            </span>
                                        </div>

                                        <div className="mt-3 p-2 bg-light rounded">
                                            <p className="mb-0 small"><strong>Nota inicial:</strong> {event.note || "Sin descripción adicional"}</p>
                                        </div>

                                        <hr />

                                        <div className="d-flex justify-content-end gap-2">
                                            {/* Solo mostramos "Atender" si la alerta está ABIERTA */}
                                            {isOpen(event) && (
                                                <button className="btn btn-warning rounded-pill px-4 fw-bold" onClick={() => handleAcknowledge(event.id)}>
                                                    <i className="bi bi-hand-index-thumb me-2"></i>
                                                    Atender
                                                </button>
                                            )}

                                            <button className="btn btn-outline-dark rounded-pill px-4" onClick={() => window.open(`tel:${event.guardian_phone}`)}>
                                                <i className="bi bi-telephone me-2"></i>
                                                Llamar
                                            </button>

                                            <button className="btn btn-success rounded-pill px-4 fw-bold" onClick={() => setSelectedEvent(event)}>
                                                <i className="bi bi-check-lg me-2"></i>
                                                Resolver
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            );
                        })
                    )}
                </div>

                {/* Sidebar de Estadísticas */}
                <div className="col-lg-4">
                    <div className="card border-0 shadow-sm bg-dark text-white rounded-4 sticky-top" style={{ top: '20px' }}>
                        <div className="card-body p-4 text-center">
                            <h6 className="opacity-75 text-uppercase fw-bold small">Alertas en Monitoreo</h6>
                            <h2 className="display-4 fw-bold mb-0">{events.length}</h2>
                            <hr className="my-3 opacity-25" />
                            <div className="d-flex justify-content-around small">
                                <div>
                                    <div className="text-danger fw-bold">{events.filter(isOpen).length}</div>
                                    <div className="opacity-50">Nuevas</div>
                                </div>
                                <div>
                                    <div className="text-warning fw-bold">{events.filter(isAcknowledge).length}</div>
                                    <div className="opacity-50">En Proceso</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal de Resolución */}
            {selectedEvent && (
                <div className="modal d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.7)', backdropFilter: 'blur(4px)' }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg">
                            <div className="modal-header bg-success text-white border-0">
                                <h5 className="modal-title fw-bold">
                                    <i className="bi bi-shield-fill-check me-2"></i>
                                    Finalizar Alerta: {selectedEvent.student_first_name}
                                </h5>
                                <button type="button" className="btn-close btn-close-white" onClick={() => { setSelectedEvent(null); setResolutionNote(""); }}></button>
                            </div>
                            <div className="modal-body p-4">
                                <div className="mb-3">
                                    <label className="form-label fw-bold text-muted small uppercase">Protocolo seguido:</label>
                                    <textarea
                                        className="form-control border-2"
                                        rows="4"
                                        value={resolutionNote}
                                        onChange={(e) => setResolutionNote(e.target.value)}
                                        placeholder="Ej: Se localizó al alumno en el comedor, se trataba de una activación accidental. Se notificó a los padres."
                                        style={{ resize: 'none' }}
                                    ></textarea>
                                    <div className="form-text text-end">
                                        Mínimo 10 caracteres. Actual: {resolutionNote.length}
                                    </div>
                                </div>
                            </div>
                            <div className="modal-footer border-0 p-3">
                                <button className="btn btn-link text-muted text-decoration-none" onClick={() => { setSelectedEvent(null); setResolutionNote(""); }}>Cancelar</button>
                                <button
                                    className="btn btn-success px-5 rounded-pill fw-bold"
                                    onClick={handleResolve}
                                    disabled={resolutionNote.length < 10}
                                >
                                    Guardar y Cerrar Caso
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
export default PanicRoom;