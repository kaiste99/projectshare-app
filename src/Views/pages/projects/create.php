<?php
ob_start();
?>

<div class="page-form">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/projects">Projects</a>
            <i class="bi bi-chevron-right"></i>
            <span>New Project</span>
        </nav>

        <div class="form-card">
            <div class="form-header">
                <h1>Create New Project</h1>
                <p>Set up a new project to share with your stakeholders</p>
            </div>

            <form action="/projects/store" method="POST" class="project-form">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                <!-- Basic Info -->
                <div class="form-section">
                    <h2>Project Details</h2>

                    <div class="form-group">
                        <label for="name" class="form-label">Project Name <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control"
                            placeholder="e.g., Heat Pump Installation - Müller Residence"
                            required
                        >
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="reference_number" class="form-label">Reference Number</label>
                            <input
                                type="text"
                                id="reference_number"
                                name="reference_number"
                                class="form-control"
                                placeholder="e.g., PRJ-2024-001"
                            >
                        </div>
                        <div class="form-group">
                            <label for="project_type" class="form-label">Project Type</label>
                            <select id="project_type" name="project_type" class="form-control">
                                <option value="heating">Heating / HVAC</option>
                                <option value="electricity">Electrical</option>
                                <option value="photovoltaics">Solar / Photovoltaics</option>
                                <option value="plumbing">Plumbing</option>
                                <option value="renovation">Renovation</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            class="form-control"
                            rows="3"
                            placeholder="Brief description of the project..."
                        ></textarea>
                    </div>
                </div>

                <!-- Location -->
                <div class="form-section">
                    <h2>Project Location</h2>

                    <div class="form-group">
                        <label for="address_line1" class="form-label">Address</label>
                        <input
                            type="text"
                            id="address_line1"
                            name="address_line1"
                            class="form-control"
                            placeholder="Street address"
                        >
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="postal_code" class="form-label">Postal Code</label>
                            <input
                                type="text"
                                id="postal_code"
                                name="postal_code"
                                class="form-control"
                                placeholder="e.g., 80331"
                            >
                        </div>
                        <div class="form-group">
                            <label for="city" class="form-label">City</label>
                            <input
                                type="text"
                                id="city"
                                name="city"
                                class="form-control"
                                placeholder="e.g., Munich"
                            >
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="form-section">
                    <h2>Timeline</h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="planned_start_date" class="form-label">Planned Start Date</label>
                            <input
                                type="date"
                                id="planned_start_date"
                                name="planned_start_date"
                                class="form-control"
                            >
                        </div>
                        <div class="form-group">
                            <label for="planned_end_date" class="form-label">Planned End Date</label>
                            <input
                                type="date"
                                id="planned_end_date"
                                name="planned_end_date"
                                class="form-control"
                            >
                        </div>
                    </div>
                </div>

                <!-- Assignment -->
                <div class="form-section">
                    <h2>Assignment</h2>

                    <div class="form-group">
                        <label for="project_manager_id" class="form-label">Project Manager</label>
                        <select id="project_manager_id" name="project_manager_id" class="form-control">
                            <option value="">Select team member...</option>
                            <?php foreach ($teamMembers as $member): ?>
                                <option value="<?= $member['id'] ?>">
                                    <?= htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '') ?: $member['email']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/projects" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        Create Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.page-form {
    padding: var(--spacing-6) 0;
    min-height: calc(100vh - 60px);
    background: var(--color-gray-50);
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
    margin-bottom: var(--spacing-4);
}

.breadcrumb a:hover {
    color: var(--color-primary);
}

.breadcrumb i {
    font-size: var(--font-size-xs);
}

.form-card {
    max-width: 720px;
    background: var(--color-white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--color-gray-200);
    overflow: hidden;
}

.form-header {
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--color-gray-200);
    background: var(--color-gray-50);
}

.form-header h1 {
    font-size: var(--font-size-xl);
    margin-bottom: var(--spacing-1);
}

.form-header p {
    color: var(--color-gray-500);
    margin: 0;
}

.project-form {
    padding: var(--spacing-6);
}

.form-section {
    margin-bottom: var(--spacing-8);
}

.form-section:last-of-type {
    margin-bottom: 0;
}

.form-section h2 {
    font-size: var(--font-size-base);
    font-weight: 600;
    color: var(--color-gray-700);
    margin-bottom: var(--spacing-4);
    padding-bottom: var(--spacing-2);
    border-bottom: 1px solid var(--color-gray-200);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-4);
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-3);
    padding-top: var(--spacing-6);
    border-top: 1px solid var(--color-gray-200);
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
