<?php



namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $this->requireAuth();

        // If no account, redirect to setup
        if (!$this->currentAccount) {
            $this->redirect('/account/setup');
        }

        // Get project stats
        $stats = $this->getProjectStats();

        // Get recent projects
        $stmt = $this->db->prepare("
            SELECT p.*,
                   (SELECT COUNT(*) FROM project_stakeholders ps WHERE ps.project_id = p.id AND ps.is_active = 1) as stakeholder_count,
                   (SELECT COUNT(*) FROM attachments a WHERE a.project_id = p.id) as file_count
            FROM projects p
            WHERE p.account_id = ?
            ORDER BY p.updated_at DESC
            LIMIT 5
        ");
        $stmt->execute([$this->currentAccount['id']]);
        $recentProjects = $stmt->fetchAll();

        // Get recent activity
        $stmt = $this->db->prepare("
            SELECT al.*, p.name as project_name, u.first_name, u.last_name, u.email
            FROM activity_logs al
            LEFT JOIN projects p ON al.project_id = p.id
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.account_id = ?
            ORDER BY al.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$this->currentAccount['id']]);
        $recentActivity = $stmt->fetchAll();

        // Get pending acknowledgements
        $stmt = $this->db->prepare("
            SELECT a.*, p.name as project_name, ps.name as stakeholder_name
            FROM attachments a
            JOIN projects p ON a.project_id = p.id
            JOIN project_stakeholders ps ON ps.project_id = p.id
            LEFT JOIN document_acknowledgements da ON da.attachment_id = a.id AND da.stakeholder_id = ps.id
            WHERE p.account_id = ? AND a.requires_acknowledgement = 1 AND da.id IS NULL
            LIMIT 5
        ");
        $stmt->execute([$this->currentAccount['id']]);
        $pendingAcknowledgements = $stmt->fetchAll();

        return $this->view('pages/dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'recentActivity' => $recentActivity,
            'pendingAcknowledgements' => $pendingAcknowledgements,
            'flash' => $this->getFlash(),
        ]);
    }

    private function getProjectStats(): array
    {
        $accountId = $this->currentAccount['id'];

        // Total projects
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM projects WHERE account_id = ?");
        $stmt->execute([$accountId]);
        $totalProjects = (int)$stmt->fetchColumn();

        // Active projects
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM projects WHERE account_id = ? AND status IN ('planning', 'in_progress')");
        $stmt->execute([$accountId]);
        $activeProjects = (int)$stmt->fetchColumn();

        // Total stakeholders
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT ps.id)
            FROM project_stakeholders ps
            JOIN projects p ON ps.project_id = p.id
            WHERE p.account_id = ? AND ps.is_active = 1
        ");
        $stmt->execute([$accountId]);
        $totalStakeholders = (int)$stmt->fetchColumn();

        // Documents viewed this week
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM attachment_views av
            JOIN attachments a ON av.attachment_id = a.id
            JOIN projects p ON a.project_id = p.id
            WHERE p.account_id = ? AND av.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute([$accountId]);
        $viewsThisWeek = (int)$stmt->fetchColumn();

        return [
            'total_projects' => $totalProjects,
            'active_projects' => $activeProjects,
            'total_stakeholders' => $totalStakeholders,
            'views_this_week' => $viewsThisWeek,
        ];
    }
}
