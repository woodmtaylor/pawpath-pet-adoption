<?php
namespace PawPath\services;

use PawPath\config\database\DatabaseConfig;
use PawPath\models\User;
use RuntimeException;
use PDO;

class UserRoleService {
    private PDO $db;
    private User $userModel;
    
    private const VALID_ROLES = ['adopter', 'shelter_staff', 'admin'];
    private const ROLE_HIERARCHY = [
        'admin' => ['admin', 'shelter_staff', 'adopter'],
        'shelter_staff' => ['shelter_staff', 'adopter'],
        'adopter' => ['adopter']
    ];
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
        $this->userModel = new User();
    }
    
    public function changeUserRole(int $userId, string $newRole, int $changedBy, string $reason = null): bool {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Get user's current role
            $user = $this->userModel->findById($userId);
            if (!$user) {
                throw new RuntimeException('User not found');
            }
            
            // Get admin user making the change
            $admin = $this->userModel->findById($changedBy);
            if (!$admin) {
                throw new RuntimeException('Admin user not found');
            }
            
            // Validate role change permissions
            if (!$this->canChangeRole($admin['role'], $user['role'], $newRole)) {
                throw new RuntimeException('Insufficient permissions to change role');
            }
            
            // Update user's role
            $stmt = $this->db->prepare("
                UPDATE User 
                SET role = ? 
                WHERE user_id = ?
            ");
            
            $stmt->execute([$newRole, $userId]);
            
            // Log the role change
            $stmt = $this->db->prepare("
                INSERT INTO RoleChangeLog (
                    user_id, old_role, new_role, 
                    changed_by, reason
                ) VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $user['role'],
                $newRole,
                $changedBy,
                $reason
            ]);
            
            // If changing to shelter staff, may need additional setup
            if ($newRole === 'shelter_staff') {
                // Handle shelter staff specific setup here
                // e.g., default shelter assignment
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function assignToShelter(int $userId, int $shelterId, string $position = 'staff'): bool {
        try {
            $this->db->beginTransaction();
            
            // Verify user is shelter_staff
            $user = $this->userModel->findById($userId);
            if ($user['role'] !== 'shelter_staff') {
                throw new RuntimeException('User must be shelter staff');
            }
            
            // Add shelter staff relationship
            $stmt = $this->db->prepare("
                INSERT INTO ShelterStaff (
                    shelter_id, user_id, position
                ) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE position = ?
            ");
            
            $stmt->execute([$shelterId, $userId, $position, $position]);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getUsersByRole(string $role): array {
        $stmt = $this->db->prepare("
            SELECT user_id, username, email, registration_date, account_status
            FROM User 
            WHERE role = ?
            ORDER BY username
        ");
        
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }
    
    private function canChangeRole(string $adminRole, string $currentRole, string $newRole): bool {
        // Only admins can change roles
        if ($adminRole !== 'admin') {
            return false;
        }
        
        // Validate role exists
        if (!in_array($newRole, self::VALID_ROLES)) {
            return false;
        }
        
        // Special case: can't change own role or other admins
        if ($currentRole === 'admin') {
            return false;
        }
        
        return true;
    }
}
