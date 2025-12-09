<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'ProjectShare - Share project plans and updates with stakeholders') ?>">
    <meta name="robots" content="index, follow">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($title ?? 'ProjectShare') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription ?? 'Share project plans and updates with stakeholders') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($appUrl ?? '') ?>">

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <title><?= htmlspecialchars($title ?? 'ProjectShare') ?> | <?= htmlspecialchars($appName ?? 'ProjectShare') ?></title>

    <link rel="stylesheet" href="/assets/css/app.css">
    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body class="<?= $bodyClass ?? '' ?>">
    <?php if (!($hideNav ?? false)): ?>
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="/" class="navbar-brand">
                <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span><?= htmlspecialchars($appName ?? 'ProjectShare') ?></span>
            </a>

            <button class="navbar-toggle" id="navbarToggle" aria-label="Toggle navigation">
                <i class="bi bi-list"></i>
            </button>

            <div class="navbar-menu" id="navbarMenu">
                <?php if ($currentUser ?? false): ?>
                    <a href="/dashboard" class="navbar-link">Dashboard</a>
                    <a href="/projects" class="navbar-link">Projects</a>
                    <div class="navbar-dropdown">
                        <button class="navbar-dropdown-toggle">
                            <span class="avatar avatar-sm">
                                <?= strtoupper(substr($currentUser['first_name'] ?? $currentUser['email'], 0, 1)) ?>
                            </span>
                            <span class="d-none d-md-inline"><?= htmlspecialchars($currentUser['first_name'] ?? explode('@', $currentUser['email'])[0]) ?></span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="navbar-dropdown-menu">
                            <a href="/account" class="dropdown-item">
                                <i class="bi bi-person"></i> Account
                            </a>
                            <a href="/account/team" class="dropdown-item">
                                <i class="bi bi-people"></i> Team
                            </a>
                            <a href="/account/settings" class="dropdown-item">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="/logout" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right"></i> Sign Out
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/login" class="navbar-link">Sign In</a>
                    <a href="/register" class="btn btn-primary btn-sm">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <?php if (isset($flash) && !empty($flash)): ?>
    <div class="container mt-3">
        <?php foreach ($flash as $type => $messages): ?>
            <?php foreach ($messages as $message): ?>
                <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?>" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="alert-close" aria-label="Close">&times;</button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <main class="<?= $mainClass ?? '' ?>">
        <?= $content ?? '' ?>
    </main>

    <?php if (!($hideFooter ?? false)): ?>
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="/" class="footer-logo">
                        <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span><?= htmlspecialchars($appName ?? 'ProjectShare') ?></span>
                    </a>
                    <p class="footer-tagline">Better communication for better projects.</p>
                </div>
                <div class="footer-links">
                    <h4>Product</h4>
                    <ul>
                        <li><a href="/#features">Features</a></li>
                        <li><a href="/#how-it-works">How It Works</a></li>
                        <li><a href="/#pricing">Pricing</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="/about">About</a></li>
                        <li><a href="/contact">Contact</a></li>
                        <li><a href="/privacy">Privacy</a></li>
                        <li><a href="/terms">Terms</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($appName ?? 'ProjectShare') ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <script src="/assets/js/app.js"></script>
    <?php if (isset($extraJs)): ?>
        <?= $extraJs ?>
    <?php endif; ?>
</body>
</html>
