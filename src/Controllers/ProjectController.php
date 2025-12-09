<?php



namespace App\Controllers;

class ProjectController extends BaseController
{
    public function index(): string
    {
        $this->requireAccount();

        $status = $_GET['status'] ?? 'all';
        $search = $_GET['search'] ?? '';

        $sql = "
            SELECT p.*,
                   (SELECT COUNT(*) FROM project_stakeholders ps WHERE ps.project_id = p.id AND ps.is_active = 1) as stakeholder_count,
                   (SELECT COUNT(*) FROM attachments a WHERE a.project_id = p.id) as file_count,
                   pm.first_name as pm_first_name, pm.last_name as pm_last_name
            FROM projects p
            LEFT JOIN users pm ON p.project_manager_id = pm.id
            WHERE p.account_id = ?
        ";
        $params = [$this->currentAccount['id']];

        if ($status !== 'all' && in_array($status, ['draft', 'planning', 'in_progress', 'on_hold', 'completed', 'cancelled'])) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }

        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.reference_number LIKE ? OR p.city LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY p.updated_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $projects = $stmt->fetchAll();

        return $this->view('pages/projects/index', [
            'title' => 'Projects',
            'projects' => $projects,
            'currentStatus' => $status,
            'search' => $search,
            'flash' => $this->getFlash(),
        ]);
    }

    public function create(): string
    {
        $this->requireAccount();

        // Get team members for assignment
        $stmt = $this->db->prepare("
            SELECT u.id, u.email, u.first_name, u.last_name
            FROM users u
            JOIN account_members am ON u.id = am.user_id
            WHERE am.account_id = ? AND am.is_active = 1
        ");
        $stmt->execute([$this->currentAccount['id']]);
        $teamMembers = $stmt->fetchAll();

        return $this->view('pages/projects/create', [
            'title' => 'Create Project',
            'teamMembers' => $teamMembers,
            'csrf' => $this->csrfToken(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function store(): string
    {
        $this->requireAccount();

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/projects/create');
        }

        $data = $this->getPostData();

        // Validate
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            $this->flash('error', 'Project name is required');
            $this->redirect('/projects/create');
        }

        $stmt = $this->db->prepare("
            INSERT INTO projects (
                account_id, name, reference_number, description, project_type, status,
                address_line1, address_line2, city, postal_code,
                planned_start_date, planned_end_date,
                project_manager_id, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $this->currentAccount['id'],
            $this->sanitize($name),
            $data['reference_number'] ?: null,
            $data['description'] ?: null,
            $data['project_type'] ?? 'other',
            'draft',
            $data['address_line1'] ?: null,
            $data['address_line2'] ?: null,
            $data['city'] ?: null,
            $data['postal_code'] ?: null,
            $data['planned_start_date'] ?: null,
            $data['planned_end_date'] ?: null,
            $data['project_manager_id'] ?: null,
            $this->currentUser['id'],
        ]);

        $projectId = (int)$this->db->lastInsertId();

        // Create initial plan
        $stmt = $this->db->prepare("
            INSERT INTO project_plans (project_id, version, name, created_by)
            VALUES (?, 1, 'Initial Plan', ?)
        ");
        $stmt->execute([$projectId, $this->currentUser['id']]);

        // Log activity
        $this->logActivity('created project', 'project', $projectId);

        $this->flash('success', 'Project created successfully');
        $this->redirect('/projects/' . $projectId);
        return '';
    }

    public function show(array $params): string
    {
        $this->requireAccount();

        $projectId = (int)($params['id'] ?? 0);
        $project = $this->getProject($projectId);

        if (!$project) {
            $this->flash('error', 'Project not found');
            $this->redirect('/projects');
        }

        // Get current plan with tasks
        $stmt = $this->db->prepare("
            SELECT * FROM project_plans
            WHERE project_id = ? AND is_current = 1
            ORDER BY version DESC LIMIT 1
        ");
        $stmt->execute([$projectId]);
        $currentPlan = $stmt->fetch();

        $tasks = [];
        if ($currentPlan) {
            $stmt = $this->db->prepare("
                SELECT t.*, GROUP_CONCAT(ti.impact_type) as impact_types
                FROM project_tasks t
                LEFT JOIN task_impacts ti ON t.id = ti.task_id
                WHERE t.plan_id = ?
                GROUP BY t.id
                ORDER BY t.sort_order, t.planned_start_date
            ");
            $stmt->execute([$currentPlan['id']]);
            $tasks = $stmt->fetchAll();
        }

        // Get stakeholders
        $stmt = $this->db->prepare("
            SELECT ps.*,
                   (SELECT COUNT(*) FROM stakeholder_access_logs sal WHERE sal.stakeholder_id = ps.id) as view_count,
                   (SELECT MAX(sal.created_at) FROM stakeholder_access_logs sal WHERE sal.stakeholder_id = ps.id) as last_viewed
            FROM project_stakeholders ps
            WHERE ps.project_id = ? AND ps.is_active = 1
        ");
        $stmt->execute([$projectId]);
        $stakeholders = $stmt->fetchAll();

        // Get recent files
        $stmt = $this->db->prepare("
            SELECT a.*,
                   (SELECT COUNT(*) FROM attachment_views av WHERE av.attachment_id = a.id) as view_count
            FROM attachments a
            WHERE a.project_id = ?
            ORDER BY a.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$projectId]);
        $recentFiles = $stmt->fetchAll();

        return $this->view('pages/projects/show', [
            'title' => $project['name'],
            'project' => $project,
            'currentPlan' => $currentPlan,
            'tasks' => $tasks,
            'stakeholders' => $stakeholders,
            'recentFiles' => $recentFiles,
            'csrf' => $this->csrfToken(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function edit(array $params): string
    {
        $this->requireAccount();

        $projectId = (int)($params['id'] ?? 0);
        $project = $this->getProject($projectId);

        if (!$project) {
            $this->flash('error', 'Project not found');
            $this->redirect('/projects');
        }

        // Get team members
        $stmt = $this->db->prepare("
            SELECT u.id, u.email, u.first_name, u.last_name
            FROM users u
            JOIN account_members am ON u.id = am.user_id
            WHERE am.account_id = ? AND am.is_active = 1
        ");
        $stmt->execute([$this->currentAccount['id']]);
        $teamMembers = $stmt->fetchAll();

        return $this->view('pages/projects/edit', [
            'title' => 'Edit: ' . $project['name'],
            'project' => $project,
            'teamMembers' => $teamMembers,
            'csrf' => $this->csrfToken(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function update(array $params): string
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

        $stmt = $this->db->prepare("
            UPDATE projects SET
                name = ?,
                reference_number = ?,
                description = ?,
                project_type = ?,
                address_line1 = ?,
                address_line2 = ?,
                city = ?,
                postal_code = ?,
                planned_start_date = ?,
                planned_end_date = ?,
                project_manager_id = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $this->sanitize($data['name'] ?? ''),
            $data['reference_number'] ?: null,
            $data['description'] ?: null,
            $data['project_type'] ?? 'other',
            $data['address_line1'] ?: null,
            $data['address_line2'] ?: null,
            $data['city'] ?: null,
            $data['postal_code'] ?: null,
            $data['planned_start_date'] ?: null,
            $data['planned_end_date'] ?: null,
            $data['project_manager_id'] ?: null,
            $projectId,
        ]);

        $this->logActivity('updated project', 'project', $projectId);

        $this->flash('success', 'Project updated successfully');
        $this->redirect('/projects/' . $projectId);
        return '';
    }

    public function updateStatus(array $params): string
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
        $status = $data['status'] ?? '';

        if (!in_array($status, ['draft', 'planning', 'in_progress', 'on_hold', 'completed', 'cancelled'])) {
            return $this->json(['error' => 'Invalid status'], 400);
        }

        $stmt = $this->db->prepare("UPDATE projects SET status = ? WHERE id = ?");
        $stmt->execute([$status, $projectId]);

        // Set actual dates
        if ($status === 'in_progress' && !$project['actual_start_date']) {
            $stmt = $this->db->prepare("UPDATE projects SET actual_start_date = CURDATE() WHERE id = ?");
            $stmt->execute([$projectId]);
        }

        if ($status === 'completed' && !$project['actual_end_date']) {
            $stmt = $this->db->prepare("UPDATE projects SET actual_end_date = CURDATE() WHERE id = ?");
            $stmt->execute([$projectId]);
        }

        $this->logActivity('changed status to ' . $status, 'project', $projectId);

        return $this->json(['success' => true]);
    }

    public function stakeholders(array $params): string
    {
        $this->requireAccount();

        $projectId = (int)($params['id'] ?? 0);
        $project = $this->getProject($projectId);

        if (!$project) {
            $this->flash('error', 'Project not found');
            $this->redirect('/projects');
        }

        // Get stakeholders with activity
        $stmt = $this->db->prepare("
            SELECT ps.*,
                   (SELECT COUNT(*) FROM stakeholder_access_logs sal WHERE sal.stakeholder_id = ps.id) as total_views,
                   (SELECT MAX(sal.created_at) FROM stakeholder_access_logs sal WHERE sal.stakeholder_id = ps.id) as last_activity
            FROM project_stakeholders ps
            WHERE ps.project_id = ?
            ORDER BY ps.stakeholder_type, ps.name
        ");
        $stmt->execute([$projectId]);
        $stakeholders = $stmt->fetchAll();

        return $this->view('pages/projects/stakeholders', [
            'title' => 'Stakeholders: ' . $project['name'],
            'project' => $project,
            'stakeholders' => $stakeholders,
            'csrf' => $this->csrfToken(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function files(array $params): string
    {
        $this->requireAccount();

        $projectId = (int)($params['id'] ?? 0);
        $project = $this->getProject($projectId);

        if (!$project) {
            $this->flash('error', 'Project not found');
            $this->redirect('/projects');
        }

        // Get files with views
        $stmt = $this->db->prepare("
            SELECT a.*,
                   u.first_name as uploader_first_name, u.last_name as uploader_last_name,
                   (SELECT COUNT(*) FROM attachment_views av WHERE av.attachment_id = a.id) as view_count,
                   (SELECT COUNT(*) FROM document_acknowledgements da WHERE da.attachment_id = a.id) as ack_count
            FROM attachments a
            JOIN users u ON a.uploaded_by = u.id
            WHERE a.project_id = ?
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$projectId]);
        $files = $stmt->fetchAll();

        return $this->view('pages/projects/files', [
            'title' => 'Files: ' . $project['name'],
            'project' => $project,
            'files' => $files,
            'csrf' => $this->csrfToken(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function plan(array $params): string
    {
        $this->requireAccount();

        $projectId = (int)($params['id'] ?? 0);
        $project = $this->getProject($projectId);

        if (!$project) {
            $this->flash('error', 'Project not found');
            $this->redirect('/projects');
        }

        // Get current plan
        $stmt = $this->db->prepare("
            SELECT * FROM project_plans
            WHERE project_id = ? AND is_current = 1
        ");
        $stmt->execute([$projectId]);
        $currentPlan = $stmt->fetch();

        // Get tasks with impacts
        $tasks = [];
        if ($currentPlan) {
            $stmt = $this->db->prepare("
                SELECT t.*
                FROM project_tasks t
                WHERE t.plan_id = ?
                ORDER BY t.sort_order, t.planned_start_date
            ");
            $stmt->execute([$currentPlan['id']]);
            $tasks = $stmt->fetchAll();

            // Get impacts for each task
            foreach ($tasks as &$task) {
                $stmt = $this->db->prepare("SELECT * FROM task_impacts WHERE task_id = ?");
                $stmt->execute([$task['id']]);
                $task['impacts'] = $stmt->fetchAll();
            }
        }

        // Get plan versions
        $stmt = $this->db->prepare("
            SELECT pp.*, u.first_name, u.last_name
            FROM project_plans pp
            JOIN users u ON pp.created_by = u.id
            WHERE pp.project_id = ?
            ORDER BY pp.version DESC
        ");
        $stmt->execute([$projectId]);
        $planVersions = $stmt->fetchAll();

        return $this->view('pages/projects/plan', [
            'title' => 'Project Plan: ' . $project['name'],
            'project' => $project,
            'currentPlan' => $currentPlan,
            'tasks' => $tasks,
            'planVersions' => $planVersions,
            'csrf' => $this->csrfToken(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function feedback(array $params): string
    {
        $this->requireAccount();

        $projectId = (int)($params['id'] ?? 0);
        $project = $this->getProject($projectId);

        if (!$project) {
            $this->flash('error', 'Project not found');
            $this->redirect('/projects');
        }

        // Get feedback
        $stmt = $this->db->prepare("
            SELECT pf.*, ps.name as stakeholder_name, ps.stakeholder_type,
                   u.first_name, u.last_name
            FROM project_feedback pf
            LEFT JOIN project_stakeholders ps ON pf.stakeholder_id = ps.id
            LEFT JOIN users u ON pf.user_id = u.id
            WHERE pf.project_id = ?
            ORDER BY pf.submitted_at DESC
        ");
        $stmt->execute([$projectId]);
        $feedbackList = $stmt->fetchAll();

        // Get pending feedback requests
        $stmt = $this->db->prepare("
            SELECT fr.*, ps.name as stakeholder_name, ps.email
            FROM feedback_requests fr
            JOIN project_stakeholders ps ON fr.stakeholder_id = ps.id
            WHERE fr.project_id = ? AND fr.completed_at IS NULL
        ");
        $stmt->execute([$projectId]);
        $pendingRequests = $stmt->fetchAll();

        return $this->view('pages/projects/feedback', [
            'title' => 'Feedback: ' . $project['name'],
            'project' => $project,
            'feedbackList' => $feedbackList,
            'pendingRequests' => $pendingRequests,
            'csrf' => $this->csrfToken(),
            'flash' => $this->getFlash(),
        ]);
    }

    private function getProject(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, pm.first_name as pm_first_name, pm.last_name as pm_last_name
            FROM projects p
            LEFT JOIN users pm ON p.project_manager_id = pm.id
            WHERE p.id = ? AND p.account_id = ?
        ");
        $stmt->execute([$id, $this->currentAccount['id']]);
        return $stmt->fetch() ?: null;
    }

    private function logActivity(string $action, string $resourceType, int $resourceId): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO activity_logs (user_id, account_id, project_id, action, resource_type, resource_id, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $this->currentUser['id'],
            $this->currentAccount['id'],
            $resourceType === 'project' ? $resourceId : null,
            $action,
            $resourceType,
            $resourceId,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}
