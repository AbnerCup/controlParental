import React, { useState } from 'react';
import { NavLink } from 'react-router-dom';
const SideBar = ({ user, onLogout }) => {
    const [isExpanded, setIsExpanded] = useState(true);

    // 1. Normalizar el rol para evitar errores de mayúsculas o nulos
    const userRoleKey = user?.primary_role?.toLowerCase() || 'student';

    // 2. Definición de bloques de menú por responsabilidad
    const menuBlocks = {
        // Módulos exclusivos de infraestructura y administración global
        super_admin: [
            { label: 'Dashboard Global', icon: 'bi-grid-fill', path: '/dashboard' },
            { label: 'Escuelas', icon: 'bi-building', path: '/schools' },
            { label: 'Gateways HMAC', icon: 'bi-shield-lock', path: '/gateways' },
        ],
        // Módulos de gestión institucional (Directores y Administradores)
        school_management: [
            { label: 'Panel Control', icon: 'bi-speedometer2', path: '/dashboard' },
            { label: 'Panic Room', icon: 'bi-exclamation-octagon-fill', path: '/panicRoom' },
            { label: 'Asistencia', icon: 'bi-calendar-check', path: '/attendance' },
            { label: 'Estudiantes', icon: 'bi-people', path: '/students' },
            { label: 'Reportes', icon: 'bi-bar-chart-line', path: '/reports' }
        ],
        // Módulos operativos (Seguridad y monitoreo)
        operator: [
            { label: 'Panic Room', icon: 'bi-exclamation-octagon-fill', path: '/panicRoom' },
            { label: 'Asistencia Hoy', icon: 'bi-calendar-check', path: '/attendance' },
            { label: 'Estudiantes', icon: 'bi-people', path: '/students' }
        ],
        // Módulos para padres y alumnos
        family: [
            { label: 'Mis Hijos', icon: 'bi-person-badge', path: '/my-children' },
            { label: 'Asistencia', icon: 'bi-calendar3', path: '/child-attendance' },
            { label: 'Alertar Pánico', icon: 'bi-exclamation-triangle-fill', path: '/panic-trigger' }
        ]
    };

    // 3. Lógica Maestra: Construcción del array según el rol
    let rawMenuItems = [];

    switch (userRoleKey) {
        case 'super_admin':
            // Super Admin ve gestión global + gestión escolar
            rawMenuItems = [...menuBlocks.super_admin, ...menuBlocks.school_management];
            break;
        case 'admin':
        case 'school_admin':
            rawMenuItems = menuBlocks.school_management;
            break;
        case 'operator':
            rawMenuItems = menuBlocks.operator;
            break;
        case 'guardian':
        case 'student':
            rawMenuItems = menuBlocks.family;
            break;
        default:
            rawMenuItems = [];
    }

    // 4. ELIMINACIÓN DE DUPLICADOS Y LIMPIEZA
    // Usamos el 'path' como identificador único para que no se repita el Dashboard
    const menuItems = rawMenuItems.filter((item, index, self) =>
        index === self.findIndex((t) => t.path === item.path)
    );
    return (
        <div className={`d-flex flex-column vh-100 bg-dark text-white shadow transition-all ${isExpanded ? 'w-250' : 'w-80'}`}
            style={{
                width: isExpanded ? '260px' : '80px',
                transition: 'width 0.3s',
                zIndex: 1000,
                flexShrink: 0 // Evita que el sidebar se colapse
            }}>

            {/* Logo y Nombre de App */}
            <div className="p-3 d-flex align-items-center justify-content-between border-bottom border-secondary" style={{ height: '70px' }}>
                {isExpanded && (
                    <div className="d-flex align-items-center">
                        <i className="bi bi-shield-check text-primary fs-4 me-2"></i>
                        <span className="fw-bold fs-5 text-primary">SchoolTrack</span>
                    </div>
                )}
                <button onClick={() => setIsExpanded(!isExpanded)} className="btn btn-sm text-white border-0 p-0 ms-auto">
                    <i className={`bi ${isExpanded ? 'bi-chevron-left' : 'bi-chevron-right'} fs-5 text-secondary`}></i>
                </button>
            </div>

            {/* Menú de Navegación Scrolleable */}
            <nav className="flex-grow-1 p-2 mt-2 overflow-y-auto custom-scrollbar">
                {menuItems.map((item) => (
                    <NavLink
                        key={item.path}
                        to={item.path}
                        className={({ isActive }) =>
                            `d-flex align-items-center p-3 mb-1 rounded text-decoration-none transition-all ${isActive ? 'bg-primary text-white shadow' : 'text-secondary hover-sidebar'
                            }`
                        }
                    >
                        <i className={`bi ${item.icon} ${isExpanded ? 'fs-5' : 'fs-4'}`}></i>
                        {isExpanded && <span className="ms-3 fw-medium text-nowrap">{item.label}</span>}
                    </NavLink>
                ))}
            </nav>

            {/* Perfil de Usuario y Logout */}
            <div className="p-3 border-top border-secondary bg-black bg-opacity-25">
                <div className="d-flex align-items-center mb-3">
                    <div className="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                        style={{ width: '35px', height: '35px', flexShrink: 0 }}>
                        {user?.name?.charAt(0).toUpperCase() || 'U'}
                    </div>
                    {isExpanded && (
                        <div className="ms-2 overflow-hidden text-nowrap">
                            <p className="mb-0 small fw-bold text-white">{user?.name || 'Usuario'}</p>
                            <p className="mb-0 text-muted" style={{ fontSize: '10px' }}>{user?.roles?.[0] || 'Usuario'}</p>
                        </div>
                    )}
                </div>

                <button onClick={onLogout} className="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center py-2">
                    <i className="bi bi-box-arrow-right me-2"></i>
                    {isExpanded && <span>Cerrar Sesión</span>}
                </button>
            </div>
        </div>
    );
};


export default SideBar;