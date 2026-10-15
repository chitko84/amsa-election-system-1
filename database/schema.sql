-- AMSA AIU Election: MySQL 8 / MariaDB (XAMPP) - first import
-- Create database via phpMyAdmin then select it and import this file.
CREATE TABLE IF NOT EXISTS admins (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(80) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 full_name VARCHAR(160) NOT NULL,
 role ENUM('super','officer','viewer') NOT NULL DEFAULT 'officer',
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS elections (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(160) NOT NULL,
 slug VARCHAR(80) NOT NULL UNIQUE,
 registration_open TINYINT(1) NOT NULL DEFAULT 0,
 results_published TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS election_rounds (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 election_id BIGINT UNSIGNED NOT NULL,
 round_number SMALLINT UNSIGNED NOT NULL,
 round_type ENUM('main','runoff') NOT NULL DEFAULT 'main',
 status ENUM('draft','open','closed') NOT NULL DEFAULT 'draft',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_round_number (election_id,round_number),
 CONSTRAINT fk_round_election FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS positions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 election_id BIGINT UNSIGNED NOT NULL,
 name VARCHAR(90) NOT NULL,
 slug VARCHAR(90) NOT NULL,
 display_order TINYINT UNSIGNED NOT NULL,
 UNIQUE KEY uq_position_slug (election_id,slug),
 UNIQUE KEY uq_position_order (election_id,display_order),
 CONSTRAINT fk_position_election FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS round_positions (
 round_id BIGINT UNSIGNED NOT NULL,
 position_id BIGINT UNSIGNED NOT NULL,
 PRIMARY KEY (round_id,position_id),
 CONSTRAINT fk_rp_round FOREIGN KEY (round_id) REFERENCES election_rounds(id) ON DELETE RESTRICT,
 CONSTRAINT fk_rp_position FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS voters (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 student_id VARCHAR(32) NOT NULL UNIQUE,
 full_name VARCHAR(160) NOT NULL,
 student_email VARCHAR(160) NULL,
 gender VARCHAR(30) NULL,
 year_of_study VARCHAR(30) NULL,
 major VARCHAR(160) NULL,
 school VARCHAR(160) NULL,
 pin_hash VARCHAR(255) NOT NULL,
 active TINYINT(1) NOT NULL DEFAULT 1,
 failed_attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 locked_until DATETIME NULL,
 pin_version INT UNSIGNED NOT NULL DEFAULT 1,
 created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_voter_email (student_email),
 CONSTRAINT fk_voter_created_by FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS candidates (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 election_id BIGINT UNSIGNED NOT NULL,
 position_id BIGINT UNSIGNED NOT NULL,
 student_id VARCHAR(32) NOT NULL,
 name VARCHAR(160) NOT NULL,
 manifesto TEXT NULL,
 photo_path VARCHAR(255) NULL,
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_candidate_election_student (election_id,student_id),
 KEY idx_candidate_position (position_id,active),
 CONSTRAINT fk_candidate_election FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE RESTRICT,
 CONSTRAINT fk_candidate_position FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS round_candidates (
 round_id BIGINT UNSIGNED NOT NULL,
 candidate_id BIGINT UNSIGNED NOT NULL,
 PRIMARY KEY(round_id,candidate_id),
 CONSTRAINT fk_rc_round FOREIGN KEY (round_id) REFERENCES election_rounds(id) ON DELETE RESTRICT,
 CONSTRAINT fk_rc_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS voter_participation (
 round_id BIGINT UNSIGNED NOT NULL,
 voter_id BIGINT UNSIGNED NOT NULL,
 voted TINYINT(1) NOT NULL DEFAULT 0,
 voted_at DATETIME NULL,
 PRIMARY KEY (round_id,voter_id),
 CONSTRAINT fk_vp_round FOREIGN KEY (round_id) REFERENCES election_rounds(id) ON DELETE RESTRICT,
 CONSTRAINT fk_vp_voter FOREIGN KEY (voter_id) REFERENCES voters(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ANONYMOUS: no voter identifier, IP, timestamp, participation FK, or receipt stored on ballot.
CREATE TABLE IF NOT EXISTS anonymous_ballots (
 id CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY,
 round_id BIGINT UNSIGNED NOT NULL,
 KEY idx_ballots_round(round_id),
 CONSTRAINT fk_ballot_round FOREIGN KEY (round_id) REFERENCES election_rounds(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ballot_choices (
 ballot_id CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 position_id BIGINT UNSIGNED NOT NULL,
 candidate_id BIGINT UNSIGNED NOT NULL,
 PRIMARY KEY (ballot_id,position_id),
 KEY idx_choices_candidate(candidate_id),
 CONSTRAINT fk_bc_ballot FOREIGN KEY (ballot_id) REFERENCES anonymous_ballots(id) ON DELETE RESTRICT,
 CONSTRAINT fk_bc_position FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE RESTRICT,
 CONSTRAINT fk_bc_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 admin_id BIGINT UNSIGNED NULL,
 event VARCHAR(100) NOT NULL,
 detail VARCHAR(255) NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_audit_created(created_at),
 CONSTRAINT fk_audit_admin FOREIGN KEY(admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 account_kind ENUM('voter','admin') NOT NULL,
 identifier_hash CHAR(64) NOT NULL,
 ip_hash CHAR(64) NOT NULL,
 succeeded TINYINT(1) NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_login_identifier (identifier_hash,created_at),
 KEY idx_login_ip (ip_hash,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
