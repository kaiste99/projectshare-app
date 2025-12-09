-- Migration: Create Project Feedback Table
-- Version: 007

-- Project closing feedback
CREATE TABLE IF NOT EXISTS project_feedback (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    stakeholder_id INT UNSIGNED NULL,
    user_id INT UNSIGNED NULL,

    -- Feedback type
    feedback_type ENUM('project_closing', 'mid_project', 'complaint', 'praise') DEFAULT 'project_closing',

    -- Ratings (1-5 scale)
    overall_rating TINYINT UNSIGNED NULL,
    communication_rating TINYINT UNSIGNED NULL,
    quality_rating TINYINT UNSIGNED NULL,
    timeliness_rating TINYINT UNSIGNED NULL,
    professionalism_rating TINYINT UNSIGNED NULL,

    -- Qualitative feedback
    what_went_well TEXT NULL,
    what_could_improve TEXT NULL,
    additional_comments TEXT NULL,

    -- Would recommend
    would_recommend BOOLEAN NULL,
    recommend_score TINYINT UNSIGNED NULL, -- NPS 0-10

    -- Testimonial
    allow_testimonial BOOLEAN DEFAULT FALSE,
    testimonial_text TEXT NULL,

    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (stakeholder_id) REFERENCES project_stakeholders(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_project (project_id),
    INDEX idx_type (feedback_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feedback requests sent to stakeholders
CREATE TABLE IF NOT EXISTS feedback_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    stakeholder_id INT UNSIGNED NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    sent_at TIMESTAMP NULL,
    opened_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    reminder_sent_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (stakeholder_id) REFERENCES project_stakeholders(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity log for audit trail
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    stakeholder_id INT UNSIGNED NULL,
    account_id INT UNSIGNED NULL,
    project_id INT UNSIGNED NULL,

    action VARCHAR(100) NOT NULL,
    resource_type VARCHAR(50) NULL,
    resource_id INT UNSIGNED NULL,
    description TEXT NULL,
    metadata JSON NULL,

    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (stakeholder_id) REFERENCES project_stakeholders(id) ON DELETE SET NULL,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_project (project_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
