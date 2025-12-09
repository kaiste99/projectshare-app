<?php
ob_start();
?>

<div class="feedback-page">
    <div class="container">
        <div class="feedback-card">
            <!-- Header -->
            <div class="feedback-header">
                <?php if ($request['logo_path']): ?>
                    <img src="<?= htmlspecialchars($request['logo_path']) ?>" alt="Logo" class="company-logo">
                <?php endif; ?>
                <h1>Share Your Feedback</h1>
                <p>Help <strong><?= htmlspecialchars($request['company_name']) ?></strong> improve by sharing your experience with the <strong><?= htmlspecialchars($request['project_name']) ?></strong> project.</p>
            </div>

            <!-- Form -->
            <form id="feedbackForm" class="feedback-form">
                <!-- Overall Rating -->
                <div class="form-section">
                    <h2>Overall Experience</h2>
                    <div class="rating-group">
                        <label class="form-label">How would you rate your overall experience?</label>
                        <div class="star-rating" data-field="overall_rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" class="star-btn" data-value="<?= $i ?>">
                                    <i class="bi bi-star"></i>
                                </button>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="overall_rating" value="">
                    </div>
                </div>

                <!-- Category Ratings -->
                <div class="form-section">
                    <h2>Rate Different Aspects</h2>

                    <div class="rating-row">
                        <div class="rating-label">
                            <i class="bi bi-chat-dots"></i>
                            <span>Communication</span>
                        </div>
                        <div class="star-rating star-rating-sm" data-field="communication_rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" class="star-btn" data-value="<?= $i ?>">
                                    <i class="bi bi-star"></i>
                                </button>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="communication_rating" value="">
                    </div>

                    <div class="rating-row">
                        <div class="rating-label">
                            <i class="bi bi-award"></i>
                            <span>Quality of Work</span>
                        </div>
                        <div class="star-rating star-rating-sm" data-field="quality_rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" class="star-btn" data-value="<?= $i ?>">
                                    <i class="bi bi-star"></i>
                                </button>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="quality_rating" value="">
                    </div>

                    <div class="rating-row">
                        <div class="rating-label">
                            <i class="bi bi-clock"></i>
                            <span>Timeliness</span>
                        </div>
                        <div class="star-rating star-rating-sm" data-field="timeliness_rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" class="star-btn" data-value="<?= $i ?>">
                                    <i class="bi bi-star"></i>
                                </button>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="timeliness_rating" value="">
                    </div>

                    <div class="rating-row">
                        <div class="rating-label">
                            <i class="bi bi-person-check"></i>
                            <span>Professionalism</span>
                        </div>
                        <div class="star-rating star-rating-sm" data-field="professionalism_rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" class="star-btn" data-value="<?= $i ?>">
                                    <i class="bi bi-star"></i>
                                </button>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="professionalism_rating" value="">
                    </div>
                </div>

                <!-- Qualitative Feedback -->
                <div class="form-section">
                    <h2>Tell Us More</h2>

                    <div class="form-group">
                        <label class="form-label">What went well?</label>
                        <textarea name="what_went_well" class="form-control" rows="3" placeholder="What did you appreciate about the project?"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">What could be improved?</label>
                        <textarea name="what_could_improve" class="form-control" rows="3" placeholder="Any suggestions for improvement?"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Additional comments (optional)</label>
                        <textarea name="additional_comments" class="form-control" rows="3" placeholder="Anything else you'd like to share?"></textarea>
                    </div>
                </div>

                <!-- NPS -->
                <div class="form-section">
                    <h2>Would You Recommend Us?</h2>
                    <div class="form-group">
                        <label class="form-label">On a scale of 0-10, how likely are you to recommend us?</label>
                        <div class="nps-scale">
                            <?php for ($i = 0; $i <= 10; $i++): ?>
                                <button type="button" class="nps-btn" data-value="<?= $i ?>"><?= $i ?></button>
                            <?php endfor; ?>
                        </div>
                        <div class="nps-labels">
                            <span>Not likely</span>
                            <span>Very likely</span>
                        </div>
                        <input type="hidden" name="recommend_score" value="">
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="would_recommend" name="would_recommend" class="form-check-input">
                            <label for="would_recommend" class="form-check-label">Yes, I would recommend this company to others</label>
                        </div>
                    </div>
                </div>

                <!-- Testimonial -->
                <div class="form-section">
                    <h2>Share a Testimonial (Optional)</h2>
                    <p class="form-hint mb-4">May we use your feedback as a testimonial on our website?</p>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="allow_testimonial" name="allow_testimonial" class="form-check-input">
                            <label for="allow_testimonial" class="form-check-label">Yes, you may use my feedback as a testimonial</label>
                        </div>
                    </div>

                    <div class="form-group testimonial-field" style="display: none;">
                        <label class="form-label">Your testimonial</label>
                        <textarea name="testimonial_text" class="form-control" rows="3" placeholder="Write a brief testimonial..."></textarea>
                    </div>
                </div>

                <!-- Submit -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="submitBtn">
                        <span class="btn-text">Submit Feedback</span>
                        <span class="btn-loading" style="display: none;">
                            <i class="bi bi-arrow-repeat spin"></i> Submitting...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.feedback-page {
    min-height: 100vh;
    background: var(--color-gray-50);
    padding: var(--spacing-6) 0;
}

.feedback-card {
    max-width: 640px;
    margin: 0 auto;
    background: var(--color-white);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
}

.feedback-header {
    text-align: center;
    padding: var(--spacing-8) var(--spacing-6);
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.feedback-header .company-logo {
    height: 48px;
    margin-bottom: var(--spacing-4);
}

.feedback-header h1 {
    font-size: var(--font-size-2xl);
    margin-bottom: var(--spacing-2);
}

.feedback-header p {
    color: var(--color-gray-600);
    margin: 0;
}

.feedback-form {
    padding: var(--spacing-6);
}

.form-section {
    padding-bottom: var(--spacing-6);
    margin-bottom: var(--spacing-6);
    border-bottom: 1px solid var(--color-gray-200);
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.form-section h2 {
    font-size: var(--font-size-lg);
    margin-bottom: var(--spacing-4);
}

/* Star Rating */
.star-rating {
    display: flex;
    gap: var(--spacing-2);
}

.star-rating.star-rating-sm {
    gap: var(--spacing-1);
}

.star-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    font-size: 32px;
    color: var(--color-gray-300);
    transition: all var(--transition-fast);
}

.star-rating-sm .star-btn {
    font-size: 24px;
}

.star-btn:hover,
.star-btn.active {
    color: #fbbf24;
}

.star-btn.active i {
    font-weight: bold;
}

.star-btn.active i::before {
    content: "\F586"; /* bi-star-fill */
}

.rating-group {
    text-align: center;
}

.rating-group .form-label {
    margin-bottom: var(--spacing-3);
}

.rating-group .star-rating {
    justify-content: center;
}

.rating-row {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    padding: var(--spacing-3) 0;
    border-bottom: 1px solid var(--color-gray-100);
}

.rating-row:last-child {
    border-bottom: none;
}

.rating-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    flex: 1;
    font-weight: 500;
}

.rating-label i {
    color: var(--color-gray-400);
}

/* NPS Scale */
.nps-scale {
    display: flex;
    gap: var(--spacing-1);
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: var(--spacing-2);
}

.nps-btn {
    width: 40px;
    height: 40px;
    border: 2px solid var(--color-gray-200);
    border-radius: var(--radius-md);
    background: var(--color-white);
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.nps-btn:hover {
    border-color: var(--color-primary);
}

.nps-btn.active {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: var(--color-white);
}

.nps-labels {
    display: flex;
    justify-content: space-between;
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
}

.form-actions {
    padding-top: var(--spacing-6);
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@media (max-width: 480px) {
    .rating-row {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-2);
    }

    .nps-btn {
        width: 32px;
        height: 32px;
        font-size: var(--font-size-sm);
    }
}
</style>

<script>
// Star ratings
document.querySelectorAll('.star-rating').forEach(container => {
    const field = container.dataset.field;
    const input = container.parentElement.querySelector(`input[name="${field}"]`);
    const stars = container.querySelectorAll('.star-btn');

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const value = star.dataset.value;
            input.value = value;

            stars.forEach(s => {
                s.classList.toggle('active', s.dataset.value <= value);
            });
        });
    });
});

// NPS scale
document.querySelectorAll('.nps-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.nps-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelector('input[name="recommend_score"]').value = btn.dataset.value;
    });
});

// Testimonial toggle
document.getElementById('allow_testimonial').addEventListener('change', function() {
    document.querySelector('.testimonial-field').style.display = this.checked ? 'block' : 'none';
});

// Form submission
document.getElementById('feedbackForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = document.getElementById('submitBtn');
    const btnText = btn.querySelector('.btn-text');
    const btnLoading = btn.querySelector('.btn-loading');

    btn.disabled = true;
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline';

    const formData = new FormData(this);

    try {
        const response = await fetch('/feedback/<?= htmlspecialchars($token) ?>', {
            method: 'POST',
            body: new URLSearchParams(formData)
        });

        const data = await response.json();

        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert(data.error || 'Failed to submit feedback');
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
include __DIR__ . '/../../layouts/main.php';
