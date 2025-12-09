<?php



namespace App\Controllers;

use App\Services\MailService;

class PlanController extends BaseController
{
    public function addTask(array $params): string
    {
        $this->requireAccount();

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid request'], 403);
        }

        $projectId = (int)($params['id'] ?? 0);
        $project = $this->getProject($projectId);

        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }

        // Get current plan
        $stmt = $this->db->prepare("SELECT * FROM project_plans WHERE project_id = ? AND is_current = 1");
        $stmt->execute([$projectId]);
        $plan = $stmt->fetch();

        if (!$plan) {
            return $this->json(['error' => 'No active plan found'], 404);
        }

        $data = $this->getPostData();

        // Get next sort order
        $stmt = $this->db->prepare("SELECT MAX(sort_order) FROM project_tasks WHERE plan_id = ?");
        $stmt->execute([$plan['id']]);
        $maxOrder = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("
            INSERT INTO project_tasks (
                plan_id, parent_task_id, sort_order, title, description, task_type,
                planned_start_date, planned_end_date, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");

        $stmt->execute([
            $plan['id'],
            $data['parent_task_id'] ?: null,
            $maxOrder + 1,
            $this->sanitize($data['title'] ?? ''),
            $data['description'] ?: null,
            $data['task_type'] ?? 'task',
            $data['planned_start_date'] ?: null,
            $data['planned_end_date'] ?: null,
        ]);

        $taskId = (int)$this->db->lastInsertId();

        // Log activity
        $this->logActivity('added task', 'task', $taskId, $projectId);

        return $this->json([
            'success' => true,
            'task_id' => $taskId,
        ]);
    }

    public function updateTask(array $params): string
    {
        $this->requireAccount();

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid request'], 403);
        }

        $projectId = (int)($params['id'] ?? 0);
        $taskId = (int)($params['taskId'] ?? 0);

        $project = $this->getProject($projectId);
        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }

        $task = $this->getTask($taskId, $projectId);
        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        $data = $this->getPostData();

        $stmt = $this->db->prepare("
            UPDATE project_tasks SET
                title = ?,
                description = ?,
                task_type = ?,
                planned_start_date = ?,
                planned_end_date = ?,
                actual_start_date = ?,
                actual_end_date = ?,
                status = ?,
                completion_percentage = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $this->sanitize($data['title'] ?? ''),
            $data['description'] ?: null,
            $data['task_type'] ?? 'task',
            $data['planned_start_date'] ?: null,
            $data['planned_end_date'] ?: null,
            $data['actual_start_date'] ?: null,
            $data['actual_end_date'] ?: null,
            $data['status'] ?? 'pending',
            (int)($data['completion_percentage'] ?? 0),
            $taskId,
        ]);

        return $this->json(['success' => true]);
    }

    public function deleteTask(array $params): string
    {
        $this->requireAccount();

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid request'], 403);
        }

        $projectId = (int)($params['id'] ?? 0);
        $taskId = (int)($params['taskId'] ?? 0);

        $project = $this->getProject($projectId);
        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }

        $task = $this->getTask($taskId, $projectId);
        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        $stmt = $this->db->prepare("DELETE FROM project_tasks WHERE id = ?");
        $stmt->execute([$taskId]);

        return $this->json(['success' => true]);
    }

    public function addImpact(array $params): string
    {
        $this->requireAccount();

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid request'], 403);
        }

        $projectId = (int)($params['id'] ?? 0);
        $taskId = (int)($params['taskId'] ?? 0);

        $project = $this->getProject($projectId);
        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }

        $task = $this->getTask($taskId, $projectId);
        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        $data = $this->getPostData();

        $stmt = $this->db->prepare("
            INSERT INTO task_impacts (
                task_id, impact_type, severity, title, description,
                affects_building_owner, affects_tenants, affects_neighbors,
                impact_start_datetime, impact_end_datetime, estimated_duration_hours,
                preparation_instructions, during_instructions, after_instructions
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $taskId,
            $data['impact_type'] ?? 'other',
            $data['severity'] ?? 'medium',
            $this->sanitize($data['title'] ?? ''),
            $data['description'] ?: null,
            isset($data['affects_building_owner']) ? 1 : 0,
            isset($data['affects_tenants']) ? 1 : 0,
            isset($data['affects_neighbors']) ? 1 : 0,
            $data['impact_start_datetime'] ?: null,
            $data['impact_end_datetime'] ?: null,
            $data['estimated_duration_hours'] ?: null,
            $data['preparation_instructions'] ?: null,
            $data['during_instructions'] ?: null,
            $data['after_instructions'] ?: null,
        ]);

        $impactId = (int)$this->db->lastInsertId();

        return $this->json([
            'success' => true,
            'impact_id' => $impactId,
        ]);
    }

    public function publish(array $params): string
    {
        $this->requireAccount();

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid request'], 403);
        }

        $projectId = (int)($params['id'] ?? 0);
        $project = $this->getProject($projectId);

        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }

        $data = $this->getPostData();
        $notifyStakeholders = isset($data['notify_stakeholders']);
        $changeSummary = $data['change_summary'] ?? 'Plan has been updated';

        // Get current plan
        $stmt = $this->db->prepare("SELECT * FROM project_plans WHERE project_id = ? AND is_current = 1");
        $stmt->execute([$projectId]);
        $currentPlan = $stmt->fetch();

        if (!$currentPlan) {
            return $this->json(['error' => 'No plan found'], 404);
        }

        // Update published timestamp
        $stmt = $this->db->prepare("UPDATE project_plans SET published_at = NOW() WHERE id = ?");
        $stmt->execute([$currentPlan['id']]);

        // Create change notification record
        $stmt = $this->db->prepare("
            INSERT INTO plan_change_notifications (plan_id, change_type, change_summary, created_by)
            VALUES (?, 'new_version', ?, ?)
        ");
        $stmt->execute([$currentPlan['id'], $changeSummary, $this->currentUser['id']]);
        $notificationId = (int)$this->db->lastInsertId();

        // Send notifications to stakeholders
        if ($notifyStakeholders) {
            $this->notifyStakeholders($projectId, $project, $changeSummary, $notificationId);
        }

        $this->flash('success', 'Plan published' . ($notifyStakeholders ? ' and stakeholders notified' : ''));

        return $this->json(['success' => true]);
    }

    private function notifyStakeholders(int $projectId, array $project, string $changeSummary, int $notificationId): void
    {
        // Get stakeholders who should be notified
        $stmt = $this->db->prepare("
            SELECT * FROM project_stakeholders
            WHERE project_id = ? AND is_active = 1 AND notify_on_update = 1 AND email IS NOT NULL
        ");
        $stmt->execute([$projectId]);
        $stakeholders = $stmt->fetchAll();

        $mailService = new MailService();
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';

        foreach ($stakeholders as $stakeholder) {
            $shareUrl = $appUrl . '/share/' . $stakeholder['access_token'];

            // Record notification recipient
            $stmt = $this->db->prepare("
                INSERT INTO notification_recipients (notification_id, stakeholder_id, email, sent_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$notificationId, $stakeholder['id'], $stakeholder['email']]);

            // Send email
            $mailService->sendPlanUpdateNotification(
                $stakeholder['email'],
                $stakeholder['name'],
                $project['name'],
                $changeSummary,
                $shareUrl
            );
        }
    }

    private function getProject(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = ? AND account_id = ?");
        $stmt->execute([$id, $this->currentAccount['id']]);
        return $stmt->fetch() ?: null;
    }

    private function getTask(int $taskId, int $projectId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT pt.* FROM project_tasks pt
            JOIN project_plans pp ON pt.plan_id = pp.id
            WHERE pt.id = ? AND pp.project_id = ?
        ");
        $stmt->execute([$taskId, $projectId]);
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
