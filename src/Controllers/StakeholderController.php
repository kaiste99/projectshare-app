<?php



namespace App\Controllers;

use App\Services\MailService;

class StakeholderController extends BaseController
{
    public function add(array $params): string
    {
        $this->requireAccount();

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request');
            $this->back();
        }

        $projectId = (int)($params['id'] ?? 0);
        $project = $this->getProject($projectId);

        if (!$project) {
            $this->flash('error', 'Project not found');
            $this->redirect('/projects');
        }

        $data = $this->getPostData();

        // Validate
        $name = trim($data['name'] ?? '');
        $type = $data['stakeholder_type'] ?? '';

        if (empty($name) || !in_array($type, ['building_owner', 'tenant', 'property_manager', 'subcontractor', 'other'])) {
            $this->flash('error', 'Name and type are required');
            $this->redirect('/projects/' . $projectId . '/stakeholders');
        }

        // Generate access token
        $accessToken = $this->generateToken(24);

        $stmt = $this->db->prepare("
            INSERT INTO project_stakeholders (
                project_id, stakeholder_type, name, company_name, email, phone,
                access_token, can_view_documents, can_view_schedule, can_view_impacts, can_download,
                notify_on_update, notify_on_impact, notes, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $projectId,
            $type,
            $this->sanitize($name),
            $data['company_name'] ?: null,
            $data['email'] ? strtolower($data['email']) : null,
            $data['phone'] ?: null,
            $accessToken,
            isset($data['can_view_documents']) ? 1 : 1,
            isset($data['can_view_schedule']) ? 1 : 1,
            isset($data['can_view_impacts']) ? 1 : 1,
            isset($data['can_download']) ? 1 : 1,
            isset($data['notify_on_update']) ? 1 : 1,
            isset($data['notify_on_impact']) ? 1 : 1,
            $data['notes'] ?: null,
            $this->currentUser['id'],
        ]);

        $stakeholderId = (int)$this->db->lastInsertId();

        // Send invitation email if email provided
        if (!empty($data['email']) && !empty($data['send_invitation'])) {
            $shareUrl = ($_ENV['APP_URL'] ?? 'http://localhost:8000') . '/share/' . $accessToken;

            $mailService = new MailService();
            $mailService->sendStakeholderInvitation(
                $data['email'],
                $name,
                $project['name'],
                $this->currentAccount['name'],
                $shareUrl
            );
        }

        // Log activity
        $this->logActivity('added stakeholder', 'stakeholder', $stakeholderId, $projectId);

        $this->flash('success', 'Stakeholder added successfully');
        $this->redirect('/projects/' . $projectId . '/stakeholders');
        return '';
    }

    public function update(array $params): string
    {
        $this->requireAccount();

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid request'], 403);
        }

        $projectId = (int)($params['id'] ?? 0);
        $stakeholderId = (int)($params['stakeholderId'] ?? 0);

        $project = $this->getProject($projectId);
        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }

        $stakeholder = $this->getStakeholder($stakeholderId, $projectId);
        if (!$stakeholder) {
            return $this->json(['error' => 'Stakeholder not found'], 404);
        }

        $data = $this->getPostData();

        $stmt = $this->db->prepare("
            UPDATE project_stakeholders SET
                name = ?,
                company_name = ?,
                email = ?,
                phone = ?,
                stakeholder_type = ?,
                can_view_documents = ?,
                can_view_schedule = ?,
                can_view_impacts = ?,
                can_download = ?,
                notify_on_update = ?,
                notify_on_impact = ?,
                notes = ?,
                is_active = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $this->sanitize($data['name'] ?? ''),
            $data['company_name'] ?: null,
            $data['email'] ? strtolower($data['email']) : null,
            $data['phone'] ?: null,
            $data['stakeholder_type'] ?? 'other',
            isset($data['can_view_documents']) ? 1 : 0,
            isset($data['can_view_schedule']) ? 1 : 0,
            isset($data['can_view_impacts']) ? 1 : 0,
            isset($data['can_download']) ? 1 : 0,
            isset($data['notify_on_update']) ? 1 : 0,
            isset($data['notify_on_impact']) ? 1 : 0,
            $data['notes'] ?: null,
            isset($data['is_active']) ? 1 : 1,
            $stakeholderId,
        ]);

        $this->flash('success', 'Stakeholder updated');
        $this->redirect('/projects/' . $projectId . '/stakeholders');
        return '';
    }

    public function regenerateLink(array $params): string
    {
        $this->requireAccount();

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid request'], 403);
        }

        $projectId = (int)($params['id'] ?? 0);
        $stakeholderId = (int)($params['stakeholderId'] ?? 0);

        $project = $this->getProject($projectId);
        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }

        $stakeholder = $this->getStakeholder($stakeholderId, $projectId);
        if (!$stakeholder) {
            return $this->json(['error' => 'Stakeholder not found'], 404);
        }

        // Generate new token
        $newToken = $this->generateToken(24);

        $stmt = $this->db->prepare("UPDATE project_stakeholders SET access_token = ? WHERE id = ?");
        $stmt->execute([$newToken, $stakeholderId]);

        $shareUrl = ($_ENV['APP_URL'] ?? 'http://localhost:8000') . '/share/' . $newToken;

        return $this->json([
            'success' => true,
            'token' => $newToken,
            'shareUrl' => $shareUrl,
        ]);
    }

    private function getProject(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = ? AND account_id = ?");
        $stmt->execute([$id, $this->currentAccount['id']]);
        return $stmt->fetch() ?: null;
    }

    private function getStakeholder(int $id, int $projectId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_stakeholders WHERE id = ? AND project_id = ?");
        $stmt->execute([$id, $projectId]);
        return $stmt->fetch() ?: null;
    }

    private function logActivity(string $action, string $resourceType, int $resourceId, int $projectId): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO activity_logs (user_id, account_id, project_id, action, resource_type, resource_id, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $this->currentUser['id'],
            $this->currentAccount['id'],
            $projectId,
            $action,
            $resourceType,
            $resourceId,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}
