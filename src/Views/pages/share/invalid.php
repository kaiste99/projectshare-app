<?php
ob_start();
?>

<div class="error-page">
    <div class="container">
        <div class="error-content">
            <div class="error-icon">
                <i class="bi bi-link-45deg"></i>
            </div>
            <h1>Invalid or Expired Link</h1>
            <p>This share link is no longer valid. It may have expired or been revoked.</p>
            <p class="text-muted">Please contact the project owner for a new link.</p>
        </div>
    </div>
</div>

<style>
.error-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-gray-50);
    padding: var(--spacing-6);
}

.error-content {
    text-align: center;
    max-width: 400px;
}

.error-icon {
    width: 80px;
    height: 80px;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto var(--spacing-6);
}

.error-content h1 {
    margin-bottom: var(--spacing-3);
}

.error-content p {
    color: var(--color-gray-600);
    margin-bottom: var(--spacing-2);
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
