<?php
ob_start();
?>

<div class="setup-page">
    <div class="container">
        <div class="setup-card">
            <div class="setup-header">
                <h1>Set Up Your Account</h1>
                <p>Let's get you started. Tell us about your company.</p>
            </div>

            <form action="/account/setup" method="POST" class="setup-form">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                <!-- Personal Info -->
                <div class="form-section">
                    <h2>Your Information</h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                class="form-control"
                                value="<?= htmlspecialchars($currentUser['first_name'] ?? '') ?>"
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                class="form-control"
                                value="<?= htmlspecialchars($currentUser['last_name'] ?? '') ?>"
                                required
                            >
                        </div>
                    </div>
                </div>

                <!-- Company Info -->
                <div class="form-section">
                    <h2>Company Information</h2>

                    <div class="form-group">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input
                            type="text"
                            id="company_name"
                            name="company_name"
                            class="form-control"
                            placeholder="e.g., Schmidt Heating & Plumbing"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="industry" class="form-label">Primary Industry</label>
                        <select id="industry" name="industry" class="form-control">
                            <option value="heating">Heating & HVAC</option>
                            <option value="electricity">Electrical</option>
                            <option value="photovoltaics">Solar / Photovoltaics</option>
                            <option value="plumbing">Plumbing</option>
                            <option value="construction">Construction</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        Continue to Dashboard
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.setup-page {
    min-height: 100vh;
    background: var(--color-gray-50);
    padding: var(--spacing-8) 0;
    display: flex;
    align-items: center;
}

.setup-card {
    max-width: 560px;
    margin: 0 auto;
    background: var(--color-white);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-lg);
    padding: var(--spacing-8);
}

.setup-header {
    text-align: center;
    margin-bottom: var(--spacing-8);
}

.setup-header h1 {
    font-size: var(--font-size-2xl);
    margin-bottom: var(--spacing-2);
}

.setup-header p {
    color: var(--color-gray-500);
    margin: 0;
}

.form-section {
    margin-bottom: var(--spacing-8);
}

.form-section h2 {
    font-size: var(--font-size-lg);
    margin-bottom: var(--spacing-4);
    padding-bottom: var(--spacing-2);
    border-bottom: 1px solid var(--color-gray-200);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-4);
}

@media (max-width: 480px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
$content = ob_get_clean();
$hideNav = true;
$hideFooter = true;
include __DIR__ . '/../../layouts/main.php';
