// src/services/apiService.js - CREA ESTE ARCHIVO
import api from '../config/axios';

export const apiService = {
    // AUTH
    login: (credentials) => api.post('/login', credentials),

    // DASHBOARD
    getDashboardStats: () => api.get('/admin/dashboard'),

    // ATTENDANCE
    getAttendance: (params) => api.get('/admin/attendance', { params }),
    markAttendance: (data) => api.post('/admin/attendance/manual', data),

    // REPORTS
    getDailyReport: (date) => api.get(`/admin/reports/daily?date=${date}`),
    getAbsenceReport: () => api.get('/admin/reports/absences'),

    // PANIC
    triggerPanic: (data) => api.post('/api/panic/trigger', data),
    getPanicEvents: () => api.get('/api/panic/events'),
    resolvePanic: (id) => api.post(`/api/panic/events/${id}/resolve`),

    // SCHOOLS
    getSchools: () => api.get('/admin/schools'),
    getSchoolStudents: (schoolId) => api.get(`/admin/schools/${schoolId}/students`),

    // PARENT
    getParentStudents: () => api.get('/api/parent/students'),
    getStudentAttendance: (studentId) => api.get(`/api/parent/attendance/${studentId}`),
};