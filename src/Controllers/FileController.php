<?php



namespace App\Controllers;

class FileController extends BaseController
{
    private array $allowedTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'zip' => 'application/zip',
    ];

    public function upload(array $params): string
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

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['error' => 'No file uploaded or upload error'], 400);
        }

        $file = $_FILES['file'];
        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Validate file type
        if (!isset($this->allowedTypes[$extension])) {
            return $this->json(['error' => 'File type not allowed'], 400);
        }

        // Validate file size (50MB max)
        $maxSize = (int)($_ENV['MAX_UPLOAD_SIZE'] ?? 52428800);
        if ($file['size'] > $maxSize) {
            return $this->json(['error' => 'File too large. Maximum size is ' . ($maxSize / 1024 / 1024) . 'MB'], 400);
        }

        // Generate unique filename
        $storedName = uniqid('file_', true) . '.' . $extension;
        $uploadDir = __DIR__ . '/../../uploads/attachments/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return $this->json(['error' => 'Failed to save file'], 500);
        }

        // Get form data
        $category = $_POST['category'] ?? 'other';
        $description = $_POST['description'] ?? null;
        $isVisible = isset($_POST['is_visible_to_stakeholders']) ? 1 : 1;
        $requiresAck = isset($_POST['requires_acknowledgement']) ? 1 : 0;
        $planId = !empty($_POST['plan_id']) ? (int)$_POST['plan_id'] : null;
        $taskId = !empty($_POST['task_id']) ? (int)$_POST['task_id'] : null;

        // Save to database
        $stmt = $this->db->prepare("
            INSERT INTO attachments (
                project_id, plan_id, task_id, original_name, stored_name, file_path,
                file_size, mime_type, file_extension, category, description,
                is_visible_to_stakeholders, requires_acknowledgement, uploaded_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $projectId,
            $planId,
            $taskId,
            $originalName,
            $storedName,
            'uploads/attachments/' . $storedName,
            $file['size'],
            $this->allowedTypes[$extension],
            $extension,
            $category,
            $description,
            $isVisible,
            $requiresAck,
            $this->currentUser['id'],
        ]);

        $fileId = (int)$this->db->lastInsertId();

        // Log activity
        $this->logActivity('uploaded file', 'attachment', $fileId, $projectId);

        return $this->json([
            'success' => true,
            'file' => [
                'id' => $fileId,
                'name' => $originalName,
                'size' => $file['size'],
                'extension' => $extension,
            ]
        ]);
    }

    public function delete(array $params): string
    {
        $this->requireAccount();

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid request'], 403);
        }

        $projectId = (int)($params['id'] ?? 0);
        $fileId = (int)($params['fileId'] ?? 0);

        $project = $this->getProject($projectId);
        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }

        // Get file
        $stmt = $this->db->prepare("SELECT * FROM attachments WHERE id = ? AND project_id = ?");
        $stmt->execute([$fileId, $projectId]);
        $file = $stmt->fetch();

        if (!$file) {
            return $this->json(['error' => 'File not found'], 404);
        }

        // Delete physical file
        $filePath = __DIR__ . '/../../' . $file['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete from database
        $stmt = $this->db->prepare("DELETE FROM attachments WHERE id = ?");
        $stmt->execute([$fileId]);

        // Log activity
        $this->logActivity('deleted file', 'attachment', $fileId, $projectId);

        return $this->json(['success' => true]);
    }

    private function getProject(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = ? AND account_id = ?");
        $stmt->execute([$id, $this->currentAccount['id']]);
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
