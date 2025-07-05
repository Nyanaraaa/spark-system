<?php
/**
 * Password Security Handler
 * Provides secure password hashing and verification
 */

class PasswordSecurity 
{
    /**
     * Hash a password securely
     */
    public static function hashPassword($password) 
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3,         // 3 threads
        ]);
    }
    
    /**
     * Verify a password against its hash
     */
    public static function verifyPassword($password, $hash) 
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if password needs rehashing (for upgrading security)
     */
    public static function needsRehash($hash) 
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3,
        ]);
    }
    
    /**
     * Generate a secure random token
     */
    public static function generateToken($length = 32) 
    {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Validate password strength
     */
    public static function validatePasswordStrength($password) 
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
