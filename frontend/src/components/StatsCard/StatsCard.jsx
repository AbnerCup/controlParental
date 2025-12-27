import React from 'react';
import './StatsCard.css';

const StatsCard = ({ title, value, subtitle, color, icon, percentage, trend, loading }) => {
    const colorClasses = {
        primary: { bg: 'var(--primary-light)', text: 'var(--primary)', border: 'var(--primary)' },
        success: { bg: 'var(--success-light)', text: 'var(--success)', border: 'var(--success)' },
        danger: { bg: 'var(--danger-light)', text: 'var(--danger)', border: 'var(--danger)' },
        warning: { bg: 'var(--warning-light)', text: 'var(--warning)', border: 'var(--warning)' },
    };

    const colors = colorClasses[color] || colorClasses.primary;

    return (
        <div
            className="stats-card"
            style={{
                borderLeft: `4px solid ${colors.border}`,
                backgroundColor: colors.bg
            }}
        >
            <div className="stats-card-header">
                <div className="stats-icon">{icon}</div>
                <h3 className="stats-title">{title}</h3>
            </div>

            <div className="stats-content">
                {loading ? (
                    <div className="loading-placeholder">
                        <div className="loading-bar"></div>
                    </div>
                ) : (
                    <>
                        <div className="stats-value" style={{ color: colors.text }}>
                            {value.toLocaleString()}
                        </div>
                        <div className="stats-details">
                            <span className="stats-subtitle">{subtitle}</span>
                            {percentage !== null && (
                                <span className="stats-percentage" style={{ color: colors.text }}>
                                    {percentage}%
                                </span>
                            )}
                        </div>
                    </>
                )}
            </div>

            {percentage !== null && !loading && (
                <div className="stats-progress">
                    <div
                        className="progress-bar"
                        style={{
                            width: `${percentage}%`,
                            backgroundColor: colors.border
                        }}
                    />
                </div>
            )}
        </div>
    );
};

export default StatsCard;