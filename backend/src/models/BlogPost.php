<?php
// src/models/BlogPost.php
namespace PawPath\models;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class BlogPost {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function create(array $data): int {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO Blog_Post (
                    title,
                    content,
                    publication_date,
                    author_id
                ) VALUES (?, ?, CURDATE(), ?)
            ");
            
            $stmt->execute([
                $data['title'],
                $data['content'],
                $data['author_id']
            ]);
            
            $postId = (int) $this->db->lastInsertId();
            
            // Handle product relationships if provided
            if (!empty($data['product_ids'])) {
                $this->updateProductRelations($postId, $data['product_ids']);
            }
            
            return $postId;
        } catch (PDOException $e) {
            error_log("Error creating blog post: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    bp.*,
                    u.username as author_name
                FROM Blog_Post bp
                JOIN User u ON bp.author_id = u.user_id
                WHERE bp.post_id = ?
            ");
            
            $stmt->execute([$id]);
            $post = $stmt->fetch();
            
            if ($post) {
                // Get related products
                $stmt = $this->db->prepare("
                    SELECT p.*
                    FROM Product p
                    JOIN Blog_Product_Relation bpr ON p.product_id = bpr.product_id
                    WHERE bpr.post_id = ?
                ");
                $stmt->execute([$id]);
                $post['products'] = $stmt->fetchAll();
            }
            
            return $post ?: null;
        } catch (PDOException $e) {
            error_log("Error finding blog post: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findAll(array $filters = []): array {
        try {
            $query = "
                SELECT 
                    bp.*,
                    u.username as author_name
                FROM Blog_Post bp
                JOIN User u ON bp.author_id = u.user_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($filters['search'])) {
                $query .= " AND (
                    bp.title LIKE ? OR 
                    bp.content LIKE ? OR 
                    bp.title LIKE ?
                )";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Always order by newest first
            $query .= " ORDER BY bp.post_id DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error finding blog posts: " . $e->getMessage());
            throw $e;
        }
    }
        
    public function update(int $id, array $data): bool {
        try {
            $this->db->beginTransaction();
            
            $fields = [];
            $params = [];
            
            if (isset($data['title'])) {
                $fields[] = "title = ?";
                $params[] = $data['title'];
            }
            
            if (isset($data['content'])) {
                $fields[] = "content = ?";
                $params[] = $data['content'];
            }
            
            if (!empty($fields)) {
                $params[] = $id;
                $query = "UPDATE Blog_Post SET " . implode(', ', $fields) . " WHERE post_id = ?";
                
                $stmt = $this->db->prepare($query);
                $stmt->execute($params);
            }
            
            // Update product relations if provided
            if (isset($data['product_ids'])) {
                $this->updateProductRelations($id, $data['product_ids']);
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error updating blog post: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function delete(int $id): bool {
        try {
            $this->db->beginTransaction();
            
            // Delete product relations first
            $stmt = $this->db->prepare("DELETE FROM Blog_Product_Relation WHERE post_id = ?");
            $stmt->execute([$id]);
            
            // Delete blog post
            $stmt = $this->db->prepare("DELETE FROM Blog_Post WHERE post_id = ?");
            $result = $stmt->execute([$id]);
            
            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error deleting blog post: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function updateProductRelations(int $postId, array $productIds): void {
        // Remove existing relations
        $stmt = $this->db->prepare("DELETE FROM Blog_Product_Relation WHERE post_id = ?");
        $stmt->execute([$postId]);
        
        // Add new relations
        $stmt = $this->db->prepare("
            INSERT INTO Blog_Product_Relation (post_id, product_id)
            VALUES (?, ?)
        ");
        
        foreach ($productIds as $productId) {
            $stmt->execute([$postId, $productId]);
        }
    }
}
