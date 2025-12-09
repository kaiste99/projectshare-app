<?php
ob_start();
?>

<div class="page-projects">
    <div class="container">
        <!-- Header -->
        <div class="page-header">
            <div>
                <h1>Projects</h1>
                <p class="text-muted">Manage your projects and stakeholder communication</p>
            </div>
            <a href="/projects/create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
                New Project
            </a>
        </div>

        <!-- Filters -->
        <div class="filters-bar">
            <div class="filter-tabs">
                <a href="/projects?status=all" class="filter-tab <?= $currentStatus === 'all' ? 'active' : '' ?>">
                    All
                </a>
                <a href="/projects?status=draft" class="filter-tab <?= $currentStatus === 'draft' ? 'active' : '' ?>">
                    Draft
                </a>
                <a href="/projects?status=planning" class="filter-tab <?= $currentStatus === 'planning' ? 'active' : '' ?>">
                    Planning
                </a>
                <a href="/projects?status=in_progress" class="filter-tab <?= $currentStatus === 'in_progress' ? 'active' : '' ?>">
                    In Progress
                </a>
                <a href="/projects?status=completed" class="filter-tab <?= $currentStatus === 'completed' ? 'active' : '' ?>">
                    Completed
                </a>
            </div>
            <form class="search-form" method="GET">
                <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">
                <div class="search-input-wrapper">
                    <i class="bi bi-search"></i>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search projects..."
                        value="<?= htmlspecialchars($search) ?>"
                    >
                </div>
            </form>
        </div>

        <!-- Projects Grid -->
        <?php if (empty($projects)): ?>
            <div class="empty-state card">
                <i class="bi bi-folder2"></i>
                <h3>No projects found</h3>
                <p>
                    <?php if ($search): ?>
                        No projects match your search. Try different keywords.
                    <?php else: ?>
                        Create your first project to start sharing with stakeholders.
                    <?php endif; ?>
                </p>
                <?php if (!$search): ?>
                    <a href="/projects/create" class="btn btn-primary">Create Project</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-card card">
                        <div class="project-card-header">
                            <div class="project-type-badge">
                                <?php
                                $typeIcons = [
                                    'heating' => 'thermometer-half',
                                    'electricity' => 'lightning',
                                    'photovoltaics' => 'sun',
                                    'plumbing' => 'droplet',
                                    'renovation' => 'hammer',
                                    'other' => 'folder',
                                ];
                                $typeColors = [
                                    'heating' => 'orange',
                                    'electricity' => 'yellow',
                                    'photovoltaics' => 'green',
                                    'plumbing' => 'blue',
                                    'renovation' => 'purple',
                                    'other' => 'gray',
                                ];
                                $icon = $typeIcons[$project['project_type']] ?? 'folder';
                                $color = $typeColors[$project['project_type']] ?? 'gray';
                                ?>
                                <span class="type-icon type-<?= $color ?>">
                                    <i class="bi bi-<?= $icon ?>"></i>
                                </span>
                            </div>
                            <div class="project-status">
                                <?php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'planning' => 'info',
                                    'in_progress' => 'success',
                                    'on_hold' => 'warning',
                                    'completed' => 'primary',
                                    'cancelled' => 'danger',
                                ];
                                $statusColor = $statusColors[$project['status']] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $statusColor ?>">
                                    <?= ucfirst(str_replace('_', ' ', $project['status'])) ?>
                                </span>
                            </div>
                        </div>

                        <a href="/projects/<?= $project['id'] ?>" class="project-card-body">
                            <h3><?= htmlspecialchars($project['name']) ?></h3>
                            <?php if ($project['reference_number']): ?>
                                <p class="project-ref">#<?= htmlspecialchars($project['reference_number']) ?></p>
                            <?php endif; ?>
                            <?php if ($project['city']): ?>
                                <p class="project-location">
                                    <i class="bi bi-geo-alt"></i>
                                    <?= htmlspecialchars($project['city']) ?>
                                </p>
                            <?php endif; ?>
                        </a>

                        <div class="project-card-footer">
                            <div class="project-stats">
                                <span title="Stakeholders">
                                    <i class="bi bi-people"></i>
                                    <?= $project['stakeholder_count'] ?>
                                </span>
                                <span title="Files">
                                    <i class="bi bi-file-earmark"></i>
                                    <?= $project['file_count'] ?>
                                </span>
                            </div>
                            <?php if ($project['planned_end_date']): ?>
                                <span class="project-date">
                                    <i class="bi bi-calendar"></i>
                                    <?= date('M j, Y', strtotime($project['planned_end_date'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.page-projects {
    padding: var(--spacing-6) 0;
    min-height: calc(100vh - 60px);
    background: var(--color-gray-50);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-6);
    flex-wrap: wrap;
}

.page-header h1 {
    margin-bottom: var(--spacing-1);
}

.filters-bar {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-6);
    background: var(--color-white);
    padding: var(--spacing-4);
    border-radius: var(--radius-xl);
    border: 1px solid var(--color-gray-200);
}

@media (min-width: 768px) {
    .filters-bar {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

.filter-tabs {
    display: flex;
    gap: var(--spacing-1);
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.filter-tab {
    padding: var(--spacing-2) var(--spacing-4);
    color: var(--color-gray-600);
    font-size: var(--font-size-sm);
    font-weight: 500;
    border-radius: var(--radius-md);
    white-space: nowrap;
    transition: all var(--transition-fast);
}

.filter-tab:hover {
    color: var(--color-gray-900);
    background: var(--color-gray-100);
}

.filter-tab.active {
    color: var(--color-primary);
    background: #dbeafe;
}

.search-form {
    width: 100%;
}

@media (min-width: 768px) {
    .search-form {
        width: 280px;
    }
}

.search-input-wrapper {
    position: relative;
}

.search-input-wrapper i {
    position: absolute;
    left: var(--spacing-3);
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-gray-400);
}

.search-input-wrapper .form-control {
    padding-left: var(--spacing-10);
}

.projects-grid {
    display: grid;
    gap: var(--spacing-4);
}

@media (min-width: 640px) {
    .projects-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .projects-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.project-card {
    display: flex;
    flex-direction: column;
    transition: all var(--transition-fast);
}

.project-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.project-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-4) var(--spacing-4) 0;
}

.type-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-lg);
    font-size: var(--font-size-lg);
}

.type-icon.type-orange { background: #ffedd5; color: #ea580c; }
.type-icon.type-yellow { background: #fef3c7; color: #d97706; }
.type-icon.type-green { background: #dcfce7; color: #16a34a; }
.type-icon.type-blue { background: #dbeafe; color: #2563eb; }
.type-icon.type-purple { background: #ede9fe; color: #7c3aed; }
.type-icon.type-gray { background: var(--color-gray-100); color: var(--color-gray-600); }

.project-card-body {
    flex: 1;
    padding: var(--spacing-4);
    color: inherit;
}

.project-card-body:hover {
    color: inherit;
}

.project-card-body h3 {
    font-size: var(--font-size-lg);
    margin-bottom: var(--spacing-2);
}

.project-ref {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
    margin-bottom: var(--spacing-2);
}

.project-location {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
    margin: 0;
}

.project-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-3) var(--spacing-4);
    border-top: 1px solid var(--color-gray-100);
    background: var(--color-gray-50);
}

.project-stats {
    display: flex;
    gap: var(--spacing-4);
}

.project-stats span {
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

.project-date {
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
}

.empty-state {
    text-align: center;
    padding: var(--spacing-12) var(--spacing-6);
}

.empty-state i {
    font-size: 64px;
    color: var(--color-gray-300);
    margin-bottom: var(--spacing-4);
}

.empty-state h3 {
    margin-bottom: var(--spacing-2);
}

.empty-state p {
    color: var(--color-gray-500);
    margin-bottom: var(--spacing-6);
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
