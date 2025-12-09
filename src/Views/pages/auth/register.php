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
            <h1>Create your account</h1>
            <p>Start your 14-day free trial. No credit card required.</p>
        </div>

        <!-- Social Login Options -->
        <div class="social-login">
            <a href="/auth/google" class="btn btn-social btn-google">
                <svg viewBox="0 0 24 24" width="20" height="20">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Sign up with Google
            </a>

            <a href="/auth/microsoft" class="btn btn-social btn-microsoft">
                <svg viewBox="0 0 24 24" width="20" height="20">
                    <path fill="#F25022" d="M1 1h10v10H1z"/>
                    <path fill="#00A4EF" d="M1 13h10v10H1z"/>
                    <path fill="#7FBA00" d="M13 1h10v10H13z"/>
                    <path fill="#FFB900" d="M13 13h10v10H13z"/>
                </svg>
                Sign up with Microsoft
            </a>

            <a href="/auth/apple" class="btn btn-social btn-apple">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                    <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                </svg>
                Sign up with Apple
            </a>
        </div>

        <div class="auth-divider">
            <span>or continue with email</span>
        </div>

        <!-- Email Signup Form -->
        <form id="emailForm" class="auth-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

            <div class="form-group">
                <label for="email" class="form-label">Work email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="name@company.com"
                    required
                    autocomplete="email"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="sendCodeBtn">
                <span class="btn-text">Continue with Email</span>
                <span class="btn-loading" style="display: none;">
                    <i class="bi bi-arrow-repeat spin"></i> Sending...
                </span>
            </button>
        </form>

        <p class="auth-terms">
            By signing up, you agree to our
            <a href="/terms">Terms of Service</a> and
            <a href="/privacy">Privacy Policy</a>.
        </p>

        <p class="auth-footer">
            Already have an account? <a href="/login">Sign in</a>
        </p>
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

.auth-header h1 {
    font-size: var(--font-size-2xl);
    margin-bottom: var(--spacing-2);
}

.auth-header p {
    color: var(--color-gray-500);
    margin: 0;
}

.social-login {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
    margin-bottom: var(--spacing-6);
}

.btn-social {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-3);
    padding: var(--spacing-3) var(--spacing-4);
    background: var(--color-white);
    border: 1px solid var(--color-gray-300);
    border-radius: var(--radius-lg);
    color: var(--color-gray-700);
    font-weight: 500;
    transition: all var(--transition-fast);
}

.btn-social:hover {
    background: var(--color-gray-50);
    border-color: var(--color-gray-400);
    color: var(--color-gray-900);
}

.btn-apple {
    background: var(--color-gray-900);
    border-color: var(--color-gray-900);
    color: var(--color-white);
}

.btn-apple:hover {
    background: var(--color-gray-800);
    color: var(--color-white);
}

.auth-divider {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-6);
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--color-gray-200);
}

.auth-divider span {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

.auth-form {
    margin-bottom: var(--spacing-4);
}

.auth-terms {
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
    text-align: center;
    margin-bottom: var(--spacing-6);
}

.auth-footer {
    text-align: center;
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
    margin: 0;
    padding-top: var(--spacing-4);
    border-top: 1px solid var(--color-gray-200);
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
document.getElementById('emailForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = document.getElementById('sendCodeBtn');
    const btnText = btn.querySelector('.btn-text');
    const btnLoading = btn.querySelector('.btn-loading');
    const email = document.getElementById('email').value;
    const csrf = document.querySelector('[name="_csrf"]').value;

    btn.disabled = true;
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline';

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
            window.location.href = `/verify-email?email=${encodeURIComponent(email)}`;
        } else {
            alert(data.error || 'Failed to send code. Please try again.');
            btn.disabled = false;
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
        btn.disabled = false;
        btnText.style.display = 'inline';
        btnLoading.style.display = 'none';
    }
});
</script>

<?php
$content = ob_get_clean();
$hideNav = true;
$hideFooter = true;
include __DIR__ . '/../../layouts/main.php';
