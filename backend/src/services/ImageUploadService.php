<?php
namespace PawPath\services;

class ImageUploadService {
    private string $uploadPath;
    
     public function __construct() {
        // Set absolute path to uploads directory
        $this->uploadPath = '/home/woodmtaylor/pawpath-pet-adoption/backend/public/uploads/images';
        
        error_log("Upload path set to: " . $this->uploadPath);
        
        // Check if directory exists and is writable
        if (!file_exists($this->uploadPath)) {
            try {
                error_log("Attempting to create directory: " . $this->uploadPath);
                $result = mkdir($this->uploadPath, 0755, true);
                if (!$result) {
                    error_log("Failed to create upload directory using mkdir");
                }
            } catch (\Exception $e) {
                error_log("Error creating upload directory: " . $e->getMessage());
            }
        }
        
        if (file_exists($this->uploadPath) && !is_writable($this->uploadPath)) {
            error_log("Upload directory exists but is not writable: " . $this->uploadPath);
        }
    }   

    public function uploadImage(array $file): string {
        // Verify upload directory is usable before proceeding
        if (!file_exists($this->uploadPath) || !is_writable($this->uploadPath)) {
            throw new \RuntimeException('Upload directory is not writable. Please check permissions.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload failed with error code: ' . $file['error']);
        }
        
        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'])) {
            throw new \RuntimeException('Invalid file type. Only JPEG, PNG, and WebP images are allowed.');
        }
        
        $extension = match($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new \RuntimeException('Unsupported image type')
        };
        
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $filepath = $this->uploadPath . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new \RuntimeException('Failed to move uploaded file');
        }
        
        return '/uploads/images/' . $filename;
    }
    
    public function deleteImage(string $imageUrl): bool {
        $filepath = $this->uploadPath . '/' . basename($imageUrl);
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
}
