-- Migration: Create Attachments Table
-- Version: 006

CREATE TABLE IF NOT EXISTS attachments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NULL,
    task_id INT UNSIGNED NULL,

    -- File info
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_extension VARCHAR(20) NOT NULL,

    -- Metadata
    category ENUM('plan', 'contract', 'permit', 'photo', 'report', 'invoice', 'other') DEFAULT 'other',
    description TEXT NULL,
    is_visible_to_stakeholders BOOLEAN DEFAULT TRUE,
    requires_acknowledgement BOOLEAN DEFAULT FALSE,

    -- Versioning
    version INT UNSIGNED DEFAULT 1,
    previous_version_id INT UNSIGNED NULL,

    uploaded_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES project_plans(id) ON DELETE SET NULL,
    FOREIGN KEY (task_id) REFERENCES project_tasks(id) ON DELETE SET NULL,
    FOREIGN KEY (previous_version_id) REFERENCES attachments(id) ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_project (project_id),
    INDEX idx_plan (plan_id),
    INDEX idx_task (task_id),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Track document views and downloads
CREATE TABLE IF NOT EXISTS attachment_views (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attachment_id INT UNSIGNED NOT NULL,
    viewer_type ENUM('user', 'stakeholder') NOT NULL,
    viewer_id INT UNSIGNED NOT NULL,
    action ENUM('viewed', 'downloaded', 'acknowledged') NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attachment_id) REFERENCES attachments(id) ON DELETE CASCADE,
    INDEX idx_attachment (attachment_id),
    INDEX idx_viewer (viewer_type, viewer_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stakeholder acknowledgements for documents
CREATE TABLE IF NOT EXISTS document_acknowledgements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attachment_id INT UNSIGNED NOT NULL,
    stakeholder_id INT UNSIGNED NOT NULL,
    acknowledged_at TIMESTAMP NOT NULL,
    ip_address VARCHAR(45) NULL,
    signature_data TEXT NULL,
    notes TEXT NULL,
    FOREIGN KEY (attachment_id) REFERENCES attachments(id) ON DELETE CASCADE,
    FOREIGN KEY (stakeholder_id) REFERENCES project_stakeholders(id) ON DELETE CASCADE,
    UNIQUE KEY unique_doc_stakeholder (attachment_id, stakeholder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
