// src/services/apiService.js - CREA ESTE ARCHIVO
import api from '../config/axios';

export const apiService = {
    // AUTH
    login: (credentials) => api.post('/login', credentials),

    // DASHBOARD
    getDashboardStats: () => api.get('/admin/dashboard'),

    //STUDENTS
    getStudents: () => api.get('/admin/students'),

    // ATTENDANCE
    getAttendance: (params) => api.get('/admin/attendance', { params }),
    markAttendance: (data) => api.post('/admin/attendance/manual', data),

    // REPORTS
    getDailyReport: (date) => api.get(`/admin/reports/daily?date=${date}`),
    getAbsenceReport: () => api.get('/admin/reports/absences'),

    // PANIC
    triggerPanic: (data) => api.post('/panic/trigger', data),
    getPanicEvents: () => api.get('/panic/events'),
    resolvePanic: (id, data) => api.post(`/panic/events/${id}/resolve`, data),
    acknowledgePanic: (id) => api.post(`/panic/events/${id}/acknowledge`),

    // SCHOOLS
    getSchools: () => api.get('/admin/schools'),
    getSchoolStudents: (schoolId) => api.get(`/admin/schools/${schoolId}/students`),

    // PARENT
    getParentStudents: () => api.get('/parent/students'),
    getStudentAttendance: (studentId) => api.get(`/parent/attendance/${studentId}`),
};