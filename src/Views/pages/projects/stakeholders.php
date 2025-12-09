<?php
ob_start();
?>

<div class="project-detail">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="/projects">Projects</a>
            <i class="bi bi-chevron-right"></i>
            <a href="/projects/<?= $project['id'] ?>"><?= htmlspecialchars($project['name']) ?></a>
            <i class="bi bi-chevron-right"></i>
            <span>Stakeholders</span>
        </nav>

        <!-- Project Tabs -->
        <div class="project-tabs">
            <a href="/projects/<?= $project['id'] ?>" class="tab">Overview</a>
            <a href="/projects/<?= $project['id'] ?>/plan" class="tab">Project Plan</a>
            <a href="/projects/<?= $project['id'] ?>/stakeholders" class="tab active">Stakeholders</a>
            <a href="/projects/<?= $project['id'] ?>/files" class="tab">Files</a>
            <a href="/projects/<?= $project['id'] ?>/feedback" class="tab">Feedback</a>
        </div>

        <div class="content-layout">
            <!-- Add Stakeholder Form -->
            <div class="card add-stakeholder-card">
                <div class="card-header">
                    <h2>Add Stakeholder</h2>
                </div>
                <div class="card-body">
                    <form action="/projects/<?= $project['id'] ?>/stakeholders/add" method="POST">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="stakeholder_type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select id="stakeholder_type" name="stakeholder_type" class="form-control" required>
                                    <option value="building_owner">Building Owner</option>
                                    <option value="tenant">Tenant</option>
                                    <option value="property_manager">Property Manager</option>
                                    <option value="subcontractor">Subcontractor</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" id="phone" name="phone" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" id="company_name" name="company_name" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea id="notes" name="notes" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="send_invitation" name="send_invitation" class="form-check-input" checked>
                                <label for="send_invitation" class="form-check-label">Send invitation email with share link</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i>
                            Add Stakeholder
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stakeholder List -->
            <div class="stakeholder-list-section">
                <h2>Stakeholders (<?= count($stakeholders) ?>)</h2>

                <?php if (empty($stakeholders)): ?>
                    <div class="card">
                        <div class="card-body text-center py-8">
                            <i class="bi bi-people" style="font-size: 48px; color: var(--color-gray-300);"></i>
                            <p class="mt-4 text-muted">No stakeholders added yet</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="stakeholders-grid">
                        <?php
                        $typeLabels = [
                            'building_owner' => ['icon' => 'house', 'label' => 'Building Owner'],
                            'tenant' => ['icon' => 'person', 'label' => 'Tenant'],
                            'property_manager' => ['icon' => 'building', 'label' => 'Property Manager'],
                            'subcontractor' => ['icon' => 'tools', 'label' => 'Subcontractor'],
                            'other' => ['icon' => 'person-badge', 'label' => 'Other'],
                        ];
                        ?>
                        <?php foreach ($stakeholders as $stakeholder): ?>
                            <?php $typeInfo = $typeLabels[$stakeholder['stakeholder_type']] ?? $typeLabels['other']; ?>
                            <div class="stakeholder-card card <?= !$stakeholder['is_active'] ? 'inactive' : '' ?>">
                                <div class="stakeholder-card-header">
                                    <div class="stakeholder-avatar">
                                        <i class="bi bi-<?= $typeInfo['icon'] ?>"></i>
                                    </div>
                                    <div class="stakeholder-info">
                                        <h3><?= htmlspecialchars($stakeholder['name']) ?></h3>
                                        <span class="stakeholder-type"><?= $typeInfo['label'] ?></span>
                                    </div>
                                    <?php if ($stakeholder['last_activity']): ?>
                                        <span class="badge badge-success" title="Last viewed: <?= date('M j, Y H:i', strtotime($stakeholder['last_activity'])) ?>">
                                            <i class="bi bi-eye"></i> <?= $stakeholder['total_views'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Not viewed</span>
                                    <?php endif; ?>
                                </div>

                                <div class="stakeholder-details">
                                    <?php if ($stakeholder['email']): ?>
                                        <div class="detail-row">
                                            <i class="bi bi-envelope"></i>
                                            <span><?= htmlspecialchars($stakeholder['email']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($stakeholder['phone']): ?>
                                        <div class="detail-row">
                                            <i class="bi bi-telephone"></i>
                                            <span><?= htmlspecialchars($stakeholder['phone']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($stakeholder['company_name']): ?>
                                        <div class="detail-row">
                                            <i class="bi bi-building"></i>
                                            <span><?= htmlspecialchars($stakeholder['company_name']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="stakeholder-link">
                                    <label class="form-label">Share Link</label>
                                    <div class="link-input-group">
                                        <input
                                            type="text"
                                            class="form-control"
                                            value="<?= htmlspecialchars(($_ENV['APP_URL'] ?? 'http://localhost:8000') . '/share/' . $stakeholder['access_token']) ?>"
                                            readonly
                                            id="link-<?= $stakeholder['id'] ?>"
                                        >
                                        <button
                                            type="button"
                                            class="btn btn-secondary"
                                            data-copy="<?= htmlspecialchars(($_ENV['APP_URL'] ?? 'http://localhost:8000') . '/share/' . $stakeholder['access_token']) ?>"
                                        >
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="stakeholder-actions">
                                    <a href="/share/<?= $stakeholder['access_token'] ?>" target="_blank" class="btn btn-sm btn-outline">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                        Preview
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline" onclick="regenerateLink(<?= $stakeholder['id'] ?>)">
                                        <i class="bi bi-arrow-repeat"></i>
                                        New Link
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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

.project-tabs {
    display: flex;
    gap: var(--spacing-1);
    margin-bottom: var(--spacing-6);
    overflow-x: auto;
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

.content-layout {
    display: grid;
    gap: var(--spacing-6);
}

@media (min-width: 1024px) {
    .content-layout {
        grid-template-columns: 380px 1fr;
    }
}

.add-stakeholder-card {
    height: fit-content;
}

.add-stakeholder-card .card-header h2 {
    font-size: var(--font-size-base);
    margin: 0;
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

.stakeholder-list-section h2 {
    font-size: var(--font-size-lg);
    margin-bottom: var(--spacing-4);
}

.stakeholders-grid {
    display: grid;
    gap: var(--spacing-4);
}

@media (min-width: 768px) {
    .stakeholders-grid {
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    }
}

.stakeholder-card {
    padding: var(--spacing-4);
}

.stakeholder-card.inactive {
    opacity: 0.6;
}

.stakeholder-card-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    margin-bottom: var(--spacing-4);
}

.stakeholder-avatar {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-primary);
    color: var(--color-white);
    border-radius: var(--radius-lg);
    font-size: var(--font-size-lg);
}

.stakeholder-info {
    flex: 1;
}

.stakeholder-info h3 {
    font-size: var(--font-size-base);
    margin-bottom: 2px;
}

.stakeholder-type {
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
}

.stakeholder-details {
    background: var(--color-gray-50);
    border-radius: var(--radius-lg);
    padding: var(--spacing-3);
    margin-bottom: var(--spacing-4);
}

.detail-row {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--color-gray-600);
    padding: var(--spacing-1) 0;
}

.detail-row i {
    color: var(--color-gray-400);
    width: 16px;
}

.stakeholder-link {
    margin-bottom: var(--spacing-4);
}

.stakeholder-link .form-label {
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
    margin-bottom: var(--spacing-1);
}

.link-input-group {
    display: flex;
    gap: var(--spacing-2);
}

.link-input-group .form-control {
    font-size: var(--font-size-xs);
    padding: var(--spacing-2);
}

.stakeholder-actions {
    display: flex;
    gap: var(--spacing-2);
}
</style>

<script>
async function regenerateLink(stakeholderId) {
    if (!confirm('Generate a new share link? The old link will stop working.')) {
        return;
    }

    try {
        const response = await fetch(`/projects/<?= $project['id'] ?>/stakeholders/${stakeholderId}/regenerate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: '_csrf=<?= htmlspecialchars($csrf) ?>'
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById(`link-${stakeholderId}`).value = data.shareUrl;
            alert('New share link generated!');
        } else {
            alert(data.error || 'Failed to regenerate link');
        }
    } catch (error) {
        alert('An error occurred');
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
