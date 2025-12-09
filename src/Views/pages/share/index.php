<?php
ob_start();
?>

<div class="share-portal">
    <!-- Header -->
    <header class="share-header">
        <div class="container">
            <div class="share-header-content">
                <div class="company-info">
                    <?php if ($project['logo_path']): ?>
                        <img src="<?= htmlspecialchars($project['logo_path']) ?>" alt="Logo" class="company-logo">
                    <?php endif; ?>
                    <span class="company-name"><?= htmlspecialchars($project['company_name']) ?></span>
                </div>
                <div class="stakeholder-info">
                    <span class="welcome-text">Welcome, <?= htmlspecialchars($stakeholder['name']) ?></span>
                </div>
            </div>
        </div>
    </header>

    <main class="share-main">
        <div class="container">
            <!-- Project Info -->
            <div class="project-banner">
                <div class="project-banner-content">
                    <h1><?= htmlspecialchars($project['name']) ?></h1>
                    <?php if ($project['reference_number']): ?>
                        <span class="project-ref">Reference: #<?= htmlspecialchars($project['reference_number']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="project-status-badge">
                    <?php
                    $statusLabels = [
                        'draft' => 'Draft',
                        'planning' => 'Planning',
                        'in_progress' => 'In Progress',
                        'on_hold' => 'On Hold',
                        'completed' => 'Completed',
                    ];
                    ?>
                    <span class="badge badge-<?= $project['status'] === 'in_progress' ? 'success' : 'primary' ?>">
                        <?= $statusLabels[$project['status']] ?? 'Active' ?>
                    </span>
                </div>
            </div>

            <?php if ($project['description']): ?>
                <p class="project-description"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
            <?php endif; ?>

            <!-- Navigation -->
            <nav class="share-nav">
                <a href="/share/<?= $token ?>" class="share-nav-item active">
                    <i class="bi bi-house"></i>
                    <span>Overview</span>
                </a>
                <?php if ($stakeholder['can_view_schedule']): ?>
                    <a href="/share/<?= $token ?>/plan" class="share-nav-item">
                        <i class="bi bi-calendar3"></i>
                        <span>Schedule</span>
                    </a>
                <?php endif; ?>
                <?php if ($stakeholder['can_view_documents']): ?>
                    <a href="/share/<?= $token ?>/files" class="share-nav-item">
                        <i class="bi bi-file-earmark"></i>
                        <span>Documents</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="share-content">
                <!-- Important Impacts -->
                <?php if (!empty($impacts) && $stakeholder['can_view_impacts']): ?>
                    <section class="share-section impacts-section">
                        <h2>
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                            Important Information
                        </h2>
                        <p class="section-description">The following service interruptions may affect you:</p>

                        <div class="impact-cards">
                            <?php foreach ($impacts as $impact): ?>
                                <div class="impact-card impact-<?= $impact['severity'] ?>">
                                    <div class="impact-header">
                                        <?php
                                        $impactIcons = [
                                            'electricity_interruption' => 'lightning',
                                            'water_interruption' => 'droplet',
                                            'heating_interruption' => 'thermometer-low',
                                            'noise' => 'volume-up',
                                            'access_restriction' => 'door-closed',
                                            'evacuation' => 'box-arrow-right',
                                            'other' => 'info-circle',
                                        ];
                                        $icon = $impactIcons[$impact['impact_type']] ?? 'info-circle';
                                        ?>
                                        <div class="impact-icon">
                                            <i class="bi bi-<?= $icon ?>"></i>
                                        </div>
                                        <div class="impact-title">
                                            <h3><?= htmlspecialchars($impact['title']) ?></h3>
                                            <span class="impact-type"><?= ucfirst(str_replace('_', ' ', $impact['impact_type'])) ?></span>
                                        </div>
                                        <span class="badge badge-<?= $impact['severity'] === 'critical' ? 'danger' : ($impact['severity'] === 'high' ? 'warning' : 'info') ?>">
                                            <?= ucfirst($impact['severity']) ?>
                                        </span>
                                    </div>

                                    <?php if ($impact['description']): ?>
                                        <p class="impact-description"><?= nl2br(htmlspecialchars($impact['description'])) ?></p>
                                    <?php endif; ?>

                                    <div class="impact-timing">
                                        <i class="bi bi-clock"></i>
                                        <?php if ($impact['impact_start_datetime']): ?>
                                            <?= date('l, M j, Y - H:i', strtotime($impact['impact_start_datetime'])) ?>
                                            <?php if ($impact['impact_end_datetime']): ?>
                                                to <?= date('H:i', strtotime($impact['impact_end_datetime'])) ?>
                                            <?php endif; ?>
                                        <?php elseif ($impact['estimated_duration_hours']): ?>
                                            Approximately <?= $impact['estimated_duration_hours'] ?> hours
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($impact['preparation_instructions']): ?>
                                        <div class="impact-instructions">
                                            <h4><i class="bi bi-check2-square"></i> Before:</h4>
                                            <p><?= nl2br(htmlspecialchars($impact['preparation_instructions'])) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($impact['during_instructions']): ?>
                                        <div class="impact-instructions">
                                            <h4><i class="bi bi-hourglass-split"></i> During:</h4>
                                            <p><?= nl2br(htmlspecialchars($impact['during_instructions'])) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Upcoming Tasks -->
                <?php if (!empty($tasks) && $stakeholder['can_view_schedule']): ?>
                    <section class="share-section">
                        <h2>
                            <i class="bi bi-calendar-check"></i>
                            Project Schedule
                        </h2>
                        <div class="timeline">
                            <?php
                            $upcomingTasks = array_filter($tasks, fn($t) =>
                                $t['status'] !== 'completed' && $t['status'] !== 'cancelled'
                            );
                            $upcomingTasks = array_slice($upcomingTasks, 0, 5);
                            ?>
                            <?php foreach ($upcomingTasks as $task): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker">
                                        <?php if ($task['status'] === 'in_progress'): ?>
                                            <i class="bi bi-play-circle-fill text-primary"></i>
                                        <?php else: ?>
                                            <i class="bi bi-circle text-muted"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="timeline-content">
                                        <h4><?= htmlspecialchars($task['title']) ?></h4>
                                        <?php if ($task['planned_start_date']): ?>
                                            <span class="timeline-date">
                                                <?= date('M j', strtotime($task['planned_start_date'])) ?>
                                                <?php if ($task['planned_end_date'] && $task['planned_end_date'] !== $task['planned_start_date']): ?>
                                                    - <?= date('M j', strtotime($task['planned_end_date'])) ?>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($task['description']): ?>
                                            <p><?= htmlspecialchars($task['description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="/share/<?= $token ?>/plan" class="btn btn-outline btn-sm">
                            View Full Schedule <i class="bi bi-arrow-right"></i>
                        </a>
                    </section>
                <?php endif; ?>

                <!-- Recent Documents -->
                <?php if (!empty($files) && $stakeholder['can_view_documents']): ?>
                    <section class="share-section">
                        <h2>
                            <i class="bi bi-file-earmark-text"></i>
                            Documents
                        </h2>
                        <div class="document-list">
                            <?php foreach (array_slice($files, 0, 5) as $file): ?>
                                <div class="document-item">
                                    <div class="document-icon">
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
                                    <div class="document-info">
                                        <h4><?= htmlspecialchars($file['original_name']) ?></h4>
                                        <span class="document-meta">
                                            <?= strtoupper($file['file_extension']) ?>
                                            &bull;
                                            <?= number_format($file['file_size'] / 1024, 0) ?> KB
                                            &bull;
                                            <?= date('M j, Y', strtotime($file['created_at'])) ?>
                                        </span>
                                    </div>
                                    <div class="document-actions">
                                        <a href="/share/<?= $token ?>/file/<?= $file['id'] ?>" class="btn btn-sm btn-outline" target="_blank">
                                            <i class="bi bi-eye"></i>
                                            View
                                        </a>
                                        <?php if ($file['requires_acknowledgement']): ?>
                                            <span class="badge badge-warning">
                                                <i class="bi bi-check-circle"></i> Requires Acknowledgement
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($files) > 5): ?>
                            <a href="/share/<?= $token ?>/files" class="btn btn-outline btn-sm">
                                View All Documents <i class="bi bi-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <!-- Contact -->
                <section class="share-section contact-section">
                    <h2>
                        <i class="bi bi-question-circle"></i>
                        Questions?
                    </h2>
                    <p>If you have any questions about this project, please contact <?= htmlspecialchars($project['company_name']) ?>.</p>
                </section>
            </div>
        </div>
    </main>

    <footer class="share-footer">
        <div class="container">
            <p>Powered by <strong>ProjectShare</strong> &bull; Secure project communication</p>
        </div>
    </footer>
</div>

<style>
.share-portal {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: var(--color-gray-50);
}

.share-header {
    background: var(--color-white);
    border-bottom: 1px solid var(--color-gray-200);
    padding: var(--spacing-4) 0;
}

.share-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--spacing-4);
}

.company-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.company-logo {
    height: 40px;
    width: auto;
}

.company-name {
    font-weight: 600;
    font-size: var(--font-size-lg);
}

.welcome-text {
    color: var(--color-gray-600);
    font-size: var(--font-size-sm);
}

.share-main {
    flex: 1;
    padding: var(--spacing-6) 0;
}

.project-banner {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--spacing-4);
    background: var(--color-white);
    padding: var(--spacing-6);
    border-radius: var(--radius-xl);
    margin-bottom: var(--spacing-4);
    border: 1px solid var(--color-gray-200);
}

.project-banner h1 {
    margin-bottom: var(--spacing-1);
}

.project-ref {
    color: var(--color-gray-500);
    font-size: var(--font-size-sm);
}

.project-description {
    background: var(--color-white);
    padding: var(--spacing-4);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-4);
    color: var(--color-gray-600);
    border: 1px solid var(--color-gray-200);
}

.share-nav {
    display: flex;
    gap: var(--spacing-2);
    margin-bottom: var(--spacing-6);
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.share-nav-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    padding: var(--spacing-3) var(--spacing-5);
    background: var(--color-white);
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-lg);
    color: var(--color-gray-600);
    font-weight: 500;
    white-space: nowrap;
    transition: all var(--transition-fast);
}

.share-nav-item:hover {
    background: var(--color-gray-100);
    color: var(--color-gray-900);
}

.share-nav-item.active {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: var(--color-white);
}

.share-section {
    background: var(--color-white);
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-xl);
    padding: var(--spacing-6);
    margin-bottom: var(--spacing-6);
}

.share-section h2 {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-lg);
    margin-bottom: var(--spacing-4);
}

.section-description {
    color: var(--color-gray-600);
    margin-bottom: var(--spacing-4);
}

/* Impacts */
.impact-cards {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-4);
}

.impact-card {
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-lg);
    padding: var(--spacing-4);
    background: var(--color-white);
}

.impact-card.impact-critical {
    border-left: 4px solid var(--color-danger);
    background: #fef2f2;
}

.impact-card.impact-high {
    border-left: 4px solid var(--color-warning);
    background: #fffbeb;
}

.impact-header {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-3);
    margin-bottom: var(--spacing-3);
}

.impact-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-gray-100);
    border-radius: var(--radius-lg);
    font-size: var(--font-size-lg);
    color: var(--color-gray-600);
}

.impact-title {
    flex: 1;
}

.impact-title h3 {
    font-size: var(--font-size-base);
    margin-bottom: 2px;
}

.impact-type {
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
}

.impact-description {
    color: var(--color-gray-600);
    margin-bottom: var(--spacing-3);
}

.impact-timing {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    padding: var(--spacing-2) var(--spacing-3);
    background: var(--color-gray-100);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: 500;
    margin-bottom: var(--spacing-3);
}

.impact-instructions {
    padding: var(--spacing-3);
    background: var(--color-gray-50);
    border-radius: var(--radius-md);
    margin-top: var(--spacing-3);
}

.impact-instructions h4 {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-sm);
    margin-bottom: var(--spacing-2);
}

.impact-instructions p {
    font-size: var(--font-size-sm);
    color: var(--color-gray-600);
    margin: 0;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: var(--spacing-6);
    margin-bottom: var(--spacing-4);
}

.timeline::before {
    content: '';
    position: absolute;
    left: 9px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--color-gray-200);
}

.timeline-item {
    position: relative;
    padding-bottom: var(--spacing-4);
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -24px;
    top: 0;
    background: var(--color-white);
}

.timeline-content h4 {
    font-size: var(--font-size-base);
    margin-bottom: var(--spacing-1);
}

.timeline-date {
    display: inline-block;
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
    margin-bottom: var(--spacing-2);
}

.timeline-content p {
    font-size: var(--font-size-sm);
    color: var(--color-gray-600);
    margin: 0;
}

/* Documents */
.document-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
    margin-bottom: var(--spacing-4);
}

.document-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    padding: var(--spacing-3);
    background: var(--color-gray-50);
    border-radius: var(--radius-lg);
    flex-wrap: wrap;
}

.document-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-white);
    border-radius: var(--radius-md);
    color: var(--color-gray-500);
    font-size: var(--font-size-xl);
}

.document-info {
    flex: 1;
    min-width: 200px;
}

.document-info h4 {
    font-size: var(--font-size-sm);
    margin-bottom: 2px;
}

.document-meta {
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
}

.document-actions {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    flex-wrap: wrap;
}

/* Contact */
.contact-section {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

/* Footer */
.share-footer {
    background: var(--color-white);
    border-top: 1px solid var(--color-gray-200);
    padding: var(--spacing-4) 0;
    text-align: center;
}

.share-footer p {
    margin: 0;
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
