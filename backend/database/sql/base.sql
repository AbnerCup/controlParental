
-- ================================================
-- Parental Control DB (MySQL 8) - Full Schema
-- ================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1) Usuarios y roles
CREATE TABLE users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(64) NOT NULL UNIQUE,  -- admin, school_admin, operator, guardian, student
  name VARCHAR(120) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_user (
  role_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, user_id),
  CONSTRAINT fk_role_user_role FOREIGN KEY (role_id) REFERENCES roles(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_role_user_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Escuelas y alcance de usuarios
CREATE TABLE schools (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  code VARCHAR(64) NOT NULL UNIQUE,
  timezone VARCHAR(64) NOT NULL DEFAULT 'America/La_Paz',
  city VARCHAR(120) NULL,
  address VARCHAR(240) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_schools (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  school_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_user_school (user_id, school_id),
  CONSTRAINT fk_user_schools_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_user_schools_school FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Organización académica y calendario
CREATE TABLE grades (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(80) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_grade_name_per_school (school_id, name),
  CONSTRAINT fk_grades_school FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE school_calendar_days (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT UNSIGNED NOT NULL,
  `day` DATE NOT NULL,
  day_type ENUM('class','holiday','closed','strike','maintenance') NOT NULL DEFAULT 'class',
  note VARCHAR(240) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_school_day (school_id, `day`),
  CONSTRAINT fk_calendar_school FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NOTE: effective_from (DATE) must be provided by the app; MySQL doesn't allow DEFAULT CURRENT_DATE for DATE
CREATE TABLE grade_schedules (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  grade_id BIGINT UNSIGNED NOT NULL,
  weekday TINYINT UNSIGNED NOT NULL,  -- 0..6
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  late_grace_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  effective_from DATE NOT NULL,       -- set by app (local school date)
  effective_to DATE NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_grade_weekday_window (grade_id, weekday, effective_from),
  CONSTRAINT fk_sched_grade FOREIGN KEY (grade_id) REFERENCES grades(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Personas: tutores y alumnos
CREATE TABLE guardians (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL, -- si el tutor tiene cuenta en el sistema
  first_name VARCHAR(120) NOT NULL,
  last_name VARCHAR(160) NOT NULL,
  email VARCHAR(160) NULL UNIQUE,
  phone VARCHAR(40) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  CONSTRAINT fk_guardian_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE students (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT UNSIGNED NOT NULL,
  grade_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,     -- si el estudiante tiene cuenta propia
  student_code VARCHAR(64) NOT NULL UNIQUE,
  first_name VARCHAR(120) NOT NULL,
  last_name VARCHAR(160) NOT NULL,
  birth_date DATE NULL,
  status ENUM('active','inactive','graduated','transferred') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  UNIQUE KEY uq_students_user (user_id),  -- permite múltiples NULL; 1:1 si no es NULL
  CONSTRAINT fk_students_school FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_students_grade FOREIGN KEY (grade_id) REFERENCES grades(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NOTE: start_date (DATE) must be provided by the app; MySQL doesn't allow DEFAULT CURRENT_DATE for DATE
CREATE TABLE student_guardians (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  guardian_id BIGINT UNSIGNED NOT NULL,
  relationship ENUM('mother','father','tutor','other') NOT NULL DEFAULT 'tutor',
  is_primary BOOLEAN NOT NULL DEFAULT FALSE,
  start_date DATE NOT NULL,   -- set by app (local school date)
  end_date DATE NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_active_link (student_id, guardian_id, start_date),
  CONSTRAINT fk_sg_student FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_sg_guardian FOREIGN KEY (guardian_id) REFERENCES guardians(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Dispositivos y asignaciones
CREATE TABLE devices (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT UNSIGNED NOT NULL,
  `type` ENUM('RFID','NFC','QR','BLE') NOT NULL DEFAULT 'RFID',
  uid VARCHAR(128) NOT NULL UNIQUE,
  status ENUM('active','lost','replaced','retired') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_devices_school FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE device_assignments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  device_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  assigned_at DATETIME NOT NULL,   -- UTC
  unassigned_at DATETIME NULL,     -- UTC
  note VARCHAR(240) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_active_device_student (device_id, student_id, assigned_at),
  KEY idx_da_active (device_id, unassigned_at),
  CONSTRAINT fk_da_device FOREIGN KEY (device_id) REFERENCES devices(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_da_student FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6) Asistencia (eventos crudos + estado del día)
CREATE TABLE attendance_events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  device_id BIGINT UNSIGNED NULL,
  event_type ENUM('check_in','check_out','manual_adjust','correction') NOT NULL,
  `source` ENUM('device','app','import') NOT NULL DEFAULT 'device',
  occurred_at DATETIME NOT NULL,     -- UTC
  actor_user_id BIGINT UNSIGNED NULL,
  payload JSON NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY idx_aev_student_time (student_id, occurred_at),
  KEY idx_aev_school_time (school_id, occurred_at),
  CONSTRAINT fk_aev_school FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_aev_student FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_aev_device FOREIGN KEY (device_id) REFERENCES devices(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_aev_actor FOREIGN KEY (actor_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attendances (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  class_date DATE NOT NULL,          -- fecha académica local (derivada por la app)
  check_in_at DATETIME NULL,         -- UTC
  check_out_at DATETIME NULL,        -- UTC
  status ENUM('present','late','absent','left_early') NOT NULL DEFAULT 'present',
  method ENUM('device_scan','manual','import') NULL,
  device_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_att_unique_day (student_id, class_date),
  KEY idx_att_school_date (school_id, class_date),
  KEY idx_att_status (school_id, class_date, status),
  CONSTRAINT fk_att_school FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_att_student FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_att_device FOREIGN KEY (device_id) REFERENCES devices(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7) Botón de pánico (tutor y estudiante)
CREATE TABLE panic_events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT UNSIGNED NULL,
  student_id BIGINT UNSIGNED NULL,
  guardian_id BIGINT UNSIGNED NULL,
  device_id BIGINT UNSIGNED NULL,             -- si lo disparó hardware del estudiante
  triggered_by_user_id BIGINT UNSIGNED NULL,  -- si fue desde app autenticada
  triggered_at DATETIME NOT NULL,             -- UTC
  lat DECIMAL(10,7) NULL,
  lng DECIMAL(10,7) NULL,
  `source` ENUM('app','device') NOT NULL DEFAULT 'app',
  triggered_by_type ENUM('guardian','student') NOT NULL DEFAULT 'guardian',
  note VARCHAR(240) NULL,
  priority ENUM('low','medium','high','critical') NOT NULL DEFAULT 'high',
  resolved BOOLEAN NOT NULL DEFAULT FALSE,
  resolved_at DATETIME NULL,
  resolved_by_user_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY idx_pe_school_time (school_id, triggered_at),
  KEY idx_pe_student_time (student_id, triggered_at),
  CONSTRAINT fk_pe_school FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pe_student FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pe_guardian FOREIGN KEY (guardian_id) REFERENCES guardians(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pe_device FOREIGN KEY (device_id) REFERENCES devices(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pe_trigger_user FOREIGN KEY (triggered_by_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pe_resolved_user FOREIGN KEY (resolved_by_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE panic_event_actions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  panic_event_id BIGINT UNSIGNED NOT NULL,
  action_type ENUM('ack','dispatch','contact_parent','contact_school','resolve') NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  note VARCHAR(240) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_pea_event (panic_event_id, created_at),
  CONSTRAINT fk_pea_event FOREIGN KEY (panic_event_id) REFERENCES panic_events(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_pea_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8) Operacional (notificaciones y auditoría)
CREATE TABLE notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT UNSIGNED NULL,
  guardian_id BIGINT UNSIGNED NULL,
  channel ENUM('email','sms','whatsapp','push') NOT NULL,
  template_key VARCHAR(80) NOT NULL,
  payload JSON NULL,
  status ENUM('queued','sent','failed') NOT NULL DEFAULT 'queued',
  error_message VARCHAR(240) NULL,
  related_type ENUM('attendance','panic_event','device_event','generic') NOT NULL DEFAULT 'generic',
  related_id BIGINT UNSIGNED NULL,
  queued_at DATETIME NOT NULL,     -- UTC
  sent_at DATETIME NULL,           -- UTC
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY idx_notif_status (status, queued_at),
  KEY idx_notif_related (related_type, related_id),
  CONSTRAINT fk_notif_school FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_notif_guardian FOREIGN KEY (guardian_id) REFERENCES guardians(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT UNSIGNED NULL,
  actor_user_id BIGINT UNSIGNED NULL,
  `action` VARCHAR(80) NOT NULL,
  entity_type VARCHAR(80) NOT NULL,
  entity_id BIGINT UNSIGNED NULL,
  `changes` JSON NULL,
  ip VARCHAR(64) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_audit_school_time (school_id, created_at),
  CONSTRAINT fk_audit_school FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_audit_user FOREIGN KEY (actor_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9) Seguridad API y eventos de hardware
CREATE TABLE api_clients (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT UNSIGNED NULL,
  name VARCHAR(120) NOT NULL,
  `type` ENUM('gateway','device','service') NOT NULL DEFAULT 'gateway',
  key_id VARCHAR(64) NOT NULL UNIQUE,
  key_secret_hash VARCHAR(255) NOT NULL,  -- guardar hash, no el secreto plano
  active BOOLEAN NOT NULL DEFAULT TRUE,
  last_used_at DATETIME NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY idx_apiclient_school (school_id),
  CONSTRAINT fk_api_school FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE device_events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT UNSIGNED NULL,
  device_id BIGINT UNSIGNED NULL,
  api_client_id BIGINT UNSIGNED NULL,
  event_type ENUM('check_in','check_out','panic','heartbeat') NOT NULL,
  occurred_at DATETIME NOT NULL,    -- UTC
  received_at DATETIME NOT NULL,    -- UTC
  signature_valid BOOLEAN NOT NULL DEFAULT FALSE,
  payload JSON NOT NULL,
  processed BOOLEAN NOT NULL DEFAULT FALSE,
  error_message VARCHAR(240) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY idx_de_school_time (school_id, received_at),
  KEY idx_de_device_time (device_id, occurred_at),
  CONSTRAINT fk_de_school FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_de_device FOREIGN KEY (device_id) REFERENCES devices(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_de_client FOREIGN KEY (api_client_id) REFERENCES api_clients(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10) Cumplimiento (consentimientos)
CREATE TABLE consents (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  guardian_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  policy_version VARCHAR(32) NOT NULL,
  consented_at DATETIME NOT NULL,   -- UTC
  revoked_at DATETIME NULL,         -- UTC
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_consent (guardian_id, student_id, policy_version),
  CONSTRAINT fk_cons_guardian FOREIGN KEY (guardian_id) REFERENCES guardians(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cons_student FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11) Protección anti-spam (opcional)
CREATE TABLE panic_event_rate_limits (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  actor_type ENUM('guardian','student') NOT NULL,
  actor_id BIGINT UNSIGNED NOT NULL,
  `count` INT UNSIGNED NOT NULL DEFAULT 0,
  window_start DATETIME NOT NULL,   -- UTC (ej. ventana de 5 min)
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_rate_window (actor_type, actor_id, window_start),
  KEY idx_rate_lookup (actor_type, actor_id, window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
