<?php
ob_start();
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

<div class="project-detail">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="/projects">Projects</a>
            <i class="bi bi-chevron-right"></i>
            <span><?= htmlspecialchars($project['name']) ?></span>
        </nav>

        <!-- Project Header -->
        <div class="project-header card">
            <div class="project-header-main">
                <div class="project-title-section">
                    <h1><?= htmlspecialchars($project['name']) ?></h1>
                    <?php if ($project['reference_number']): ?>
                        <span class="project-ref">#<?= htmlspecialchars($project['reference_number']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="project-actions">
                    <a href="/projects/<?= $project['id'] ?>/edit" class="btn btn-outline">
                        <i class="bi bi-pencil"></i>
                        Edit
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" id="statusDropdown">
                            <span class="badge badge-<?= $statusColor ?>">
                                <?= ucfirst(str_replace('_', ' ', $project['status'])) ?>
                            </span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu" id="statusMenu">
                            <button class="dropdown-item" data-status="draft">Draft</button>
                            <button class="dropdown-item" data-status="planning">Planning</button>
                            <button class="dropdown-item" data-status="in_progress">In Progress</button>
                            <button class="dropdown-item" data-status="on_hold">On Hold</button>
                            <button class="dropdown-item" data-status="completed">Completed</button>
                            <button class="dropdown-item text-danger" data-status="cancelled">Cancelled</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($project['description']): ?>
                <p class="project-description"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
            <?php endif; ?>

            <div class="project-meta-grid">
                <?php if ($project['city']): ?>
                    <div class="meta-item">
                        <i class="bi bi-geo-alt"></i>
                        <span>
                            <?= htmlspecialchars($project['address_line1'] ?? '') ?>
                            <?php if ($project['city']): ?>
                                <?= $project['address_line1'] ? ', ' : '' ?><?= htmlspecialchars($project['city']) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>
                <?php if ($project['planned_start_date'] || $project['planned_end_date']): ?>
                    <div class="meta-item">
                        <i class="bi bi-calendar"></i>
                        <span>
                            <?php if ($project['planned_start_date']): ?>
                                <?= date('M j, Y', strtotime($project['planned_start_date'])) ?>
                            <?php endif; ?>
                            <?php if ($project['planned_end_date']): ?>
                                - <?= date('M j, Y', strtotime($project['planned_end_date'])) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>
                <?php if ($project['pm_first_name']): ?>
                    <div class="meta-item">
                        <i class="bi bi-person"></i>
                        <span>Manager: <?= htmlspecialchars($project['pm_first_name'] . ' ' . $project['pm_last_name']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="project-tabs">
            <a href="/projects/<?= $project['id'] ?>" class="tab active">Overview</a>
            <a href="/projects/<?= $project['id'] ?>/plan" class="tab">Project Plan</a>
            <a href="/projects/<?= $project['id'] ?>/stakeholders" class="tab">Stakeholders</a>
            <a href="/projects/<?= $project['id'] ?>/files" class="tab">Files</a>
            <a href="/projects/<?= $project['id'] ?>/feedback" class="tab">Feedback</a>
        </div>

        <div class="project-content">
            <div class="content-main">
                <!-- Tasks Overview -->
                <div class="card">
                    <div class="card-header">
                        <h2>Project Plan</h2>
                        <a href="/projects/<?= $project['id'] ?>/plan" class="btn btn-sm btn-outline">Manage Plan</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tasks)): ?>
                            <div class="empty-state-sm">
                                <i class="bi bi-list-task"></i>
                                <p>No tasks yet. Add tasks to your project plan.</p>
                                <a href="/projects/<?= $project['id'] ?>/plan" class="btn btn-primary btn-sm">Add Tasks</a>
                            </div>
                        <?php else: ?>
                            <div class="task-list">
                                <?php foreach (array_slice($tasks, 0, 5) as $task): ?>
                                    <div class="task-item">
                                        <div class="task-status">
                                            <?php
                                            $taskStatusIcons = [
                                                'pending' => 'circle',
                                                'in_progress' => 'play-circle-fill',
                                                'completed' => 'check-circle-fill',
                                                'cancelled' => 'x-circle-fill',
                                                'delayed' => 'exclamation-circle-fill',
                                            ];
                                            $taskStatusColors = [
                                                'pending' => 'gray',
                                                'in_progress' => 'blue',
                                                'completed' => 'green',
                                                'cancelled' => 'red',
                                                'delayed' => 'orange',
                                            ];
                                            ?>
                                            <i class="bi bi-<?= $taskStatusIcons[$task['status']] ?? 'circle' ?> text-<?= $taskStatusColors[$task['status']] ?? 'gray' ?>"></i>
                                        </div>
                                        <div class="task-content">
                                            <span class="task-title"><?= htmlspecialchars($task['title']) ?></span>
                                            <?php if ($task['planned_start_date']): ?>
                                                <span class="task-date">
                                                    <?= date('M j', strtotime($task['planned_start_date'])) ?>
                                                    <?php if ($task['planned_end_date']): ?>
                                                        - <?= date('M j', strtotime($task['planned_end_date'])) ?>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($task['impact_types']): ?>
                                            <span class="badge badge-warning" title="Has impacts">
                                                <i class="bi bi-exclamation-triangle"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($tasks) > 5): ?>
                                <div class="mt-4 text-center">
                                    <a href="/projects/<?= $project['id'] ?>/plan" class="btn btn-outline btn-sm">
                                        View All <?= count($tasks) ?> Tasks
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Files -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h2>Recent Files</h2>
                        <a href="/projects/<?= $project['id'] ?>/files" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentFiles)): ?>
                            <div class="empty-state-sm">
                                <i class="bi bi-file-earmark"></i>
                                <p>No files uploaded yet.</p>
                                <a href="/projects/<?= $project['id'] ?>/files" class="btn btn-primary btn-sm">Upload Files</a>
                            </div>
                        <?php else: ?>
                            <div class="file-list">
                                <?php foreach ($recentFiles as $file): ?>
                                    <div class="file-item">
                                        <div class="file-icon">
                                            <?php
                                            $fileIcons = [
                                                'pdf' => 'file-earmark-pdf',
                                                'doc' => 'file-earmark-word',
                                                'docx' => 'file-earmark-word',
                                                'xls' => 'file-earmark-excel',
                                                'xlsx' => 'file-earmark-excel',
                                                'jpg' => 'file-earmark-image',
                                                'jpeg' => 'file-earmark-image',
                                                'png' => 'file-earmark-image',
                                            ];
                                            $icon = $fileIcons[$file['file_extension']] ?? 'file-earmark';
                                            ?>
                                            <i class="bi bi-<?= $icon ?>"></i>
                                        </div>
                                        <div class="file-info">
                                            <span class="file-name"><?= htmlspecialchars($file['original_name']) ?></span>
                                            <span class="file-meta">
                                                <?= number_format($file['file_size'] / 1024, 0) ?> KB
                                                &bull;
                                                <?= $file['view_count'] ?> views
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="content-sidebar">
                <!-- Stakeholders -->
                <div class="card">
                    <div class="card-header">
                        <h2>Stakeholders</h2>
                        <a href="/projects/<?= $project['id'] ?>/stakeholders" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus"></i> Add
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($stakeholders)): ?>
                            <p class="text-muted text-center py-4">No stakeholders added yet</p>
                        <?php else: ?>
                            <div class="stakeholder-list">
                                <?php foreach ($stakeholders as $stakeholder): ?>
                                    <div class="stakeholder-item">
                                        <div class="stakeholder-avatar">
                                            <?= strtoupper(substr($stakeholder['name'], 0, 1)) ?>
                                        </div>
                                        <div class="stakeholder-info">
                                            <span class="stakeholder-name"><?= htmlspecialchars($stakeholder['name']) ?></span>
                                            <span class="stakeholder-type">
                                                <?= ucfirst(str_replace('_', ' ', $stakeholder['stakeholder_type'])) ?>
                                            </span>
                                        </div>
                                        <?php if ($stakeholder['last_viewed']): ?>
                                            <span class="stakeholder-status text-success" title="Last viewed: <?= date('M j, Y', strtotime($stakeholder['last_viewed'])) ?>">
                                                <i class="bi bi-check-circle-fill"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="stakeholder-status text-muted" title="Never viewed">
                                                <i class="bi bi-clock"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h2>Quick Actions</h2>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="/projects/<?= $project['id'] ?>/stakeholders" class="quick-action">
                                <i class="bi bi-person-plus"></i>
                                <span>Add Stakeholder</span>
                            </a>
                            <a href="/projects/<?= $project['id'] ?>/files" class="quick-action">
                                <i class="bi bi-upload"></i>
                                <span>Upload File</span>
                            </a>
                            <a href="/projects/<?= $project['id'] ?>/plan" class="quick-action">
                                <i class="bi bi-bell"></i>
                                <span>Notify Changes</span>
                            </a>
                            <?php if ($project['status'] === 'completed'): ?>
                                <a href="/projects/<?= $project['id'] ?>/feedback" class="quick-action">
                                    <i class="bi bi-chat-square-text"></i>
                                    <span>Request Feedback</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.project-detail {
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

.project-header {
    margin-bottom: var(--spacing-6);
}

.project-header-main {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--spacing-4);
    flex-wrap: wrap;
    margin-bottom: var(--spacing-4);
}

.project-title-section h1 {
    margin-bottom: var(--spacing-1);
}

.project-ref {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

.project-actions {
    display: flex;
    gap: var(--spacing-2);
}

.project-description {
    color: var(--color-gray-600);
    margin-bottom: var(--spacing-4);
}

.project-meta-grid {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-4) var(--spacing-6);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--color-gray-600);
}

.meta-item i {
    color: var(--color-gray-400);
}

.project-tabs {
    display: flex;
    gap: var(--spacing-1);
    margin-bottom: var(--spacing-6);
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    background: var(--color-white);
    padding: var(--spacing-2);
    border-radius: var(--radius-xl);
    border: 1px solid var(--color-gray-200);
}

.project-tabs .tab {
    padding: var(--spacing-2) var(--spacing-4);
    font-size: var(--font-size-sm);
    font-weight: 500;
    color: var(--color-gray-600);
    border-radius: var(--radius-lg);
    white-space: nowrap;
    transition: all var(--transition-fast);
}

.project-tabs .tab:hover {
    color: var(--color-gray-900);
    background: var(--color-gray-100);
}

.project-tabs .tab.active {
    color: var(--color-primary);
    background: #dbeafe;
}

.project-content {
    display: grid;
    gap: var(--spacing-6);
}

@media (min-width: 1024px) {
    .project-content {
        grid-template-columns: 1fr 350px;
    }
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    font-size: var(--font-size-base);
    margin: 0;
}

.empty-state-sm {
    text-align: center;
    padding: var(--spacing-6);
}

.empty-state-sm i {
    font-size: 32px;
    color: var(--color-gray-300);
    margin-bottom: var(--spacing-2);
}

.empty-state-sm p {
    color: var(--color-gray-500);
    margin-bottom: var(--spacing-3);
}

.task-list {
    display: flex;
    flex-direction: column;
}

.task-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding: var(--spacing-3) 0;
    border-bottom: 1px solid var(--color-gray-100);
}

.task-item:last-child {
    border-bottom: none;
}

.task-status {
    font-size: var(--font-size-lg);
}

.text-gray { color: var(--color-gray-400); }
.text-blue { color: var(--color-primary); }
.text-green { color: var(--color-success); }
.text-red { color: var(--color-danger); }
.text-orange { color: var(--color-warning); }

.task-content {
    flex: 1;
    min-width: 0;
}

.task-title {
    display: block;
    font-weight: 500;
    margin-bottom: 2px;
}

.task-date {
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
}

.file-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.file-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding: var(--spacing-2);
    border-radius: var(--radius-md);
    transition: background var(--transition-fast);
}

.file-item:hover {
    background: var(--color-gray-50);
}

.file-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-gray-100);
    border-radius: var(--radius-md);
    color: var(--color-gray-500);
}

.file-info {
    flex: 1;
    min-width: 0;
}

.file-name {
    display: block;
    font-size: var(--font-size-sm);
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.file-meta {
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
}

.stakeholder-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.stakeholder-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.stakeholder-avatar {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-primary);
    color: var(--color-white);
    border-radius: var(--radius-full);
    font-size: var(--font-size-sm);
    font-weight: 600;
}

.stakeholder-info {
    flex: 1;
    min-width: 0;
}

.stakeholder-name {
    display: block;
    font-size: var(--font-size-sm);
    font-weight: 500;
}

.stakeholder-type {
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
}

.quick-actions {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.quick-action {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding: var(--spacing-3);
    color: var(--color-gray-700);
    border-radius: var(--radius-lg);
    transition: all var(--transition-fast);
}

.quick-action:hover {
    background: var(--color-gray-100);
    color: var(--color-primary);
}

.quick-action i {
    width: 20px;
    text-align: center;
}

.dropdown {
    position: relative;
}

.dropdown-toggle {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    min-width: 160px;
    background: var(--color-white);
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    padding: var(--spacing-2);
    margin-top: var(--spacing-2);
    z-index: 50;
}

.dropdown-menu.show {
    display: block;
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: var(--spacing-2) var(--spacing-3);
    text-align: left;
    color: var(--color-gray-700);
    background: none;
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    font-family: inherit;
    font-size: var(--font-size-sm);
}

.dropdown-item:hover {
    background: var(--color-gray-100);
}
</style>

<script>
// Status dropdown
const statusDropdown = document.getElementById('statusDropdown');
const statusMenu = document.getElementById('statusMenu');

statusDropdown?.addEventListener('click', () => {
    statusMenu.classList.toggle('show');
});

document.addEventListener('click', (e) => {
    if (!statusDropdown?.contains(e.target)) {
        statusMenu?.classList.remove('show');
    }
});

// Status change
statusMenu?.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', async () => {
        const status = item.dataset.status;

        const response = await fetch('/projects/<?= $project['id'] ?>/status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `status=${status}&_csrf=<?= htmlspecialchars($csrf) ?>`
        });

        if (response.ok) {
            window.location.reload();
        } else {
            alert('Failed to update status');
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
