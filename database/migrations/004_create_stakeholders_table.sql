-- Migration: Create Project Stakeholders Table
-- Version: 004

-- External stakeholders (building owners, tenants, subcontractors)
CREATE TABLE IF NOT EXISTS project_stakeholders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    stakeholder_type ENUM('building_owner', 'tenant', 'property_manager', 'subcontractor', 'other') NOT NULL,

    -- Contact info
    name VARCHAR(255) NOT NULL,
    company_name VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,

    -- Secret link access
    access_token VARCHAR(100) NOT NULL UNIQUE,
    access_token_expires_at TIMESTAMP NULL,

    -- Permissions
    can_view_documents BOOLEAN DEFAULT TRUE,
    can_view_schedule BOOLEAN DEFAULT TRUE,
    can_view_impacts BOOLEAN DEFAULT TRUE,
    can_download BOOLEAN DEFAULT TRUE,

    -- Notification preferences
    notify_on_update BOOLEAN DEFAULT TRUE,
    notify_on_impact BOOLEAN DEFAULT TRUE,

    notes TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_project (project_id),
    INDEX idx_token (access_token),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Track stakeholder access/views
CREATE TABLE IF NOT EXISTS stakeholder_access_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stakeholder_id INT UNSIGNED NOT NULL,
    action ENUM('link_opened', 'document_viewed', 'document_downloaded', 'plan_viewed', 'impact_viewed', 'acknowledged') NOT NULL,
    resource_type VARCHAR(50) NULL,
    resource_id INT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stakeholder_id) REFERENCES project_stakeholders(id) ON DELETE CASCADE,
    INDEX idx_stakeholder (stakeholder_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
