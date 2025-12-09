<?php



namespace App\Controllers;

class FeedbackController extends BaseController
{
    public function show(array $params): string
    {
        $token = $params['token'] ?? '';

        // Get feedback request
        $stmt = $this->db->prepare("
            SELECT fr.*, p.name as project_name, ps.name as stakeholder_name,
                   a.name as company_name, a.logo_path
            FROM feedback_requests fr
            JOIN projects p ON fr.project_id = p.id
            JOIN project_stakeholders ps ON fr.stakeholder_id = ps.id
            JOIN accounts a ON p.account_id = a.id
            WHERE fr.token = ?
        ");
        $stmt->execute([$token]);
        $request = $stmt->fetch();

        if (!$request) {
            return $this->view('pages/feedback/invalid', [
                'title' => 'Invalid Link',
                'hideNav' => true,
            ]);
        }

        if ($request['completed_at']) {
            return $this->view('pages/feedback/completed', [
                'title' => 'Thank You',
                'request' => $request,
                'hideNav' => true,
            ]);
        }

        // Mark as opened if not already
        if (!$request['opened_at']) {
            $stmt = $this->db->prepare("UPDATE feedback_requests SET opened_at = NOW() WHERE id = ?");
            $stmt->execute([$request['id']]);
        }

        return $this->view('pages/feedback/form', [
            'title' => 'Project Feedback',
            'request' => $request,
            'token' => $token,
            'hideNav' => true,
            'hideFooter' => true,
        ]);
    }

    public function submit(array $params): string
    {
        $token = $params['token'] ?? '';

        // Get feedback request
        $stmt = $this->db->prepare("
            SELECT fr.*, ps.stakeholder_type
            FROM feedback_requests fr
            JOIN project_stakeholders ps ON fr.stakeholder_id = ps.id
            WHERE fr.token = ? AND fr.completed_at IS NULL
        ");
        $stmt->execute([$token]);
        $request = $stmt->fetch();

        if (!$request) {
            return $this->json(['error' => 'Invalid or already completed feedback request'], 400);
        }

        $data = $this->getPostData();

        // Save feedback
        $stmt = $this->db->prepare("
            INSERT INTO project_feedback (
                project_id, stakeholder_id, feedback_type,
                overall_rating, communication_rating, quality_rating, timeliness_rating, professionalism_rating,
                what_went_well, what_could_improve, additional_comments,
                would_recommend, recommend_score,
                allow_testimonial, testimonial_text,
                submitted_at
            ) VALUES (?, ?, 'project_closing', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $request['project_id'],
            $request['stakeholder_id'],
            $data['overall_rating'] ?: null,
            $data['communication_rating'] ?: null,
            $data['quality_rating'] ?: null,
            $data['timeliness_rating'] ?: null,
            $data['professionalism_rating'] ?: null,
            $data['what_went_well'] ?: null,
            $data['what_could_improve'] ?: null,
            $data['additional_comments'] ?: null,
            isset($data['would_recommend']) ? 1 : 0,
            $data['recommend_score'] ?: null,
            isset($data['allow_testimonial']) ? 1 : 0,
            $data['testimonial_text'] ?: null,
        ]);

        // Mark request as completed
        $stmt = $this->db->prepare("UPDATE feedback_requests SET completed_at = NOW() WHERE id = ?");
        $stmt->execute([$request['id']]);

        return $this->json(['success' => true, 'redirect' => '/feedback/' . $token]);
    }

    public function requestFeedback(array $params): string
    {
        $this->requireAccount();

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid request'], 403);
        }

        $projectId = (int)($params['id'] ?? 0);

        // Get project
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = ? AND account_id = ?");
        $stmt->execute([$projectId, $this->currentAccount['id']]);
        $project = $stmt->fetch();

        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }

        $data = $this->getPostData();
        $stakeholderIds = $data['stakeholder_ids'] ?? [];

        if (empty($stakeholderIds)) {
            return $this->json(['error' => 'No stakeholders selected'], 400);
        }

        $mailService = new \App\Services\MailService();
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $sentCount = 0;

        foreach ($stakeholderIds as $stakeholderId) {
            // Get stakeholder
            $stmt = $this->db->prepare("
                SELECT * FROM project_stakeholders
                WHERE id = ? AND project_id = ? AND email IS NOT NULL
            ");
            $stmt->execute([$stakeholderId, $projectId]);
            $stakeholder = $stmt->fetch();

            if (!$stakeholder) {
                continue;
            }

            // Check for existing pending request
            $stmt = $this->db->prepare("
                SELECT 1 FROM feedback_requests
                WHERE project_id = ? AND stakeholder_id = ? AND completed_at IS NULL
            ");
            $stmt->execute([$projectId, $stakeholderId]);

            if ($stmt->fetch()) {
                continue; // Already has pending request
            }

            // Create feedback request
            $token = $this->generateToken();
            $stmt = $this->db->prepare("
                INSERT INTO feedback_requests (project_id, stakeholder_id, token, sent_at, expires_at)
                VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))
            ");
            $stmt->execute([$projectId, $stakeholderId, $token]);

            // Send email
            $feedbackUrl = $appUrl . '/feedback/' . $token;
            $mailService->sendFeedbackRequest(
                $stakeholder['email'],
                $stakeholder['name'],
                $project['name'],
                $this->currentAccount['name'],
                $feedbackUrl
            );

            $sentCount++;
        }

        return $this->json([
            'success' => true,
            'sent_count' => $sentCount,
        ]);
    }
}
