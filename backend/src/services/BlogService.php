<?php
// src/services/BlogService.php
namespace PawPath\services;

use PawPath\models\BlogPost;
use PawPath\models\User;
use RuntimeException;

class BlogService {
    private BlogPost $blogModel;
    private User $userModel;
    
    public function __construct() {
        $this->blogModel = new BlogPost();
        $this->userModel = new User();
    }
    
    public function createPost(array $data): array {
        // Validate required fields
        if (empty($data['title']) || empty($data['content'])) {
            throw new RuntimeException("Title and content are required");
        }
        
        // Verify author exists
        if (!$this->userModel->findById($data['author_id'])) {
            throw new RuntimeException("Invalid author");
        }
        
        // Create post
        $postId = $this->blogModel->create($data);
        return $this->blogModel->findById($postId);
    }
    
    public function getPost(int $id): array {
        $post = $this->blogModel->findById($id);
        if (!$post) {
            throw new RuntimeException("Post not found");
        }
        return $post;
    }
    
    public function listPosts(array $filters = []): array {
        return $this->blogModel->findAll($filters);
    }
    
    public function updatePost(int $id, array $data, int $userId): array {
        // Verify post exists and user is the author
        $post = $this->blogModel->findById($id);
        if (!$post) {
            throw new RuntimeException("Post not found");
        }
        
        if ($post['author_id'] !== $userId) {
            throw new RuntimeException("Unauthorized to update this post");
        }
        
        // Update post
        $this->blogModel->update($id, $data);
        return $this->blogModel->findById($id);
    }
    
    public function deletePost(int $id, int $userId): void {
        // Verify post exists and user is the author
        $post = $this->blogModel->findById($id);
        if (!$post) {
            throw new RuntimeException("Post not found");
        }
        
        if ($post['author_id'] !== $userId) {
            throw new RuntimeException("Unauthorized to delete this post");
        }
        
        // Delete post
        if (!$this->blogModel->delete($id)) {
            throw new RuntimeException("Failed to delete post");
        }
    }
}
