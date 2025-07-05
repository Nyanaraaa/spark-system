<?php

class InputValidator
{
    private static $errors = [];

    /**
     * Validate and sanitize input data
     */
    public static function validate($data, $rules)
    {
        self::$errors = [];
        $validatedData = [];

        foreach ($rules as $field => $rule) {
            $value = isset($data[$field]) ? $data[$field] : null;
            $validatedData[$field] = self::validateField($field, $value, $rule);
        }

        return self::$errors ? null : $validatedData;
    }

    /**
     * Get validation errors
     */
    public static function getErrors()
    {
        return self::$errors;
    }

    /**
     * Get first error message
     */
    public static function getFirstError()
    {
        return self::$errors ? reset(self::$errors) : null;
    }

    /**
     * Validate a single field
     */
    private static function validateField($fieldName, $value, $rules)
    {
        $ruleArray = is_array($rules) ? $rules : explode('|', $rules);
        $isRequired = in_array('required', $ruleArray);

        // Check if field is required but empty
        if ($isRequired && (is_null($value) || trim($value) === '')) {
            self::$errors[$fieldName] = ucfirst($fieldName) . ' is required';
            return null;
        }

        // If field is optional and empty, return null
        if (!$isRequired && (is_null($value) || trim($value) === '')) {
            return null;
        }

        // Apply validation rules
        foreach ($ruleArray as $rule) {
            if (strpos($rule, ':') !== false) {
                list($ruleName, $ruleValue) = explode(':', $rule, 2);
            } else {
                $ruleName = $rule;
                $ruleValue = null;
            }

            $value = self::applyRule($fieldName, $value, $ruleName, $ruleValue);
            
            if ($value === false) {
                return null; // Validation failed
            }
        }

        return $value;
    }

    /**
     * Apply a single validation rule
     */
    private static function applyRule($fieldName, $value, $rule, $ruleValue = null)
    {
        switch ($rule) {
            case 'required':
                // Already handled above
                break;

            case 'string':
                if (!is_string($value)) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be a string';
                    return false;
                }
                return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');

            case 'integer':
            case 'int':
                if (!is_numeric($value) || !ctype_digit(strval($value))) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be an integer';
                    return false;
                }
                return (int)$value;

            case 'numeric':
                if (!is_numeric($value)) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be numeric';
                    return false;
                }
                return (float)$value;

            case 'email':
                $email = filter_var(trim($value), FILTER_VALIDATE_EMAIL);
                if (!$email) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be a valid email address';
                    return false;
                }
                return $email;

            case 'date':
                $date = date_create($value);
                if (!$date) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be a valid date';
                    return false;
                }
                return date_format($date, 'Y-m-d');

            case 'datetime':
                $date = date_create($value);
                if (!$date) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be a valid datetime';
                    return false;
                }
                return date_format($date, 'Y-m-d H:i:s');

            case 'min':
                if (is_string($value) && strlen($value) < $ruleValue) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . " must be at least {$ruleValue} characters";
                    return false;
                } elseif (is_numeric($value) && $value < $ruleValue) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . " must be at least {$ruleValue}";
                    return false;
                }
                break;

            case 'max':
                if (is_string($value) && strlen($value) > $ruleValue) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . " must not exceed {$ruleValue} characters";
                    return false;
                } elseif (is_numeric($value) && $value > $ruleValue) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . " must not exceed {$ruleValue}";
                    return false;
                }
                break;

            case 'in':
                $allowedValues = explode(',', $ruleValue);
                if (!in_array($value, $allowedValues)) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be one of: ' . implode(', ', $allowedValues);
                    return false;
                }
                break;

            case 'alpha':
                if (!ctype_alpha($value)) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must contain only letters';
                    return false;
                }
                break;

            case 'alphanumeric':
                if (!ctype_alnum($value)) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must contain only letters and numbers';
                    return false;
                }
                break;

            case 'username':
                if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $value)) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be 3-20 characters and contain only letters, numbers, hyphens, or underscores';
                    return false;
                }
                break;

            case 'password':
                if (strlen($value) < 6) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be at least 6 characters long';
                    return false;
                }
                break;

            case 'employee_id':
                if (!preg_match('/^[A-Z0-9]{4,10}$/', $value)) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be 4-10 characters and contain only uppercase letters and numbers';
                    return false;
                }
                break;

            case 'phone':
                $phone = preg_replace('/[^0-9]/', '', $value);
                if (strlen($phone) < 10 || strlen($phone) > 15) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be a valid phone number';
                    return false;
                }
                return $phone;

            case 'url':
                $url = filter_var($value, FILTER_VALIDATE_URL);
                if (!$url) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be a valid URL';
                    return false;
                }
                return $url;

            case 'json':
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be valid JSON';
                    return false;
                }
                return $decoded;

            case 'boolean':
                if (in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true)) {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                }
                self::$errors[$fieldName] = ucfirst($fieldName) . ' must be true or false';
                return false;

            case 'array':
                if (!is_array($value)) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be an array';
                    return false;
                }
                break;

            case 'file':
                if (!is_array($value) || !isset($value['tmp_name']) || !is_uploaded_file($value['tmp_name'])) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be a valid uploaded file';
                    return false;
                }
                break;

            case 'image':
                if (!is_array($value) || !isset($value['tmp_name'])) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be a valid image file';
                    return false;
                }
                $imageInfo = getimagesize($value['tmp_name']);
                if (!$imageInfo) {
                    self::$errors[$fieldName] = ucfirst($fieldName) . ' must be a valid image file';
                    return false;
                }
                break;
        }

        return $value;
    }

    /**
     * Validate file upload
     */
    public static function validateFile($file, $allowedTypes = [], $maxSize = 5242880) // 5MB default
    {
        if (!is_array($file) || !isset($file['tmp_name'])) {
            return ['error' => 'No file uploaded'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'File upload error: ' . $file['error']];
        }

        if ($file['size'] > $maxSize) {
            return ['error' => 'File size exceeds maximum allowed size'];
        }

        if (!empty($allowedTypes)) {
            $fileType = mime_content_type($file['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                return ['error' => 'File type not allowed'];
            }
        }

        return ['success' => true, 'file' => $file];
    }

    /**
     * Sanitize HTML content
     */
    public static function sanitizeHtml($content, $allowedTags = '')
    {
        return strip_tags($content, $allowedTags);
    }

    /**
     * Validate database ID
     */
    public static function validateId($id, $fieldName = 'ID')
    {
        if (!is_numeric($id) || $id <= 0) {
            self::$errors[$fieldName] = ucfirst($fieldName) . ' must be a positive integer';
            return false;
        }
        return (int)$id;
    }

    /**
     * Validate pagination parameters
     */
    public static function validatePagination($page = 1, $limit = 10, $maxLimit = 100)
    {
        $page = max(1, (int)$page);
        $limit = max(1, min($maxLimit, (int)$limit));
        
        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => ($page - 1) * $limit
        ];
    }

    /**
     * Validate date range
     */
    public static function validateDateRange($startDate, $endDate)
    {
        $start = date_create($startDate);
        $end = date_create($endDate);

        if (!$start || !$end) {
            self::$errors['date_range'] = 'Invalid date format';
            return false;
        }

        if ($start > $end) {
            self::$errors['date_range'] = 'Start date must be before end date';
            return false;
        }

        return [
            'start_date' => date_format($start, 'Y-m-d'),
            'end_date' => date_format($end, 'Y-m-d')
        ];
    }
}
