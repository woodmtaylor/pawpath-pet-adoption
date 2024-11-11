<?php
namespace PawPath\utils;

use Psr\Http\Message\ResponseInterface;

class ResponseHelper {
    public static function sendResponse(ResponseInterface $response, $data, int $status = 200): ResponseInterface {
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $data
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    public static function sendError(ResponseInterface $response, string $message, int $status = 400): ResponseInterface {
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $message
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
