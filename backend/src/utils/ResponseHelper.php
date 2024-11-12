<?php
namespace PawPath\utils;

use Psr\Http\Message\ResponseInterface;

class ResponseHelper {
    public static function sendResponse(ResponseInterface $response, $data, int $status = 200): ResponseInterface {
        $body = json_encode([
            'success' => true,
            'data' => $data
        ]);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON encode error: " . json_last_error_msg());
            return self::sendError($response, "Internal server error", 500);
        }
        
        $response->getBody()->write($body);
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    public static function sendError(ResponseInterface $response, string $message, int $status = 400): ResponseInterface {
        $body = json_encode([
            'success' => false,
            'error' => $message
        ]);
        
        $response->getBody()->write($body);
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
