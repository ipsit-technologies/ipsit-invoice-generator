<?php
/**
 * Helper/Utility Class
 *
 * Common utility functions used throughout the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class IG_Helper {
    
    /**
     * Check rate limit for user action
     */
    public static function check_rate_limit(string $action, int $user_id = 0): bool {
        if ($user_id === 0) {
            $user_id = get_current_user_id();
        }
        
        $transient_key = IG_Config::RATE_LIMIT_PREFIX . $action . '_' . $user_id;
        $count = get_transient($transient_key);
        
        if ($count && $count >= IG_Config::RATE_LIMIT_REQUESTS) {
            IG_Logger::warning(
                'Rate limit exceeded',
                array(
                    'action' => $action,
                    'user_id' => $user_id,
                    'count' => $count,
                )
            );
            return false;
        }
        
        set_transient($transient_key, ($count ?: 0) + 1, IG_Config::RATE_LIMIT_WINDOW);
        return true;
    }
    
    /**
     * Clear rate limit for user action
     */
    public static function clear_rate_limit(string $action, int $user_id = 0): void {
        if ($user_id === 0) {
            $user_id = get_current_user_id();
        }
        
        $transient_key = IG_Config::RATE_LIMIT_PREFIX . $action . '_' . $user_id;
        delete_transient($transient_key);
    }
    
    /**
     * Get cached data
     */
    public static function get_cache(string $key) {
        return get_transient($key);
    }
    
    /**
     * Set cached data
     */
    public static function set_cache(string $key, $value, int $expiration = null): bool {
        if ($expiration === null) {
            $expiration = IG_Config::CACHE_COMPANY_DATA;
        }
        return set_transient($key, $value, $expiration);
    }
    
    /**
     * Delete cached data
     */
    public static function delete_cache(string $key): bool {
        return delete_transient($key);
    }
    
    /**
     * Clear all plugin caches
     */
    public static function clear_all_caches(): void {
        delete_transient(IG_Config::CACHE_KEY_COMPANY);
        delete_transient(IG_Config::CACHE_KEY_TEMPLATES);
        delete_transient(IG_Config::CACHE_KEY_SETTINGS);
        
        IG_Logger::info('All plugin caches cleared');
    }
    
    /**
     * Format currency
     */
    public static function format_currency(float $amount, string $currency_symbol = null): string {
        if ($currency_symbol === null) {
            $currency_symbol = get_option('ipsit_ig_currency_symbol', '$');
        }
        
        return $currency_symbol . number_format($amount, 2);
    }
    
    /**
     * Format date
     */
    public static function format_date(string $date, string $format = null): string {
        if ($format === null) {
            $format = get_option('date_format');
        }
        
        return date_i18n($format, strtotime($date));
    }
    
    /**
     * Sanitize template content
     */
    public static function sanitize_template_html(string $html): string {
        return wp_kses($html, IG_Config::get_template_allowed_html());
    }
    
    /**
     * Sanitize CSS content
     */
    public static function sanitize_css(string $css): string {
        // Remove any <script> tags or javascript: protocols
        $css = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $css);
        $css = preg_replace('/javascript:/i', '', $css);
        $css = preg_replace('/on\w+\s*=/i', '', $css); // Remove event handlers
        
        return wp_strip_all_tags($css);
    }
    
    /**
     * Check if user has capability
     */
    public static function current_user_can_manage_invoices(): bool {
        return current_user_can(IG_Config::CAP_MANAGE_INVOICES) || current_user_can('manage_options');
    }
    
    /**
     * Check if user can manage clients
     */
    public static function current_user_can_manage_clients(): bool {
        return current_user_can(IG_Config::CAP_MANAGE_CLIENTS) || current_user_can('manage_options');
    }
    
    /**
     * Check if user can manage templates
     */
    public static function current_user_can_manage_templates(): bool {
        return current_user_can(IG_Config::CAP_MANAGE_TEMPLATES) || current_user_can('manage_options');
    }
    
    /**
     * Check if user can manage settings
     */
    public static function current_user_can_manage_settings(): bool {
        return current_user_can(IG_Config::CAP_MANAGE_SETTINGS) || current_user_can('manage_options');
    }
    
    /**
     * Get file upload max size in bytes
     */
    public static function get_max_upload_size(): int {
        $max_upload = wp_max_upload_size();
        $max_post = ini_get('post_max_size');
        $memory_limit = ini_get('memory_limit');
        
        $max_post_bytes = self::convert_to_bytes($max_post);
        $memory_limit_bytes = self::convert_to_bytes($memory_limit);
        
        return min($max_upload, $max_post_bytes, $memory_limit_bytes);
    }
    
    /**
     * Convert size string to bytes
     */
    private static function convert_to_bytes(string $size): int {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;
        
        switch ($last) {
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }
        
        return $size;
    }
    
    /**
     * Validate file upload
     */
    public static function validate_logo_upload(array $file): array {
        $errors = array();
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = __('File upload error.', 'ipsit-invoice-generator');
            return $errors;
        }
        
        if ($file['size'] > IG_Config::MAX_LOGO_SIZE) {
            $errors[] = sprintf(
                __('File size exceeds maximum allowed size of %s.', 'ipsit-invoice-generator'),
                size_format(IG_Config::MAX_LOGO_SIZE)
            );
        }
        
        if (!in_array($file['type'], IG_Config::ALLOWED_LOGO_TYPES)) {
            $errors[] = __('Invalid file type. Only JPEG, PNG, and GIF are allowed.', 'ipsit-invoice-generator');
        }
        
        return $errors;
    }
    
    /**
     * Generate random string
     */
    public static function generate_random_string(int $length = 32): string {
        return wp_generate_password($length, false);
    }
    
    /**
     * Check if request is AJAX
     */
    public static function is_ajax(): bool {
        return wp_doing_ajax();
    }
    
    /**
     * Get current user IP
     */
    public static function get_user_ip(): string {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        }
        
        return $ip;
    }
}

