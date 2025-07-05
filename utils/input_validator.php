<?php
/**
 * Input Validator
 * Centralizes input validation and sanitization
 */

class InputValidator 
{
    /**
     * Validate and sanitize email
     */
    public static function validateEmail($email) 
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return [
            'valid' => filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
            'value' => $email,
            'message' => 'Please enter a valid email address'
        ];
    }
    
    /**
     * Validate phone number
     */
    public static function validatePhone($phone) 
    {
        $phone = preg_replace('/[^0-9+\-\s\(\)]/', '', $phone);
        $valid = preg_match('/^[\+]?[0-9\-\s\(\)]{7,20}$/', $phone);
        
        return [
            'valid' => $valid,
            'value' => $phone,
            'message' => 'Please enter a valid phone number'
        ];
    }
    
    /**
     * Validate username
     */
    public static function validateUsername($username) 
    {
        $username = trim($username);
        $valid = preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
        
        return [
            'valid' => $valid,
            'value' => $username,
            'message' => 'Username must be 3-20 characters (letters, numbers, underscore only)'
        ];
    }
    
    /**
     * Validate employee ID
     */
    public static function validateEmployeeId($employeeId) 
    {
        $employeeId = trim($employeeId);
        $valid = !empty($employeeId) && strlen($employeeId) >= 3;
        
        return [
            'valid' => $valid,
            'value' => $employeeId,
            'message' => 'Employee ID must be at least 3 characters long'
        ];
    }
    
    /**
     * Validate text input (general)
     */
    public static function validateText($text, $minLength = 1, $maxLength = 255) 
    {
        $text = trim($text);
        $length = strlen($text);
        $valid = $length >= $minLength && $length <= $maxLength;
        
        return [
            'valid' => $valid,
            'value' => htmlspecialchars($text, ENT_QUOTES, 'UTF-8'),
            'message' => "Text must be between {$minLength} and {$maxLength} characters"
        ];
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) 
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return [
                'valid' => false,
                'message' => 'Invalid file upload'
            ];
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'message' => 'File upload failed'
            ];
        }
        
        if ($file['size'] > $maxSize) {
            return [
                'valid' => false,
                'message' => 'File size exceeds maximum allowed size'
            ];
        }
        
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension'] ?? '');
        
        if (!in_array($extension, $allowedTypes)) {
            return [
                'valid' => false,
                'message' => 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes)
            ];
        }
        
        // Verify file type by content
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg', 
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        if (!in_array($mimeType, array_values($allowedMimes))) {
            return [
                'valid' => false,
                'message' => 'Invalid file type detected'
            ];
        }
        
        return [
            'valid' => true,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'message' => 'File is valid'
        ];
    }
    
    /**
     * Sanitize HTML input
     */
    public static function sanitizeHtml($html) 
    {
        return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize and validate integer input
     */
    public static function sanitizeInt($value) 
    {
        $cleaned = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
        $int = filter_var($cleaned, FILTER_VALIDATE_INT);
        return $int !== false ? $int : null;
    }
    
    /**
     * Sanitize string input
     */
    public static function sanitizeString($value, $stripTags = true) 
    {
        $cleaned = trim($value);
        if ($stripTags) {
            $cleaned = strip_tags($cleaned);
        }
        return htmlspecialchars($cleaned, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate multiple fields at once
     */
    public static function validateFields($data, $rules) 
    {
        $results = [];
        $isValid = true;
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            
            switch ($rule['type']) {
                case 'email':
                    $result = self::validateEmail($value);
                    break;
                case 'phone':
                    $result = self::validatePhone($value);
                    break;
                case 'username':
                    $result = self::validateUsername($value);
                    break;
                case 'employee_id':
                    $result = self::validateEmployeeId($value);
                    break;
                case 'text':
                    $minLength = $rule['min_length'] ?? 1;
                    $maxLength = $rule['max_length'] ?? 255;
                    $result = self::validateText($value, $minLength, $maxLength);
                    break;
                default:
                    $result = [
                        'valid' => true,
                        'value' => self::sanitizeHtml($value),
                        'message' => ''
                    ];
            }
            
            $results[$field] = $result;
            if (!$result['valid']) {
                $isValid = false;
            }
        }
        
        return [
            'valid' => $isValid,
            'fields' => $results
        ];
    }
    
    /**
     * Validate password strength
     */
    public static function validatePassword($password) 
    {
        $password = trim($password);
        $errors = [];
        
        // Check minimum length
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        // Check for at least one number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        // Check for at least one special character
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        $valid = empty($errors);
        $message = $valid ? 'Password is strong' : implode('. ', $errors);
        
        return [
            'valid' => $valid,
            'value' => $password,
            'message' => $message
        ];
    }
}
