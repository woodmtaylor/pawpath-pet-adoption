<?php
namespace PawPath\services;

class ImageUploadService {
    private string $uploadPath;
    
    public function __construct() {
        // Set absolute path to uploads directory
        $this->uploadPath = __DIR__ . '/../../public/uploads/images';
        
        // Ensure the upload directories exist
        $this->ensureUploadDirectoryExists($this->uploadPath . '/profiles');
    }
    
    private function ensureUploadDirectoryExists(string $path): void {
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new \RuntimeException("Failed to create upload directory: $path");
            }
        }
        
        if (!is_writable($path)) {
            throw new \RuntimeException("Upload directory is not writable: $path");
        }
    }

    public function uploadProfileImage(array $file): string {
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
        
        $filename = 'profile_' . bin2hex(random_bytes(16)) . '.' . $extension;
        $filepath = $this->uploadPath . '/profiles/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new \RuntimeException('Failed to move uploaded file');
        }
        
        // Make sure this URL is correct for your setup
        return '/uploads/images/profiles/' . $filename;
    }

    public function deleteImage(string $filepath): bool {
        $fullPath = $this->uploadPath . '/' . basename($filepath);
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
}
