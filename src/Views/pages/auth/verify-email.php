<?php
ob_start();
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <a href="/" class="auth-logo">
                <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span><?= htmlspecialchars($appName) ?></span>
            </a>
            <div class="auth-icon">
                <i class="bi bi-envelope-check"></i>
            </div>
            <h1>Check your email</h1>
            <p>We sent a 6-digit code to<br><strong><?= htmlspecialchars($email) ?></strong></p>
        </div>

        <form id="verifyForm" class="auth-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

            <div class="form-group">
                <label for="code" class="form-label">Enter verification code</label>
                <div class="code-input-wrapper">
                    <input
                        type="text"
                        id="code"
                        name="code"
                        class="form-control code-input"
                        placeholder="000000"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        required
                    >
                </div>
                <p class="form-hint">The code expires in 15 minutes</p>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="remember" name="remember" class="form-check-input" checked>
                    <label for="remember" class="form-check-label">Keep me signed in</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="verifyBtn">
                <span class="btn-text">Verify & Sign In</span>
                <span class="btn-loading" style="display: none;">
                    <i class="bi bi-arrow-repeat spin"></i> Verifying...
                </span>
            </button>

            <div id="errorMessage" class="alert alert-danger mt-4" style="display: none;"></div>
        </form>

        <div class="auth-footer">
            <p>Didn't receive the code?</p>
            <button type="button" class="btn btn-outline btn-sm" id="resendBtn">
                Resend Code
            </button>
            <p class="mt-4">
                <a href="/login"><i class="bi bi-arrow-left"></i> Back to sign in</a>
            </p>
        </div>
    </div>
</div>

<style>
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-6);
    background: var(--color-gray-50);
}

.auth-card {
    width: 100%;
    max-width: 420px;
    background: var(--color-white);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-lg);
    padding: var(--spacing-8);
}

.auth-header {
    text-align: center;
    margin-bottom: var(--spacing-6);
}

.auth-logo {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-xl);
    font-weight: 700;
    color: var(--color-gray-900);
    margin-bottom: var(--spacing-6);
}

.auth-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #dbeafe;
    color: var(--color-primary);
    border-radius: var(--radius-full);
    font-size: var(--font-size-2xl);
    margin: 0 auto var(--spacing-4);
}

.auth-header h1 {
    font-size: var(--font-size-2xl);
    margin-bottom: var(--spacing-2);
}

.auth-header p {
    color: var(--color-gray-500);
    margin: 0;
}

.auth-header strong {
    color: var(--color-gray-700);
}

.code-input-wrapper {
    max-width: 200px;
    margin: 0 auto;
}

.code-input {
    text-align: center;
    font-size: var(--font-size-2xl);
    font-weight: 600;
    letter-spacing: 0.5em;
    padding: var(--spacing-4);
}

.auth-footer {
    text-align: center;
    padding-top: var(--spacing-6);
    border-top: 1px solid var(--color-gray-200);
}

.auth-footer p {
    color: var(--color-gray-500);
    font-size: var(--font-size-sm);
    margin-bottom: var(--spacing-3);
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<script>
// Auto-focus and format code input
const codeInput = document.getElementById('code');
codeInput.focus();

codeInput.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);

    // Auto-submit when 6 digits entered
    if (this.value.length === 6) {
        document.getElementById('verifyForm').dispatchEvent(new Event('submit'));
    }
});

// Verify form submission
document.getElementById('verifyForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = document.getElementById('verifyBtn');
    const btnText = btn.querySelector('.btn-text');
    const btnLoading = btn.querySelector('.btn-loading');
    const errorDiv = document.getElementById('errorMessage');

    const formData = new FormData(this);

    btn.disabled = true;
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline';
    errorDiv.style.display = 'none';

    try {
        const response = await fetch('/verify-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(formData).toString()
        });

        const data = await response.json();

        if (data.success) {
            window.location.href = data.redirect || '/dashboard';
        } else {
            errorDiv.textContent = data.error || 'Invalid code. Please try again.';
            errorDiv.style.display = 'block';
            btn.disabled = false;
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
            codeInput.select();
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
        btn.disabled = false;
        btnText.style.display = 'inline';
        btnLoading.style.display = 'none';
    }
});

// Resend code
document.getElementById('resendBtn').addEventListener('click', async function() {
    const email = document.querySelector('[name="email"]').value;
    const csrf = document.querySelector('[name="_csrf"]').value;

    this.disabled = true;
    this.textContent = 'Sending...';

    try {
        const response = await fetch('/send-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${encodeURIComponent(email)}&_csrf=${encodeURIComponent(csrf)}`
        });

        const data = await response.json();

        if (data.success) {
            this.textContent = 'Code Sent!';
            setTimeout(() => {
                this.disabled = false;
                this.textContent = 'Resend Code';
            }, 30000);
        } else {
            alert(data.error || 'Failed to resend code.');
            this.disabled = false;
            this.textContent = 'Resend Code';
        }
    } catch (error) {
        alert('An error occurred.');
        this.disabled = false;
        this.textContent = 'Resend Code';
    }
});
</script>

<?php
$content = ob_get_clean();
$hideNav = true;
$hideFooter = true;
include __DIR__ . '/../../layouts/main.php';
