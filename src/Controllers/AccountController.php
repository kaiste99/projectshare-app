<?php



namespace App\Controllers;

use App\Services\MailService;

class AccountController extends BaseController
{
    public function index(): string
    {
        $this->requireAuth();

        // Check if user has an account, if not redirect to setup
        if (!$this->currentAccount) {
            return $this->showSetup();
        }

        return $this->view('pages/account/index', [
            'title' => 'Account',
            'flash' => $this->getFlash(),
        ]);
    }

    public function showSetup(): string
    {
        $this->requireAuth();

        return $this->view('pages/account/setup', [
            'title' => 'Set Up Your Account',
            'csrf' => $this->csrfToken(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function setup(): string
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/account/setup');
        }

        $data = $this->getPostData();

        // Validate required fields
        $companyName = trim($data['company_name'] ?? '');
        $industry = $data['industry'] ?? 'other';

        if (empty($companyName)) {
            $this->flash('error', 'Company name is required');
            $this->redirect('/account/setup');
        }

        // Generate slug
        $slug = $this->generateSlug($companyName);

        try {
            $this->db->beginTransaction();

            // Create account
            $stmt = $this->db->prepare("
                INSERT INTO accounts (name, slug, industry, owner_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$companyName, $slug, $industry, $this->currentUser['id']]);
            $accountId = (int)$this->db->lastInsertId();

            // Add owner as member
            $stmt = $this->db->prepare("
                INSERT INTO account_members (account_id, user_id, role, accepted_at)
                VALUES (?, ?, 'owner', NOW())
            ");
            $stmt->execute([$accountId, $this->currentUser['id']]);

            // Update user profile if provided
            $firstName = trim($data['first_name'] ?? '');
            $lastName = trim($data['last_name'] ?? '');

            if ($firstName || $lastName) {
                $stmt = $this->db->prepare("
                    UPDATE users SET first_name = ?, last_name = ?, company_name = ?
                    WHERE id = ?
                ");
                $stmt->execute([$firstName ?: null, $lastName ?: null, $companyName, $this->currentUser['id']]);
            }

            $this->db->commit();

            $this->flash('success', 'Account created successfully!');
            $this->redirect('/dashboard');

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->flash('error', 'Failed to create account. Please try again.');
            $this->redirect('/account/setup');
        }

        return '';
    }

    public function settings(): string
    {
        $this->requireAccount();

        return $this->view('pages/account/settings', [
            'title' => 'Account Settings',
            'csrf' => $this->csrfToken(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function updateSettings(): string
    {
        $this->requireAccount();

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid request'], 403);
        }

        $data = $this->getPostData();

        // Update account
        $stmt = $this->db->prepare("
            UPDATE accounts SET
                name = ?,
                description = ?,
                address_line1 = ?,
                address_line2 = ?,
                city = ?,
                postal_code = ?,
                phone = ?,
                website = ?,
                industry = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $this->sanitize($data['name'] ?? ''),
            $data['description'] ?? null,
            $data['address_line1'] ?? null,
            $data['address_line2'] ?? null,
            $data['city'] ?? null,
            $data['postal_code'] ?? null,
            $data['phone'] ?? null,
            $data['website'] ?? null,
            $data['industry'] ?? 'other',
            $this->currentAccount['id'],
        ]);

        // Update user profile
        $stmt = $this->db->prepare("
            UPDATE users SET first_name = ?, last_name = ?, phone = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $this->sanitize($data['first_name'] ?? ''),
            $this->sanitize($data['last_name'] ?? ''),
            $data['user_phone'] ?? null,
            $this->currentUser['id'],
        ]);

        $this->flash('success', 'Settings updated successfully');
        $this->redirect('/account/settings');
        return '';
    }

    public function team(): string
    {
        $this->requireAccount();

        // Get team members
        $stmt = $this->db->prepare("
            SELECT am.*, u.email, u.first_name, u.last_name, u.avatar_path,
                   inv.first_name as inviter_first_name, inv.last_name as inviter_last_name
            FROM account_members am
            JOIN users u ON am.user_id = u.id
            LEFT JOIN users inv ON am.invited_by = inv.id
            WHERE am.account_id = ?
            ORDER BY am.role = 'owner' DESC, am.created_at ASC
        ");
        $stmt->execute([$this->currentAccount['id']]);
        $members = $stmt->fetchAll();

        // Get pending invitations
        $stmt = $this->db->prepare("
            SELECT ai.*, inv.first_name as inviter_first_name, inv.last_name as inviter_last_name
            FROM account_invitations ai
            JOIN users inv ON ai.invited_by = inv.id
            WHERE ai.account_id = ? AND ai.accepted_at IS NULL AND ai.expires_at > NOW()
            ORDER BY ai.created_at DESC
        ");
        $stmt->execute([$this->currentAccount['id']]);
        $invitations = $stmt->fetchAll();

        return $this->view('pages/account/team', [
            'title' => 'Team Members',
            'members' => $members,
            'invitations' => $invitations,
            'csrf' => $this->csrfToken(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function showInvite(): string
    {
        $this->requireAccount();

        // Check if user can invite (owner or admin)
        if (!in_array($this->currentAccount['member_role'], ['owner', 'admin'])) {
            $this->flash('error', 'You do not have permission to invite team members');
            $this->redirect('/account/team');
        }

        return $this->view('pages/account/invite', [
            'title' => 'Invite Team Member',
            'csrf' => $this->csrfToken(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function sendInvite(): string
    {
        $this->requireAccount();

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/account/team/invite');
        }

        // Check permissions
        if (!in_array($this->currentAccount['member_role'], ['owner', 'admin'])) {
            $this->flash('error', 'You do not have permission to invite team members');
            $this->redirect('/account/team');
        }

        $data = $this->getPostData();
        $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $role = in_array($data['role'] ?? '', ['admin', 'manager', 'member']) ? $data['role'] : 'member';

        if (!$email) {
            $this->flash('error', 'Please enter a valid email address');
            $this->redirect('/account/team/invite');
        }

        // Check if already a member
        $stmt = $this->db->prepare("
            SELECT 1 FROM account_members am
            JOIN users u ON am.user_id = u.id
            WHERE am.account_id = ? AND u.email = ?
        ");
        $stmt->execute([$this->currentAccount['id'], $email]);
        if ($stmt->fetch()) {
            $this->flash('error', 'This person is already a team member');
            $this->redirect('/account/team/invite');
        }

        // Check for existing invitation
        $stmt = $this->db->prepare("
            SELECT 1 FROM account_invitations
            WHERE account_id = ? AND email = ? AND accepted_at IS NULL AND expires_at > NOW()
        ");
        $stmt->execute([$this->currentAccount['id'], $email]);
        if ($stmt->fetch()) {
            $this->flash('error', 'An invitation has already been sent to this email');
            $this->redirect('/account/team/invite');
        }

        // Create invitation
        $token = $this->generateToken();
        $stmt = $this->db->prepare("
            INSERT INTO account_invitations (account_id, email, role, token, invited_by, expires_at)
            VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))
        ");
        $stmt->execute([
            $this->currentAccount['id'],
            strtolower($email),
            $role,
            $token,
            $this->currentUser['id'],
        ]);

        // Send invitation email
        $inviterName = trim(($this->currentUser['first_name'] ?? '') . ' ' . ($this->currentUser['last_name'] ?? '')) ?: $this->currentUser['email'];
        $inviteUrl = ($_ENV['APP_URL'] ?? 'http://localhost:8000') . '/invite/' . $token;

        $mailService = new MailService();
        $mailService->sendTeamInvitation($email, $inviterName, $this->currentAccount['name'], $inviteUrl);

        $this->flash('success', 'Invitation sent to ' . $email);
        $this->redirect('/account/team');
        return '';
    }

    public function removeMember(array $params): string
    {
        $this->requireAccount();

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid request'], 403);
        }

        $memberId = (int)($params['id'] ?? 0);

        // Check permissions
        if (!in_array($this->currentAccount['member_role'], ['owner', 'admin'])) {
            return $this->json(['error' => 'Permission denied'], 403);
        }

        // Get member info
        $stmt = $this->db->prepare("
            SELECT * FROM account_members WHERE id = ? AND account_id = ?
        ");
        $stmt->execute([$memberId, $this->currentAccount['id']]);
        $member = $stmt->fetch();

        if (!$member) {
            return $this->json(['error' => 'Member not found'], 404);
        }

        // Cannot remove owner
        if ($member['role'] === 'owner') {
            return $this->json(['error' => 'Cannot remove account owner'], 400);
        }

        // Cannot remove yourself
        if ($member['user_id'] == $this->currentUser['id']) {
            return $this->json(['error' => 'Cannot remove yourself'], 400);
        }

        // Soft delete - mark as inactive
        $stmt = $this->db->prepare("UPDATE account_members SET is_active = 0 WHERE id = ?");
        $stmt->execute([$memberId]);

        return $this->json(['success' => true]);
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Check uniqueness
        $baseSlug = $slug;
        $counter = 1;

        while (true) {
            $stmt = $this->db->prepare("SELECT 1 FROM accounts WHERE slug = ?");
            $stmt->execute([$slug]);
            if (!$stmt->fetch()) {
                break;
            }
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}
