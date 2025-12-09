-- Migration: Create Projects Table
-- Version: 003

CREATE TABLE IF NOT EXISTS projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    reference_number VARCHAR(50) NULL,
    description TEXT NULL,
    project_type ENUM('heating', 'electricity', 'photovoltaics', 'plumbing', 'renovation', 'other') DEFAULT 'other',
    status ENUM('draft', 'planning', 'in_progress', 'on_hold', 'completed', 'cancelled') DEFAULT 'draft',

    -- Location
    address_line1 VARCHAR(255) NULL,
    address_line2 VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    postal_code VARCHAR(20) NULL,
    country VARCHAR(100) DEFAULT 'Germany',

    -- Dates
    planned_start_date DATE NULL,
    planned_end_date DATE NULL,
    actual_start_date DATE NULL,
    actual_end_date DATE NULL,

    -- Manager
    project_manager_id INT UNSIGNED NULL,

    -- Settings
    notify_on_update BOOLEAN DEFAULT TRUE,
    require_acknowledgement BOOLEAN DEFAULT TRUE,

    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (project_manager_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_account (account_id),
    INDEX idx_status (status),
    INDEX idx_reference (reference_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Project team members (internal)
CREATE TABLE IF NOT EXISTS project_team (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    role VARCHAR(100) NULL,
    can_edit BOOLEAN DEFAULT FALSE,
    can_manage_stakeholders BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_project_user (project_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
