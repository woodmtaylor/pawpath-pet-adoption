<?php
namespace PawPath\api;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PawPath\models\User;
use PawPath\services\EmailService;
use PawPath\utils\ResponseHelper;

class AdminController {
    private User $userModel;
    private EmailService $emailService;

    public function __construct() {
        $this->userModel = new User();
        $this->emailService = new EmailService();
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
