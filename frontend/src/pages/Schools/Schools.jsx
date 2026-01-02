import React, { useState, useEffect } from 'react';
import { apiService } from '../../services/apiService';

const Schools = () => {
    const [schools, setSchools] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState("");
    const [pagination, setPagination] = useState({});
    // Estado para controlar el modal
    const [showModal, setShowModal] = useState(false);

    // Estado para los campos del formulario
    const [formData, setFormData] = useState({
        name: '',
        code: '',
        city: '',
        address: '',
        status: 'active'
    });
    const [editMode, setEditMode] = useState(false);
    const [currentId, setCurrentId] = useState(null); // Para saber qué escuela editar

    // Función para obtener datos del backend
    const fetchSchools = async (page = 1, search = "") => {
        setLoading(true);
        try {
            const response = await apiService.getSchools({
                params: {
                    page,
                    search,
                    per_page: 6
                },
            });

            if (response.data.success) {
                setSchools(response.data.data);
                setPagination(response.data.pagination);
            }
            console.log(response.data);

        } catch (error) {
            console.error("Error cargando escuelas:", error);
        } finally {
            setLoading(false);
        }
    };
    const handleOpenCreate = () => {
        setFormData({ name: '', code: '', city: '', address: '', status: 'active' });
        setCurrentId(null);
        setEditMode(false);
        setShowModal(false);
        setTimeout(() => setShowModal(true), 10);
    };
    const handleSubmit = async (e) => {
        e.preventDefault();
        console.log("1. El formulario se envió");
        console.log("2. Datos a enviar:", formData);
        setLoading(true);
        try {
            let response;
            console.log("3. Intentando llamar al servicio...");
            if (editMode) {
                response = await apiService.updateSchool(currentId, formData);
            } else {
                response = await apiService.createSchool(formData);
            }
            console.log("4. Respuesta recibida:", response);
            if (response.status === 200 || response.status === 201) {
                setShowModal(false);
                setFormData({ name: '', code: '', city: '', address: '', status: 'active' });
                await fetchSchools();
                alert(editMode ? "¡Escuela actualizada con éxito!" : "¡Escuela registrada correctamente!");
            }
        } catch (error) {
            console.error("Error en la operación:", error);
            const errorMessage = error.response?.data?.message || "Ocurrió un error inesperado al guardar.";
            alert("Error: " + errorMessage);
            console.error("Error en la operación:", error);

        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        const delayDebounceFn = setTimeout(() => {
            fetchSchools(1, searchTerm);
        }, 500);

        return () => clearTimeout(delayDebounceFn);
    }, [searchTerm]);

    return (
        <div className="container-fluid py-4 bg-light min-vh-100">
            {/* Header y Buscador */}
            <div className="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 className="fw-bold text-dark">Unidades Educativas</h1>
                    <p className="text-muted">Administración de escuelas y configuración de Gateways</p>
                </div>
                <div className="d-flex gap-3">
                    <input
                        type="text"
                        className="form-control rounded-pill border-0 shadow-sm px-4"
                        placeholder="Buscar por nombre o código..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                    <button
                        onClick={handleOpenCreate}
                        className="btn btn-primary rounded-pill shadow-sm px-4 d-flex align-items-center"
                    >
                        <i className="bi bi-plus-circle me-2"></i>
                        <span>Registrar Institución</span>
                    </button>
                </div>
            </div>

            {loading ? (
                <div className="text-center py-5">Cargando escuelas...</div>
            ) : (
                <div className="row g-4">
                    {schools.map((school) => (
                        <div className="col-xl-6" key={school.id}>
                            <div className="card border-0 shadow-sm rounded-4 overflow-hidden">
                                <div className="card-body p-4">
                                    <div className="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 className="fw-bold mb-1">{school.name}</h5>
                                            <span className="badge bg-light text-secondary border">ID: {school.id} | {school.code}</span>
                                        </div>
                                        {/* Status real de la BD */}
                                        <span className={`badge rounded-pill px-3 py-2 ${school.status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'}`}>
                                            {school.status.toUpperCase()}
                                        </span>
                                    </div>

                                    <div className="row g-2 my-3">
                                        <div className="col-6">
                                            <div className="p-3 bg-light rounded-3">
                                                <small className="text-muted d-block">Ubicación</small>
                                                <span className="fw-bold">{school.city || 'No definida'}</span>
                                            </div>
                                        </div>
                                        <div className="col-6">
                                            <div className="p-3 bg-light rounded-3">
                                                <small className="text-muted d-block">Creado el</small>
                                                <span className="fw-bold">{new Date(school.created_at).toLocaleDateString()}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {/* HMAC mockup por ahora, ya que la BD no lo tiene aún */}
                                    <div className="bg-dark bg-opacity-10 p-3 rounded-4 border border-dashed border-secondary">
                                        <small className="fw-bold text-uppercase text-muted" style={{ fontSize: '10px' }}>HMAC Key (Próximamente)</small>
                                        <div className="d-flex align-items-center justify-content-between mt-1">
                                            <code className="text-primary small">KID-HIDDEN-{school.id}</code>
                                            <button className="btn btn-sm btn-outline-dark px-3 rounded-pill">Gestionar</button>
                                        </div>
                                    </div>

                                    <div className="d-flex gap-2 mt-4">
                                        <button className="btn btn-light rounded-pill flex-grow-1 border">
                                            <i className="bi bi-pencil me-2"></i> Editar
                                        </button>
                                        <button className="btn btn-outline-primary rounded-pill flex-grow-1">
                                            <i className="bi bi-people me-2"></i> Estudiantes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* Simple Paginación */}
            {!loading && pagination.last_page > 1 && (
                <div className="d-flex justify-content-center mt-5">
                    <nav>
                        <ul className="pagination">
                            {[...Array(pagination.last_page)].map((_, i) => (
                                <li key={i} className={`page-item ${pagination.current_page === i + 1 ? 'active' : ''}`}>
                                    <button className="page-link rounded-circle mx-1" onClick={() => fetchSchools(i + 1, searchTerm)}>
                                        {i + 1}
                                    </button>
                                </li>
                            ))}
                        </ul>
                    </nav>
                </div>
            )}
            {showModal && (
                <div className="modal d-block animate__animated animate__fadeIn" style={{ backgroundColor: 'rgba(0,0,0,0.6)', zIndex: 1050 }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg">

                            {/* Cabecera */}
                            <div className="modal-header bg-light border-0 p-4">
                                <h5 className="fw-bold mb-0">Nueva Unidad Educativa</h5>
                                <button type="button" className="btn-close" onClick={() => setShowModal(false)}></button>
                            </div>

                            {/* Formulario */}
                            <form onSubmit={handleSubmit}>
                                <div className="modal-body p-4">
                                    <div className="mb-3">
                                        <label className="form-label small fw-bold text-secondary">Nombre de la Escuela</label>
                                        <input
                                            type="text"
                                            className="form-control form-control-lg fs-6 shadow-none"
                                            placeholder="Ej: Colegio San Agustín"
                                            required
                                            value={formData.name}
                                            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                        />
                                    </div>

                                    <div className="row">
                                        <div className="col-md-6 mb-3">
                                            <label className="form-label small fw-bold text-secondary">Código (SIE/Interno)</label>
                                            <input
                                                type="text"
                                                className="form-control shadow-none"
                                                placeholder="Ej: ESC-2026"
                                                required
                                                value={formData.code}
                                                onChange={(e) => setFormData({ ...formData, code: e.target.value })}
                                            />
                                        </div>
                                        <div className="col-md-6 mb-3">
                                            <label className="form-label small fw-bold text-secondary">Ciudad</label>
                                            <select
                                                className="form-select shadow-none"
                                                value={formData.city}
                                                onChange={(e) => setFormData({ ...formData, city: e.target.value })}
                                                required
                                            >
                                                <option value="">Seleccionar...</option>
                                                <option value="Oruro">Oruro</option>
                                                <option value="La Paz">La Paz</option>
                                                <option value="Cochabamba">Cochabamba</option>
                                                <option value="Santa Cruz">Santa Cruz</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div className="mb-0">
                                        <label className="form-label small fw-bold text-secondary">Dirección</label>
                                        <textarea
                                            className="form-control shadow-none"
                                            rows="2"
                                            placeholder="Calle, Número, Zona..."
                                            value={formData.address}
                                            onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                                        ></textarea>
                                    </div>
                                </div>

                                {/* Botones de acción */}
                                <div className="modal-footer border-0 p-4 pt-0">
                                    <button type="button" className="btn btn-link text-decoration-none text-secondary" onClick={() => setShowModal(false)}>
                                        Cancelar
                                    </button>
                                    <button type="submit" className="btn btn-primary px-4 rounded-pill">
                                        <i className="bi bi-save me-2"></i>Guardar Escuela
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </div>

    );
};

export default Schools;