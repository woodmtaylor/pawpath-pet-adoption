<?php
namespace PawPath\api;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PawPath\models\User;
use PawPath\models\Shelter;
use PawPath\services\EmailService;
use PawPath\utils\ResponseHelper;
use PawPath\config\database\DatabaseConfig;
use PDO;

class AdminController {
    private User $userModel;
    private EmailService $emailService;
    private PDO $db;
    private Shelter $shelterModel;

    public function __construct() {
        $this->userModel = new User();
        $this->emailService = new EmailService();
        $this->db = DatabaseConfig::getConnection();
        $this->shelterModel = new Shelter();
    }

    public function getStats(Request $request, Response $response): Response {
        try {
            $stats = [
                'totalUsers' => $this->userModel->countUsers(),
                'totalShelters' => $this->userModel->countByRole('shelter_staff'),
                'totalPets' => count($this->userModel->findAll(['role' => 'admin'])),
                'totalApplications' => 0, // Implement this with your applications model
                'pendingApplications' => 0,
                'activeUsers' => $this->userModel->countByStatus('active'),
                'recentActivity' => [] // Implement activity logging if needed
            ];

            return ResponseHelper::sendResponse($response, $stats);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage());
        }
    }

    public function listUsers(Request $request, Response $response): Response {
        try {
            $queryParams = $request->getQueryParams();
            $filters = [];

            // Handle search
            if (!empty($queryParams['search'])) {
                $filters['search'] = $queryParams['search'];
            }

            // Handle role filter
            if (!empty($queryParams['role']) && $queryParams['role'] !== 'all') {
                $filters['role'] = $queryParams['role'];
            }

            // Handle status filter
            if (!empty($queryParams['status']) && $queryParams['status'] !== 'all') {
                $filters['account_status'] = $queryParams['status'];
            }

            $users = $this->userModel->findAll($filters);

            return ResponseHelper::sendResponse($response, $users);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage());
        }
    }

    public function updateUserRole(Request $request, Response $response, array $args): Response {
        try {
            $userId = (int) $args['id'];
            $data = $request->getParsedBody();

            if (empty($data['role'])) {
                throw new \InvalidArgumentException('Role is required');
            }

            $success = $this->userModel->updateRole($userId, $data['role']);

            if (!$success) {
                throw new \RuntimeException('Failed to update user role');
            }

            $updatedUser = $this->userModel->findById($userId);
            return ResponseHelper::sendResponse($response, $updatedUser);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage());
        }
    }

    public function updateUserStatus(Request $request, Response $response, array $args): Response {
        try {
            $userId = (int) $args['id'];
            $data = $request->getParsedBody();

            if (empty($data['status'])) {
                throw new \InvalidArgumentException('Status is required');
            }

            $success = $this->userModel->updateAccountStatus($userId, $data['status']);

            if (!$success) {
                throw new \RuntimeException('Failed to update user status');
            }

            $updatedUser = $this->userModel->findById($userId);
            return ResponseHelper::sendResponse($response, $updatedUser);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage());
        }
    }

    public function listShelters(Request $request, Response $response): Response {
        try {
            $queryParams = $request->getQueryParams();
            $filters = [];

            // Handle search
            if (!empty($queryParams['search'])) {
                $filters['search'] = $queryParams['search'];
            }

            // Handle no-kill filter
            if (isset($queryParams['is_no_kill'])) {
                $filters['is_no_kill'] = (bool)$queryParams['is_no_kill'];
            }

            $shelterModel = new \PawPath\models\Shelter();
            $shelters = $shelterModel->findAll($filters);

            // Get additional stats for each shelter
            foreach ($shelters as &$shelter) {
                // Get total pets
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) FROM Pet 
                    WHERE shelter_id = ?
                ");
                $stmt->execute([$shelter['shelter_id']]);
                $shelter['total_pets'] = (int)$stmt->fetchColumn();

                // Get active applications
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) FROM Adoption_Application aa
                    JOIN Pet p ON aa.pet_id = p.pet_id
                    WHERE p.shelter_id = ? 
                    AND aa.status IN ('pending', 'under_review')
                ");
                $stmt->execute([$shelter['shelter_id']]);
                $shelter['active_applications'] = (int)$stmt->fetchColumn();
            }

            return ResponseHelper::sendResponse($response, $shelters);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage());
        }
    }

    public function createShelter(Request $request, Response $response): Response {
        try {
            $data = $request->getParsedBody();
            
            if (empty($data['name']) || empty($data['address']) || empty($data['phone']) || empty($data['email'])) {
                throw new \InvalidArgumentException('Missing required shelter information');
            }

            $shelterModel = new \PawPath\models\Shelter();
            $shelterId = $shelterModel->create($data);
            $shelter = $shelterModel->findById($shelterId);

            return ResponseHelper::sendResponse($response, $shelter);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage());
        }
    }

    public function deleteShelter(Request $request, Response $response, array $args): Response {
        try {
            $shelterId = (int)$args['id'];
            $shelterModel = new \PawPath\models\Shelter();
            
            // Check if shelter has any pets
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM Pet WHERE shelter_id = ?");
            $stmt->execute([$shelterId]);
            if ($stmt->fetchColumn() > 0) {
                throw new \RuntimeException('Cannot delete shelter with existing pets');
            }

            $success = $shelterModel->delete($shelterId);
            
            if (!$success) {
                throw new \RuntimeException('Failed to delete shelter');
            }

            return ResponseHelper::sendResponse($response, ['message' => 'Shelter deleted successfully']);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage());
        }
    }

    public function resendVerification(Request $request, Response $response, array $args): Response {
        try {
            $userId = (int) $args['id'];
            $user = $this->userModel->findById($userId);

            if (!$user) {
                throw new \RuntimeException('User not found');
            }

            $token = $this->userModel->createEmailVerificationToken($userId);
            $this->emailService->sendVerificationEmail($user['email'], $user['username'], $token);

            return ResponseHelper::sendResponse($response, [
                'message' => 'Verification email sent successfully'
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage());
        }
    }
}
