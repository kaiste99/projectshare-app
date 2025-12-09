-- Migration: Create Project Plans and Tasks Table
-- Version: 005

-- Project plan versions
CREATE TABLE IF NOT EXISTS project_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_current BOOLEAN DEFAULT TRUE,
    published_at TIMESTAMP NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_project_version (project_id, version),
    INDEX idx_current (project_id, is_current)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Plan tasks/milestones
CREATE TABLE IF NOT EXISTS project_tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    parent_task_id INT UNSIGNED NULL,
    sort_order INT UNSIGNED DEFAULT 0,

    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    task_type ENUM('milestone', 'task', 'phase') DEFAULT 'task',

    -- Dates
    planned_start_date DATE NULL,
    planned_end_date DATE NULL,
    actual_start_date DATE NULL,
    actual_end_date DATE NULL,

    -- Status
    status ENUM('pending', 'in_progress', 'completed', 'cancelled', 'delayed') DEFAULT 'pending',
    completion_percentage TINYINT UNSIGNED DEFAULT 0,

    -- Assignments
    assigned_to_type ENUM('team_member', 'subcontractor') NULL,
    assigned_to_id INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (plan_id) REFERENCES project_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_task_id) REFERENCES project_tasks(id) ON DELETE CASCADE,
    INDEX idx_plan (plan_id),
    INDEX idx_parent (parent_task_id),
    INDEX idx_status (status),
    INDEX idx_dates (planned_start_date, planned_end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task impacts (consequences for stakeholders)
CREATE TABLE IF NOT EXISTS task_impacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id INT UNSIGNED NOT NULL,
    impact_type ENUM('electricity_interruption', 'water_interruption', 'heating_interruption', 'noise', 'access_restriction', 'evacuation', 'other') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',

    title VARCHAR(255) NOT NULL,
    description TEXT NULL,

    -- Who is affected
    affects_building_owner BOOLEAN DEFAULT FALSE,
    affects_tenants BOOLEAN DEFAULT FALSE,
    affects_neighbors BOOLEAN DEFAULT FALSE,

    -- Duration
    impact_start_datetime DATETIME NULL,
    impact_end_datetime DATETIME NULL,
    estimated_duration_hours INT UNSIGNED NULL,

    -- Instructions
    preparation_instructions TEXT NULL,
    during_instructions TEXT NULL,
    after_instructions TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (task_id) REFERENCES project_tasks(id) ON DELETE CASCADE,
    INDEX idx_task (task_id),
    INDEX idx_type (impact_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Plan change notifications
CREATE TABLE IF NOT EXISTS plan_change_notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    change_type ENUM('new_version', 'schedule_change', 'impact_added', 'impact_changed', 'task_added', 'task_removed') NOT NULL,
    change_summary TEXT NOT NULL,
    notified_at TIMESTAMP NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES project_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_plan (plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Track who was notified
CREATE TABLE IF NOT EXISTS notification_recipients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    notification_id INT UNSIGNED NOT NULL,
    stakeholder_id INT UNSIGNED NULL,
    user_id INT UNSIGNED NULL,
    email VARCHAR(255) NOT NULL,
    sent_at TIMESTAMP NULL,
    opened_at TIMESTAMP NULL,
    acknowledged_at TIMESTAMP NULL,
    FOREIGN KEY (notification_id) REFERENCES plan_change_notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (stakeholder_id) REFERENCES project_stakeholders(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_notification (notification_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
