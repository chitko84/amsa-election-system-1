CREATE TABLE IF NOT EXISTS pin_recovery_requests (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 voter_id BIGINT UNSIGNED NOT NULL,
 status ENUM('pending','resolved','rejected') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 resolved_at DATETIME NULL,
 resolved_by BIGINT UNSIGNED NULL,
 pending_voter_id BIGINT UNSIGNED GENERATED ALWAYS AS (IF(status='pending',voter_id,NULL)) STORED,
 UNIQUE KEY uq_pin_recovery_pending_voter (pending_voter_id),
 KEY idx_pin_recovery_status_created (status,created_at),
 KEY idx_pin_recovery_voter_created (voter_id,created_at),
 CONSTRAINT fk_pin_recovery_voter FOREIGN KEY (voter_id) REFERENCES voters(id) ON DELETE RESTRICT,
 CONSTRAINT fk_pin_recovery_admin FOREIGN KEY (resolved_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pin_recovery_attempts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 identifier_hash CHAR(64) NOT NULL,
 ip_hash CHAR(64) NOT NULL,
 recognized TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_pin_recovery_attempt_identifier (identifier_hash,created_at),
 KEY idx_pin_recovery_attempt_ip (ip_hash,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
