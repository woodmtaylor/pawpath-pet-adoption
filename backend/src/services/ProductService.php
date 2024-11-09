<?php

namespace PawPath\services;

use PawPath\models\Product;
use RuntimeException;

class ProductService {
    private Product $productModel;
    
    public function __construct() {
        $this->productModel = new Product();
    }
    
    public function createProduct(array $data): array {
        // Validate required fields
        if (empty($data['name']) || !isset($data['price'])) {
            throw new RuntimeException("Name and price are required");
        }
        
        // Validate price
        if ($data['price'] < 0) {
            throw new RuntimeException("Price cannot be negative");
        }
        
        // Create product
        $productId = $this->productModel->create($data);
        return $this->productModel->findById($productId);
    }
    
    public function getProduct(int $id): array {
        $product = $this->productModel->findById($id);
        if (!$product) {
            throw new RuntimeException("Product not found");
        }
        return $product;
    }
    
    public function listProducts(array $filters = []): array {
        // Validate price filters
        if (isset($filters['price_min']) && $filters['price_min'] < 0) {
            throw new RuntimeException("Minimum price cannot be negative");
        }
        
        if (isset($filters['price_max']) && $filters['price_max'] < 0) {
            throw new RuntimeException("Maximum price cannot be negative");
        }
        
        if (isset($filters['price_min'], $filters['price_max']) 
            && $filters['price_min'] > $filters['price_max']) {
            throw new RuntimeException("Minimum price cannot be greater than maximum price");
        }
        
        return $this->productModel->findAll($filters);
    }
    
    public function updateProduct(int $id, array $data): array {
        // Verify product exists
        $product = $this->productModel->findById($id);
        if (!$product) {
            throw new RuntimeException("Product not found");
        }
        
        // Validate price if provided
        if (isset($data['price']) && $data['price'] < 0) {
            throw new RuntimeException("Price cannot be negative");
        }
        
        // Update product
        $this->productModel->update($id, $data);
        return $this->productModel->findById($id);
    }
    
    public function deleteProduct(int $id): void {
        // Verify product exists
        $product = $this->productModel->findById($id);
        if (!$product) {
            throw new RuntimeException("Product not found");
        }
        
        // Delete product
        if (!$this->productModel->delete($id)) {
            throw new RuntimeException("Failed to delete product");
        }
    }
}
