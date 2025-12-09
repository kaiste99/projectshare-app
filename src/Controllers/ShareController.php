<?php



namespace App\Controllers;

class ShareController extends BaseController
{
    public function index(array $params): string
    {
        $token = $params['token'] ?? '';
        $stakeholder = $this->getStakeholderByToken($token);

        if (!$stakeholder) {
            return $this->view('pages/share/invalid', [
                'title' => 'Invalid Link',
                'hideNav' => true,
            ]);
        }

        // Log access
        $this->logAccess($stakeholder['id'], 'link_opened');

        // Get project
        $stmt = $this->db->prepare("
            SELECT p.*, a.name as company_name, a.logo_path
            FROM projects p
            JOIN accounts a ON p.account_id = a.id
            WHERE p.id = ?
        ");
        $stmt->execute([$stakeholder['project_id']]);
        $project = $stmt->fetch();

        // Get current plan with tasks if allowed
        $tasks = [];
        $impacts = [];
        if ($stakeholder['can_view_schedule']) {
            $stmt = $this->db->prepare("
                SELECT pp.* FROM project_plans pp
                WHERE pp.project_id = ? AND pp.is_current = 1
            ");
            $stmt->execute([$project['id']]);
            $currentPlan = $stmt->fetch();

            if ($currentPlan) {
                $stmt = $this->db->prepare("
                    SELECT t.* FROM project_tasks t
                    WHERE t.plan_id = ?
                    ORDER BY t.sort_order, t.planned_start_date
                ");
                $stmt->execute([$currentPlan['id']]);
                $tasks = $stmt->fetchAll();
            }
        }

        // Get impacts if allowed
        if ($stakeholder['can_view_impacts']) {
            $stmt = $this->db->prepare("
                SELECT ti.*, pt.title as task_title, pt.planned_start_date, pt.planned_end_date
                FROM task_impacts ti
                JOIN project_tasks pt ON ti.task_id = pt.id
                JOIN project_plans pp ON pt.plan_id = pp.id
                WHERE pp.project_id = ? AND pp.is_current = 1
                ORDER BY ti.impact_start_datetime, pt.planned_start_date
            ");
            $stmt->execute([$project['id']]);
            $impacts = $stmt->fetchAll();
        }

        // Get files if allowed
        $files = [];
        if ($stakeholder['can_view_documents']) {
            $stmt = $this->db->prepare("
                SELECT * FROM attachments
                WHERE project_id = ? AND is_visible_to_stakeholders = 1
                ORDER BY created_at DESC
            ");
            $stmt->execute([$project['id']]);
            $files = $stmt->fetchAll();

            // Log document view
            $this->logAccess($stakeholder['id'], 'document_viewed');
        }

        return $this->view('pages/share/index', [
            'title' => $project['name'],
            'project' => $project,
            'stakeholder' => $stakeholder,
            'tasks' => $tasks,
            'impacts' => $impacts,
            'files' => $files,
            'token' => $token,
            'hideNav' => true,
            'hideFooter' => true,
        ]);
    }

    public function plan(array $params): string
    {
        $token = $params['token'] ?? '';
        $stakeholder = $this->getStakeholderByToken($token);

        if (!$stakeholder || !$stakeholder['can_view_schedule']) {
            return $this->view('pages/share/invalid', [
                'title' => 'Access Denied',
                'hideNav' => true,
            ]);
        }

        $this->logAccess($stakeholder['id'], 'plan_viewed');

        $stmt = $this->db->prepare("
            SELECT p.*, a.name as company_name
            FROM projects p
            JOIN accounts a ON p.account_id = a.id
            WHERE p.id = ?
        ");
        $stmt->execute([$stakeholder['project_id']]);
        $project = $stmt->fetch();

        // Get current plan with tasks
        $stmt = $this->db->prepare("
            SELECT pp.* FROM project_plans pp
            WHERE pp.project_id = ? AND pp.is_current = 1
        ");
        $stmt->execute([$project['id']]);
        $currentPlan = $stmt->fetch();

        $tasks = [];
        if ($currentPlan) {
            $stmt = $this->db->prepare("
                SELECT t.* FROM project_tasks t
                WHERE t.plan_id = ?
                ORDER BY t.sort_order, t.planned_start_date
            ");
            $stmt->execute([$currentPlan['id']]);
            $tasks = $stmt->fetchAll();

            // Get impacts for each task
            foreach ($tasks as &$task) {
                if ($stakeholder['can_view_impacts']) {
                    $stmt = $this->db->prepare("SELECT * FROM task_impacts WHERE task_id = ?");
                    $stmt->execute([$task['id']]);
                    $task['impacts'] = $stmt->fetchAll();
                } else {
                    $task['impacts'] = [];
                }
            }
        }

        return $this->view('pages/share/plan', [
            'title' => 'Project Plan - ' . $project['name'],
            'project' => $project,
            'stakeholder' => $stakeholder,
            'currentPlan' => $currentPlan,
            'tasks' => $tasks,
            'token' => $token,
            'hideNav' => true,
            'hideFooter' => true,
        ]);
    }

    public function files(array $params): string
    {
        $token = $params['token'] ?? '';
        $stakeholder = $this->getStakeholderByToken($token);

        if (!$stakeholder || !$stakeholder['can_view_documents']) {
            return $this->view('pages/share/invalid', [
                'title' => 'Access Denied',
                'hideNav' => true,
            ]);
        }

        $stmt = $this->db->prepare("
            SELECT p.*, a.name as company_name
            FROM projects p
            JOIN accounts a ON p.account_id = a.id
            WHERE p.id = ?
        ");
        $stmt->execute([$stakeholder['project_id']]);
        $project = $stmt->fetch();

        $stmt = $this->db->prepare("
            SELECT a.*,
                   (SELECT 1 FROM document_acknowledgements da WHERE da.attachment_id = a.id AND da.stakeholder_id = ?) as is_acknowledged
            FROM attachments a
            WHERE a.project_id = ? AND a.is_visible_to_stakeholders = 1
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$stakeholder['id'], $project['id']]);
        $files = $stmt->fetchAll();

        return $this->view('pages/share/files', [
            'title' => 'Files - ' . $project['name'],
            'project' => $project,
            'stakeholder' => $stakeholder,
            'files' => $files,
            'token' => $token,
            'hideNav' => true,
            'hideFooter' => true,
        ]);
    }

    public function viewFile(array $params): string
    {
        $token = $params['token'] ?? '';
        $fileId = (int)($params['id'] ?? 0);
        $stakeholder = $this->getStakeholderByToken($token);

        if (!$stakeholder || !$stakeholder['can_view_documents']) {
            http_response_code(403);
            return 'Access denied';
        }

        // Get file
        $stmt = $this->db->prepare("
            SELECT * FROM attachments
            WHERE id = ? AND project_id = ? AND is_visible_to_stakeholders = 1
        ");
        $stmt->execute([$fileId, $stakeholder['project_id']]);
        $file = $stmt->fetch();

        if (!$file) {
            http_response_code(404);
            return 'File not found';
        }

        // Log view
        $stmt = $this->db->prepare("
            INSERT INTO attachment_views (attachment_id, viewer_type, viewer_id, action, ip_address, user_agent)
            VALUES (?, 'stakeholder', ?, 'viewed', ?, ?)
        ");
        $stmt->execute([
            $fileId,
            $stakeholder['id'],
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        // Serve file
        $filePath = __DIR__ . '/../../uploads/attachments/' . $file['stored_name'];

        if (!file_exists($filePath)) {
            http_response_code(404);
            return 'File not found';
        }

        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: inline; filename="' . $file['original_name'] . '"');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }

    public function acknowledge(array $params): string
    {
        $token = $params['token'] ?? '';
        $fileId = (int)($params['fileId'] ?? 0);
        $stakeholder = $this->getStakeholderByToken($token);

        if (!$stakeholder) {
            return $this->json(['error' => 'Invalid token'], 403);
        }

        // Check file exists and requires acknowledgement
        $stmt = $this->db->prepare("
            SELECT * FROM attachments
            WHERE id = ? AND project_id = ? AND is_visible_to_stakeholders = 1 AND requires_acknowledgement = 1
        ");
        $stmt->execute([$fileId, $stakeholder['project_id']]);
        $file = $stmt->fetch();

        if (!$file) {
            return $this->json(['error' => 'File not found'], 404);
        }

        // Check if already acknowledged
        $stmt = $this->db->prepare("
            SELECT 1 FROM document_acknowledgements WHERE attachment_id = ? AND stakeholder_id = ?
        ");
        $stmt->execute([$fileId, $stakeholder['id']]);

        if (!$stmt->fetch()) {
            // Create acknowledgement
            $stmt = $this->db->prepare("
                INSERT INTO document_acknowledgements (attachment_id, stakeholder_id, acknowledged_at, ip_address)
                VALUES (?, ?, NOW(), ?)
            ");
            $stmt->execute([
                $fileId,
                $stakeholder['id'],
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            // Log access
            $this->logAccess($stakeholder['id'], 'acknowledged', 'attachment', $fileId);
        }

        return $this->json(['success' => true]);
    }

    private function getStakeholderByToken(string $token): ?array
    {
        if (empty($token)) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT * FROM project_stakeholders
            WHERE access_token = ? AND is_active = 1
            AND (access_token_expires_at IS NULL OR access_token_expires_at > NOW())
        ");
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    private function logAccess(int $stakeholderId, string $action, ?string $resourceType = null, ?int $resourceId = null): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO stakeholder_access_logs (stakeholder_id, action, resource_type, resource_id, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $stakeholderId,
            $action,
            $resourceType,
            $resourceId,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
}
