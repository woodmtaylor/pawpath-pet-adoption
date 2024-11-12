<?php
namespace PawPath\config;

class Permissions {
    // User roles
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SHELTER_STAFF = 'shelter_staff';
    public const ROLE_ADOPTER = 'adopter';
    
    // Permission sets by role
    public const PERMISSIONS = [
        self::ROLE_ADMIN => [
            'users:manage',
            'shelters:manage',
            'pets:manage',
            'applications:manage',
            'blog:manage',
            'products:manage'
        ],
        self::ROLE_SHELTER_STAFF => [
            'pets:create',
            'pets:update',
            'pets:delete',
            'applications:view',
            'applications:process'
        ],
        self::ROLE_ADOPTER => [
            'pets:view',
            'applications:create',
            'applications:view-own',
            'profile:manage-own'
        ]
    ];
    
    public static function hasPermission(string $role, string $permission): bool {
        return isset(self::PERMISSIONS[$role]) && 
               in_array($permission, self::PERMISSIONS[$role]);
    }
    
    public static function validateRole(string $role): bool {
        return in_array($role, [self::ROLE_ADMIN, self::ROLE_SHELTER_STAFF, self::ROLE_ADOPTER]);
    }
}
