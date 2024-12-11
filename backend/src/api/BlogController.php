<?php
namespace PawPath\api;

use PDO;
use PawPath\services\BlogService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PawPath\config\database\DatabaseConfig;
use PawPath\utils\ResponseHelper;

class BlogController {
    private PDO $db;
    private BlogService $blogService;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
        $this->blogService = new BlogService();
    }
    
    public function listPosts(Request $request, Response $response): Response {
        try {
            // Get posts with author names and products
            $query = "
                SELECT 
                    bp.*,
                    u.username as author_name,
                    GROUP_CONCAT(
                        JSON_OBJECT(
                            'product_id', p.product_id,
                            'name', p.name,
                            'description', p.description,
                            'price', p.price,
                            'affiliate_link', p.affiliate_link
                        )
                    ) as products
                FROM Blog_Post bp
                LEFT JOIN User u ON bp.author_id = u.user_id
                LEFT JOIN Blog_Product_Relation bpr ON bp.post_id = bpr.post_id
                LEFT JOIN Product p ON bpr.product_id = p.product_id
                GROUP BY bp.post_id
                ORDER BY bp.publication_date DESC
            ";
            
            error_log("Executing blog posts query");
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $posts = $stmt->fetchAll();
            
            error_log("Found " . count($posts) . " posts");
            
            // Process the products string into an array
            foreach ($posts as &$post) {
                if ($post['products']) {
                    $products = explode(',', $post['products']);
                    $post['products'] = array_map(function($product) {
                        return json_decode($product, true);
                    }, $products);
                } else {
                    $post['products'] = [];
                }
            }

            return ResponseHelper::sendResponse($response, $posts);
        } catch (\Exception $e) {
            error_log("Error in listPosts: " . $e->getMessage());
            return ResponseHelper::sendError($response, "Failed to fetch blog posts: " . $e->getMessage());
        }
    }
    
    public function getPost(Request $request, Response $response, array $args): Response {
        try {
            $postId = (int) $args['id'];
            $post = $this->blogService->getPost($postId);
            
            if (!$post) {
                return ResponseHelper::sendError($response, "Post not found", 404);
            }

            return ResponseHelper::sendResponse($response, $post);
        } catch (\Exception $e) {
            error_log("Error in getPost: " . $e->getMessage());
            return ResponseHelper::sendError($response, "Failed to fetch blog post", 500);
        }
    }
}
