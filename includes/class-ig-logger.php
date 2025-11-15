<?php
/**
 * Logger Class
 *
 * Handles logging throughout the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class IG_Logger {
    
    /**
     * Log levels
     */
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';
    
    /**
     * Log an error
     */
    public static function error(string $message, array $context = array()): void {
        self::log(self::LEVEL_ERROR, $message, $context);
    }
    
    /**
     * Log a warning
     */
    public static function warning(string $message, array $context = array()): void {
        self::log(self::LEVEL_WARNING, $message, $context);
    }
    
    /**
     * Log info
     */
    public static function info(string $message, array $context = array()): void {
        self::log(self::LEVEL_INFO, $message, $context);
    }
    
    /**
     * Log debug info
     */
    public static function debug(string $message, array $context = array()): void {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            self::log(self::LEVEL_DEBUG, $message, $context);
        }
    }
    
    /**
     * Log critical error
     */
    public static function critical(string $message, array $context = array()): void {
        self::log(self::LEVEL_CRITICAL, $message, $context);
    }
    
    /**
     * Main log method
     */
    private static function log(string $level, string $message, array $context = array()): void {
        $formatted_message = sprintf(
            '[IPSIT Invoice Generator - %s] %s',
            strtoupper($level),
            $message
        );
        
        if (!empty($context)) {
            $formatted_message .= ' | Context: ' . wp_json_encode($context);
        }
        
        error_log($formatted_message);
        
        // Also log to WordPress debug log if enabled
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            // The error_log above already handles this
        }
        
        // Store recent logs in transient for admin display (optional)
        if ($level === self::LEVEL_ERROR || $level === self::LEVEL_CRITICAL) {
            self::store_recent_log($level, $message, $context);
        }
    }
    
    /**
     * Store recent logs for admin display
     */
    private static function store_recent_log(string $level, string $message, array $context): void {
        $logs = get_transient('ipsit_ig_recent_logs');
        if (!is_array($logs)) {
            $logs = array();
        }
        
        $logs[] = array(
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'timestamp' => current_time('mysql'),
        );
        
        // Keep only last 50 logs
        if (count($logs) > 50) {
            $logs = array_slice($logs, -50);
        }
        
        set_transient('ipsit_ig_recent_logs', $logs, DAY_IN_SECONDS);
    }
    
    /**
     * Get recent logs
     */
    public static function get_recent_logs(): array {
        $logs = get_transient('ipsit_ig_recent_logs');
        return is_array($logs) ? $logs : array();
    }
    
    /**
     * Clear recent logs
     */
    public static function clear_logs(): void {
        delete_transient('ipsit_ig_recent_logs');
    }
    
    /**
     * Log database error
     */
    public static function log_db_error(string $operation, $wpdb): void {
        global $wpdb;
        if (!empty($wpdb->last_error)) {
            self::error(
                sprintf('Database error during %s', $operation),
                array(
                    'sql_error' => $wpdb->last_error,
                    'last_query' => $wpdb->last_query,
                )
            );
        }
    }
}

