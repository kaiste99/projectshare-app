<?php
ob_start();
?>

<div class="feedback-complete">
    <div class="container">
        <div class="complete-card">
            <div class="complete-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <h1>Thank You!</h1>
            <p>Your feedback has been submitted successfully.</p>
            <p class="text-muted">We appreciate you taking the time to share your experience with us.</p>

            <?php if ($request['company_name']): ?>
                <div class="company-info mt-6">
                    <p>From all of us at</p>
                    <strong><?= htmlspecialchars($request['company_name']) ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.feedback-complete {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    padding: var(--spacing-6);
}

.complete-card {
    text-align: center;
    max-width: 400px;
    background: var(--color-white);
    border-radius: var(--radius-2xl);
    padding: var(--spacing-10);
    box-shadow: var(--shadow-lg);
}

.complete-icon {
    width: 80px;
    height: 80px;
    background: #dcfce7;
    color: #16a34a;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    margin: 0 auto var(--spacing-6);
}

.complete-card h1 {
    font-size: var(--font-size-2xl);
    margin-bottom: var(--spacing-3);
}

.complete-card p {
    color: var(--color-gray-600);
    margin-bottom: var(--spacing-2);
}

.company-info {
    padding-top: var(--spacing-6);
    border-top: 1px solid var(--color-gray-200);
}

.company-info p {
    font-size: var(--font-size-sm);
    margin-bottom: var(--spacing-1);
}

.company-info strong {
    font-size: var(--font-size-lg);
    color: var(--color-gray-900);
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
