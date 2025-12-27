import React from 'react';
import './DashboardHeader.css';

const DashboardHeader = ({ title, subtitle, onRefresh, onGenerateReport }) => {
    return (
        <header className="dashboard-header">
            <div className="header-content">
                <div>
                    <h1 className="header-title">{title}</h1>
                    <p className="header-subtitle">{subtitle}</p>
                </div>
                <div className="header-actions">
                    <button
                        className="btn-refresh"
                        onClick={onRefresh}
                        aria-label="Actualizar datos"
                    >
                        <svg className="icon" viewBox="0 0 24 24">
                            <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z" />
                        </svg>
                        Actualizar
                    </button>
                    <button
                        className="btn-report"
                        onClick={onGenerateReport}
                    >
                        <svg className="icon" viewBox="0 0 24 24">
                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" />
                        </svg>
                        Generar Reporte
                    </button>
                </div>
            </div>
        </header>
    );
};

export default DashboardHeader;