<?php
ob_start();
?>

<div class="dashboard">
    <div class="container">
        <!-- Header -->
        <div class="dashboard-header">
            <div>
                <h1>Welcome back<?= $currentUser['first_name'] ? ', ' . htmlspecialchars($currentUser['first_name']) : '' ?>!</h1>
                <p class="text-muted">Here's what's happening with your projects</p>
            </div>
            <a href="/projects/create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
                New Project
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon bg-blue">
                    <i class="bi bi-folder2-open"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?= $stats['total_projects'] ?></span>
                    <span class="stat-label">Total Projects</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-green">
                    <i class="bi bi-play-circle"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?= $stats['active_projects'] ?></span>
                    <span class="stat-label">Active Projects</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-purple">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?= $stats['total_stakeholders'] ?></span>
                    <span class="stat-label">Stakeholders</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-orange">
                    <i class="bi bi-eye"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?= $stats['views_this_week'] ?></span>
                    <span class="stat-label">Views This Week</span>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Recent Projects -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Recent Projects</h2>
                    <a href="/projects" class="btn btn-sm btn-outline">View All</a>
                </div>

                <?php if (empty($recentProjects)): ?>
                    <div class="empty-state">
                        <i class="bi bi-folder2"></i>
                        <h3>No projects yet</h3>
                        <p>Create your first project to start sharing with stakeholders</p>
                        <a href="/projects/create" class="btn btn-primary">Create Project</a>
                    </div>
                <?php else: ?>
                    <div class="project-list">
                        <?php foreach ($recentProjects as $project): ?>
                            <a href="/projects/<?= $project['id'] ?>" class="project-card">
                                <div class="project-card-header">
                                    <span class="project-type">
                                        <?php
                                        $typeIcons = [
                                            'heating' => 'thermometer-half',
                                            'electricity' => 'lightning',
                                            'photovoltaics' => 'sun',
                                            'plumbing' => 'droplet',
                                            'renovation' => 'hammer',
                                            'other' => 'folder',
                                        ];
                                        $icon = $typeIcons[$project['project_type']] ?? 'folder';
                                        ?>
                                        <i class="bi bi-<?= $icon ?>"></i>
                                    </span>
                                    <span class="badge badge-<?= $project['status'] === 'in_progress' ? 'success' : ($project['status'] === 'completed' ? 'secondary' : 'primary') ?>">
                                        <?= ucfirst(str_replace('_', ' ', $project['status'])) ?>
                                    </span>
                                </div>
                                <h3><?= htmlspecialchars($project['name']) ?></h3>
                                <?php if ($project['reference_number']): ?>
                                    <p class="project-ref">#<?= htmlspecialchars($project['reference_number']) ?></p>
                                <?php endif; ?>
                                <div class="project-meta">
                                    <span><i class="bi bi-people"></i> <?= $project['stakeholder_count'] ?></span>
                                    <span><i class="bi bi-file-earmark"></i> <?= $project['file_count'] ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Activity & Pending -->
            <div class="dashboard-sidebar">
                <!-- Pending Acknowledgements -->
                <?php if (!empty($pendingAcknowledgements)): ?>
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2>Pending Acknowledgements</h2>
                        </div>
                        <div class="pending-list">
                            <?php foreach ($pendingAcknowledgements as $doc): ?>
                                <div class="pending-item">
                                    <div class="pending-icon">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>
                                    <div class="pending-content">
                                        <strong><?= htmlspecialchars($doc['original_name']) ?></strong>
                                        <p>
                                            <span class="text-muted"><?= htmlspecialchars($doc['project_name']) ?></span>
                                            <span class="text-warning">
                                                <i class="bi bi-clock"></i> Awaiting: <?= htmlspecialchars($doc['stakeholder_name']) ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Recent Activity -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Recent Activity</h2>
                    </div>
                    <?php if (empty($recentActivity)): ?>
                        <p class="text-muted text-center py-4">No recent activity</p>
                    <?php else: ?>
                        <div class="activity-feed">
                            <?php foreach ($recentActivity as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-dot"></div>
                                    <div class="activity-content">
                                        <p>
                                            <?php
                                            $actorName = trim(($activity['first_name'] ?? '') . ' ' . ($activity['last_name'] ?? '')) ?: ($activity['email'] ?? 'Someone');
                                            echo htmlspecialchars($actorName);
                                            echo ' ' . htmlspecialchars($activity['action']);
                                            if ($activity['project_name']) {
                                                echo ' in <strong>' . htmlspecialchars($activity['project_name']) . '</strong>';
                                            }
                                            ?>
                                        </p>
                                        <span class="activity-time">
                                            <?= $this->timeAgo($activity['created_at']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard {
    padding: var(--spacing-6) 0;
    min-height: calc(100vh - 60px);
    background: var(--color-gray-50);
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-6);
    flex-wrap: wrap;
}

.dashboard-header h1 {
    margin-bottom: var(--spacing-1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-6);
}

@media (min-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.stat-card {
    background: var(--color-white);
    border-radius: var(--radius-xl);
    padding: var(--spacing-5);
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--color-gray-200);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-xl);
    flex-shrink: 0;
}

.stat-icon.bg-blue { background: #dbeafe; color: #2563eb; }
.stat-icon.bg-green { background: #dcfce7; color: #16a34a; }
.stat-icon.bg-purple { background: #ede9fe; color: #7c3aed; }
.stat-icon.bg-orange { background: #ffedd5; color: #ea580c; }

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: var(--font-size-2xl);
    font-weight: 700;
    color: var(--color-gray-900);
    line-height: 1.2;
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

.dashboard-grid {
    display: grid;
    gap: var(--spacing-6);
}

@media (min-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 2fr 1fr;
    }
}

.dashboard-section {
    background: var(--color-white);
    border-radius: var(--radius-xl);
    padding: var(--spacing-5);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--color-gray-200);
}

.dashboard-sidebar {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-6);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-4);
}

.section-header h2 {
    font-size: var(--font-size-lg);
    margin: 0;
}

.empty-state {
    text-align: center;
    padding: var(--spacing-8) var(--spacing-4);
}

.empty-state i {
    font-size: 48px;
    color: var(--color-gray-300);
    margin-bottom: var(--spacing-4);
}

.empty-state h3 {
    margin-bottom: var(--spacing-2);
}

.empty-state p {
    color: var(--color-gray-500);
    margin-bottom: var(--spacing-4);
}

.project-list {
    display: grid;
    gap: var(--spacing-4);
}

.project-card {
    display: block;
    padding: var(--spacing-4);
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-lg);
    transition: all var(--transition-fast);
    color: inherit;
}

.project-card:hover {
    border-color: var(--color-primary);
    box-shadow: var(--shadow-md);
    color: inherit;
}

.project-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-2);
}

.project-type {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-gray-100);
    border-radius: var(--radius-md);
    color: var(--color-gray-600);
}

.project-card h3 {
    font-size: var(--font-size-base);
    margin-bottom: var(--spacing-1);
}

.project-ref {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
    margin-bottom: var(--spacing-3);
}

.project-meta {
    display: flex;
    gap: var(--spacing-4);
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

.project-meta span {
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
}

.pending-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
}

.pending-item {
    display: flex;
    gap: var(--spacing-3);
    padding: var(--spacing-3);
    background: var(--color-gray-50);
    border-radius: var(--radius-lg);
}

.pending-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-white);
    border-radius: var(--radius-md);
    color: var(--color-gray-500);
    flex-shrink: 0;
}

.pending-content strong {
    display: block;
    font-size: var(--font-size-sm);
    margin-bottom: 2px;
}

.pending-content p {
    font-size: var(--font-size-xs);
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.activity-feed {
    display: flex;
    flex-direction: column;
}

.activity-item {
    display: flex;
    gap: var(--spacing-3);
    padding: var(--spacing-3) 0;
    border-bottom: 1px solid var(--color-gray-100);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-dot {
    width: 8px;
    height: 8px;
    background: var(--color-primary);
    border-radius: 50%;
    margin-top: 6px;
    flex-shrink: 0;
}

.activity-content p {
    font-size: var(--font-size-sm);
    margin-bottom: var(--spacing-1);
}

.activity-time {
    font-size: var(--font-size-xs);
    color: var(--color-gray-400);
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';

// Helper function for time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';

    return date('M j', $time);
}
